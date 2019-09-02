<?php
// This file is part of 3rd party created module for Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod
 * @subpackage peerassessment
 * @copyright  2013 LEARNING TECHNOLOGY SERVICES
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define('PEERASSESSMENT_STATUS_NOT_SUBMITTED', 0);
define('PEERASSESSMENT_STATUS_SUBMITTED', 1);
define('PEERASSESSMENT_STATUS_GRADED', 2);
define('PEERASSESSMENT_STATUS_NOT_SUBMITTED_CLOSED', 3);

define('PEERASSESSMENT_DUEDATE_NOT_USED', 0);
define('PEERASSESSMENT_DUEDATE_OK', 1);
define('PEERASSESSMENT_DUEDATE_PASSED', 2);

define('PEERASSESSMENT_FROMDATE_NOT_USED', 0);
define('PEERASSESSMENT_FROMDATE_OK', 1);
define('PEERASSESSMENT_FROMDATE_BEFORE', 2);

define('PEERASSESSMENT_SIMPLE', 'simple');
define('PEERASSESSMENT_OUTLIER', 'outlier');

require_once($CFG->dirroot . '/lib/grouplib.php');

/**
 * Gets the peers that the current can grade.
 *
 * @param int $courseid The id of the course.
 * @param int $peerassessment 
 * @param int $groupingid optional returns only groups in the specified grouping.
 * @return array|bool Returns an array of the users for the specified
 * group or false if no users or an error returned.
 */
function peerassessment_get_peers($course, $peerassessment, $groupingid, $group = null) {
    global $USER;

    if (!$group) {
        $group = peerassessment_get_mygroup($course, $USER->id, $groupingid);
    }

    $members = groups_get_members($group);
    $membersgradeable = $members;

    if (!$peerassessment->selfgrading) {
        unset($membersgradeable[$USER->id]);
    }

    return $membersgradeable;
}

/**
 * Gets the group id for the group the user belongs to. Prints errors if
 * the user belongs to none or more than one group. Can be restricted to
 * groups within a grouping.
 *
 * @param int $courseid The id of the course.
 * @param int $userid The id of the user.
 * @param int $groupingid optional returns only groups in the specified grouping.
 * @param bool $die - @TODO check use of this parameter.
 * @return int The group id.
 */
function peerassessment_get_mygroup($courseid, $userid, $groupingid = 0, $die = true) {
    global $CFG;

    $mygroups = groups_get_all_groups($courseid, $userid, $groupingid);

    if ($die && count($mygroups) == 0) {
        print_error("You do not belong to any group.");
    } else if ($die && count($mygroups) > 1) {
        print_error("You belong to more than one group, this is currently not supported.");
    }

    $mygroup = array_shift($mygroups);

    return $mygroup->id;
}

/**
 * Gets the status.
 * @param $peerassessment
 * @param int $group 
 * @return stdClass status
 */
function peerassessment_get_status($peerassessment, $group) {
    global $DB;

    $submission = $DB->get_record('peerassessment_submission', array('assignment' => $peerassessment->id, 'groupid' => $group->id));
    $status = new stdClass();
    $duedate = peerassessment_due_date($peerassessment);

    if ($submission && $submission->timegraded) {
        $status->code = PEERASSESSMENT_STATUS_GRADED;
        $user = $DB->get_record('user', array('id' => $submission->gradedby));
        $status->text = "Assessment graded by " . fullname($user) . ' on ' .
        userdate($submission->timegraded) . '. Grade: ' . $submission->grade;
        $status->text = "Assessment graded by " . fullname($user) . ' on ' .
        userdate($submission->timegraded) . '.';
        return $status;
    }

    if (!$submission && $duedate == PEERASSESSMENT_DUEDATE_PASSED) {
        $status->code = PEERASSESSMENT_STATUS_NOT_SUBMITTED_CLOSED;
        $status->text = "Nothing submitted yet but due date passed " . format_time(time() - $peerassessment->duedate) . ' ago.';
        return $status;
    }

    if (!$submission) {
        $status->code = PEERASSESSMENT_STATUS_NOT_SUBMITTED;
        $status->text = "Nothing submitted yet";
        return $status;
    }

    if ($duedate == PEERASSESSMENT_DUEDATE_PASSED) {
        $submiter = $DB->get_record('user', array('id' => $submission->userid));
        $status->code = PEERASSESSMENT_STATUS_SUBMITTED;
        $status->text = "First submitted by " . fullname($submiter) . ' on ' . userdate($submission->timecreated) .
        ". Due date has passed " . format_time(time() - $peerassessment->duedate) . ' ago.';
        return $status;
    } else {
        $submiter = $DB->get_record('user', array('id' => $submission->userid));
        $status->code = PEERASSESSMENT_STATUS_SUBMITTED;
        $status->text = "First submitted by " . fullname($submiter) . ' on ' . userdate($submission->timecreated);
        return $status;
    }
}

/**
 * Does the peerassessment have submission(s) or grade(s) already?
 * @param $peerassessment
 * @return bool
 */
function has_been_graded($peerassessment) {
    global $DB;

    $submissions = $DB->get_records('peerassessment_submission', array('assignment' => $peerassessment->id));
    $status = new stdClass();
    $status->code = '';

    foreach ($submissions as $submission) {

        if ($submission && $submission->timegraded) {
            $status->code = PEERASSESSMENT_STATUS_GRADED;
        }
    }

    if ($status->code == PEERASSESSMENT_STATUS_GRADED) {
        return true;
    } else {
        return false;
    }

}

/**
 * Was due date used and has it passed?
 * @param $peerassessment
 * @return int
 */
function peerassessment_due_date($peerassessment) {
    if (!$peerassessment->duedate) {
        return PEERASSESSMENT_DUEDATE_NOT_USED;
    }

    if ($peerassessment->duedate < time()) {
        return PEERASSESSMENT_DUEDATE_PASSED;
    } else {
        return PEERASSESSMENT_DUEDATE_OK;
    }
}

/**
 * Was from date used and has it passed?
 * @param $peerassessment
 * @return int
 */
function peerassessment_from_date($peerassessment) {
    if (!$peerassessment->fromdate) {
        return PEERASSESSMENT_FROMDATE_NOT_USED;
    }

    if ($peerassessment->fromdate > time()) {
        return PEERASSESSMENT_FROMDATE_BEFORE;
    } else {
        return PEERASSESSMENT_FROMDATE_OK;
    }
}

/**
 * Can student $user submit/edit based on the current status?
 * @param $peerassessment
 * @param $groupid
 * @return stdClass
 */
function peerassessment_is_open($peerassessment, $groupid = 0) {
    global $DB;

    $status = new stdClass();
    $status->code = false;

    // Is it before from date?
    $fromdate = peerassessment_from_date($peerassessment);

    if ($fromdate == PEERASSESSMENT_FROMDATE_BEFORE) {
        $status->text = "Assessment not open yet.";
        return $status;
    }

    $course = $DB->get_record('course', array('id' => $peerassessment->course), '*', MUST_EXIST);
    $group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);

    // Is it already graded?
    $pstatus = peerassessment_get_status($peerassessment, $group);

    if ($pstatus->code == PEERASSESSMENT_STATUS_GRADED) {
        $status->text = "Assessment already graded.";
        return $status;
    }

    // Is it after due date?
    $duedate = peerassessment_due_date($peerassessment);

    if ($duedate == PEERASSESSMENT_DUEDATE_PASSED) {
        if ($peerassessment->allowlatesubmissions) {
            $status->code = true;
            $status->text = "After due date but late submissions allowed.";
        } else {
            $status->text = "After due date and late submissions not allowed.";
        }
        return $status;
    }

    // If we are here it means it's between from date and due date.
    $status->code = true;
    $status->text = "Assessment open.";

    return $status;
}

/**
 * Get grades for all peers in a group
 * @param $peerassessment
 * @param $group
 * @param $membersgradeable
 * @param $full
 * @return stdClass
 */
function peerassessment_get_peer_grades($peerassessment, $group, $membersgradeable = null, $full = true) {
    global $DB;

    $members = groups_get_members($group->id);
    $members = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($members, SQL_PARAMS_NAMED);
    list($insql2, $inparams2) = $DB->get_in_or_equal($members, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradedby ' . $insql1;
    $conditions[] = 'gradefor ' . $insql2;
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id
    ];

    $params = array_merge($params, $inparams1, $inparams2);
    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT p.*
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $peers = $DB->get_records_sql($sql, $params);
    $return = new stdClass();
    $grades = [];
    $feedback = [];

    foreach ($peers as $peer) {
        $grades[$peer->gradedby][$peer->gradefor] = $peer->grade;
        $feedback[$peer->gradedby][$peer->gradefor] = $peer->feedback;
    }

    if ($full) {
        foreach ($membersgradeable as $member1) {
            foreach ($membersgradeable as $member2) {
                if (!isset($grades[$member1->id][$member2->id])) {
                    $grades[$member1->id][$member2->id] = '-';
                }
                if (!isset($feedback[$member1->id][$member2->id])) {
                    $feedback[$member1->id][$member2->id] = '-';
                }
            }
        }
    }

    $return->grades = $grades;
    $return->feedback = $feedback;

    return $return;
}

/**
 * Get peer grades for an individual. Takes into account treat0asgrade
 * @param $peerassessment
 * @param $group
 * @param $user
 * @return array
 */
function peerassessment_get_indpeergrades($peerassessment, $group, $user) {
    global $DB;

    $members = groups_get_members($group->id);
    $peers = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradedby ' . $insql1;
    $conditions[] = 'gradefor = :userid';
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id,
        'userid' => $user->id
    ];

    $params = array_merge($params, $inparams1);

    if (!$peerassessment->treat0asgrade) {
        $conditions[] = 'p.grade > 0';
    }

    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT id, grade 
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $records = $DB->get_records_sql($sql, $params);
    $peergrades = [];

    foreach ($records as $record) {
        $peergrades[] = $record->grade;
    }

    return $peergrades;
}

/**
 * Get count of an individuals peer grades. Takes into account treat0asgrade
 * @param $peerassessment
 * @param $group
 * @param $user
 * @return int
 */
function peerassessment_get_indcount($peerassessment, $group, $user) {
    global $DB;

    $members = groups_get_members($group->id);
    $peers = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradedby ' . $insql1;
    $conditions[] = 'gradefor = :userid';
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id,
        'userid' => $user->id
    ];

    $params = array_merge($params, $inparams1);

    if (!$peerassessment->treat0asgrade) {
        $conditions[] = 'p.grade > 0';
    }

    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT COUNT(grade) 
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $count = (int)$DB->count_records_sql($sql, $params); 

    if (!$count) {
        return 0;
    } else {
        return $count;
    }
}

/**
 * Get sum of an individuals peer grades. Rounded to two decimal places.
 * @param $peerassessment
 * @param $group
 * @param $user
 * @return int
 */
function peerassessment_get_indpeergradestotal($peerassessment, $group, $user) {
    global $DB;

    $members = groups_get_members($group->id);
    $peers = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradedby ' . $insql1;
    $conditions[] = 'gradefor = :userid';
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id,
        'userid' => $user->id
    ];

    $params = array_merge($params, $inparams1);

    if (!$peerassessment->treat0asgrade) {
        $conditions[] = 'p.grade > 0';
    }

    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT SUM(grade) as s
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $gradesum = $DB->get_record_sql($sql, $params); 

    return $gradesum->s;
}

/**
 * Get count of all peer grades. Takes into account treat0asgrade
 * @param $peerassessment
 * @param $group
 * @return int
 */
function peerassessment_get_groupcount($peerassessment, $group) {
    global $DB;

    $members = groups_get_members($group->id);
    $peers = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);
    list($insql2, $inparams2) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradedby ' . $insql1;
    $conditions[] = 'gradefor ' . $insql2;
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id
    ];

    $params = array_merge($params, $inparams1, $inparams2);

    if (!$peerassessment->treat0asgrade) {
        $conditions[] = 'p.grade > 0';
    }

    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT COUNT(grade) 
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $count = (int)$DB->count_records_sql($sql, $params); 

    if (!$count) {
        return 0;
    } else {
        return $count;
    }
}

/**
 * Get sum of all peer grades. Rounded to two decimal places.
 * @param $peerassessment
 * @param $group
 * @return int
 */
function peerassessment_get_grouppeergradestotal($peerassessment, $group) {
    global $DB;

    $members = groups_get_members($group->id);
    $peers = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);
    list($insql2, $inparams2) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradedby ' . $insql1;
    $conditions[] = 'gradefor ' . $insql2;
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id
    ];

    $params = array_merge($params, $inparams1, $inparams2);

    if (!$peerassessment->treat0asgrade) {
        $conditions[] = 'p.grade > 0';
    }

    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT SUM(grade) as s
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $gradesum = $DB->get_record_sql($sql, $params); 

    return $gradesum->s;
}

/**
 * Get group average. Either simple or adjusted for outlier.
 * @param $peerassessment
 * @param $group
 * @return float
 */
function peerassessment_get_groupaverage($peerassessment, $group) {
    global $DB;

    // Can't calculate grade if student does not belong to any group.
    if (!$group) {
        return null;
    }

    if ($peerassessment->calculationtype == PEERASSESSMENT_SIMPLE) {
        $groupaverage = peerassessment_get_simplegravg($peerassessment, $group);
    } else if ($peerassessment->calculationtype == PEERASSESSMENT_OUTLIER) {
        $groupaverage = peerassessment_get_adjustedgravg($peerassessment, $group);
    } else {
        return null;
    }

    return $groupaverage;
}

/**
 * Get simple group average. Rounded to two decimal places.
 * @param $peerassessment
 * @param $group
 * @return float
 */
function peerassessment_get_simplegravg($peerassessment, $group) {
    global $DB;

    $count = peerassessment_get_groupcount($peerassessment, $group);
    $total = peerassessment_get_grouppeergradestotal($peerassessment, $group);

    if ($count === 0) {
        return 0;
    }

    return round($total / $count, 2);
}

/**
 * Get adjusted group average. Rounded to two decimal places.
 * @param $peerassessment
 * @param $group
 * @return float
 */
function peerassessment_get_adjustedgravg($peerassessment, $group) {
    global $DB;

    $peermarks = array();
    $averagetotal = 0;
    $count = 0;
    $groupaverage = 0;
    $members = groups_get_members($group->id);

    foreach ($members as $member) {
        $standarddev = peerassessment_get_indsd($peerassessment, $group, $member);
        $indaverage = peerassessment_get_simpleindavg($peerassessment, $group, $member);

        $peermarks[$member->id] = new stdClass();
        $peermarks[$member->id]->userid = $member->id;
        $peermarks[$member->id]->standarddev = $standarddev;

        if ($peermarks[$member->id]->standarddev <= get_config('peerassessment', 'standard_deviation')) {
            $peermarks[$member->id]->indaverage = $indaverage;
        } else {
            $peermarks[$member->id]->indaverage = 0;
        }
    }

    // THIS CAN'T BE DONE UNTIL INDIVIDUAL AVERAGES ARE ALL SET TO INDAV OR 0. NEEDS TO BE A SEPARATE FOREACH.
    foreach ($members as $member) {
        $averagetotal = $averagetotal + $peermarks[$member->id]->indaverage;

        if ($peermarks[$member->id]->standarddev <= get_config('peerassessment', 'standard_deviation')) {
            // Spreadsheet is doing a different calculation.
            // Spreadsheet is doing 
            $count = $count + 1;
        }
    }

    $groupaverage = $averagetotal / $count;

    return $groupaverage;
}

/**
 * Get individual average.
 * @param $peerassessment
 * @param $group
 * @return float
 */
function peerassessment_get_individualaverage($peerassessment, $group, stdClass $member) {
    global $DB;

    // Can't calculate grade if student does not belong to any group.
    if (!$group) {
        return null;
    }

    if ($peerassessment->calculationtype == PEERASSESSMENT_SIMPLE) {
        $average = peerassessment_get_simpleindavg($peerassessment, $group, $member);
    } else if ($peerassessment->calculationtype == PEERASSESSMENT_OUTLIER) {
        $average = peerassessment_get_adjustedindavg($peerassessment, $group, $member);
    } else {
        return null;
    }

    return $average;
}

/**
 * Get individual user average.
 * @param $peerassessment
 * @param $group
 * @param $user
 * @return float
 */
function peerassessment_get_simpleindavg($peerassessment, $group, $user) {
    global $DB;

    $count = peerassessment_get_indcount($peerassessment, $group, $user);
    $total = peerassessment_get_indpeergradestotal($peerassessment, $group, $user);

    if ($count === 0) {
        return 0;
    } else {
        return round($total / $count, 2);
    }
}

/**
 * Get adjusted individual user average which takes into account the standard deviation also
 * @param $peerassessment
 * @param $group
 * @param $member
 * @return float
 */
function peerassessment_get_adjustedindavg($peerassessment, $group, $member) {
    global $DB;

    $standarddev = peerassessment_get_indsd($peerassessment, $group, $member);
    $indaverage = peerassessment_get_simpleindavg($peerassessment, $group, $member);
    $groupaverage = peerassessment_get_adjustedgravg($peerassessment, $group);

    if ($standarddev <= get_config('peerassessment', 'standard_deviation')) {
            $indaverage = $indaverage;
    } else {
        $indaverage = round($groupaverage, 2);
    }

    return $indaverage;
}

/**
 * Get standard deviation for individual.
 * @param $peerassessment
 * @param $group
 * @param $user
 * @return float
 */
function peerassessment_get_indsd($peerassessment, $group, $user) {
    global $DB;

    $count = peerassessment_get_indcount($peerassessment, $group, $user);

    if ($count == 0) {
        return '-';
    }

    $peergrades = peerassessment_get_indpeergrades($peerassessment, $group, $user);
    $result = peerassessment_get_pstd_dev($peergrades);

    return round($result, 2);
}

/**
 * Get final grade for individual.
 * @param $peerassessment
 * @param $group
 * @param $member
 * @return float
 */
function peerassessment_get_grade($peerassessment, $group, stdClass $member) {
    global $DB;

    // Can't calculate grade if student does not belong to any group.
    if (!$group) {
        return null;
    }

    if ($peerassessment->calculationtype == PEERASSESSMENT_SIMPLE) {
        $grade = peerassessment_get_simple_grade($peerassessment, $group, $member);
    } else if ($peerassessment->calculationtype == PEERASSESSMENT_OUTLIER) {
        $grade = peerassessment_get_outlier_adjusted_grade($peerassessment, $group, $member);
    } else {
        return null;
    }

    return $grade;
}

/**
 * Get final simple grade for individual.
 * @param $peerassessment
 * @param $group
 * @param $member
 * @return float
 */
function peerassessment_get_simple_grade($peerassessment, $group, stdClass $member) {
    global $CFG, $DB;

    $peermarks = [];

    // Can't calculate grade if student does not belong to any group.
    if (!$group) {
        return null;
    }

    // $multiplier = get_config('peerassessment', 'multiplyby');die($multiplier);
    $multiplier = 5;
    $indavg = peerassessment_get_simpleindavg($peerassessment, $group, $member);
    $gravg = peerassessment_get_simplegravg($peerassessment, $group);
    $submission = $DB->get_record('peerassessment_submission', array('assignment' => $peerassessment->id, 'groupid' => $group->id));

    if (!$submission || !isset($submission->grade)) {
        return '-';
    }

    $gravg = peerassessment_get_simplegravg($peerassessment, $group);
    $peermarks[$member->id] = new stdClass();
    $peermarks[$member->id]->userid = $member->id;
    $peermarks[$member->id]->indaverage = peerassessment_get_simpleindavg($peerassessment, $group, $member);
    $grade = $submission->grade + (($peermarks[$member->id]->indaverage - $gravg) * $multiplier);

    if ($grade > 100) {
        $grade = 100;
    }

    if ($grade < 0) {
        $grade = 0;
    }

    return $grade;
}

/**
 * Get final outlier grade for individual.
 * @param $peerassessment
 * @param $group
 * @param $member
 * @return float
 */
function peerassessment_get_outlier_adjusted_grade($peerassessment, $group, stdClass $member) {
    global $CFG, $DB;

    $peermarks = array();

    // Can't calculate grade if student does not belong to any group.
    if (!$group) {
        return null;
    }

    // $multiplier = get_config('peerassessment', 'multiplyby');
    $multiplier = 4;
    $groupaverage = peerassessment_get_groupaverage($peerassessment, $group);
    $submission = $DB->get_record('peerassessment_submission', array('assignment' => $peerassessment->id, 'groupid' => $group->id));

    if (!$submission || !isset($submission->grade)) {
        return '-';
    }

    $standarddev = peerassessment_get_indsd($peerassessment, $group, $member);
    $indaverage = peerassessment_get_simpleindavg($peerassessment, $group, $member);

    $peermarks[$member->id] = new stdClass();
    $peermarks[$member->id]->userid = $member->id;
    $peermarks[$member->id]->standarddev = $standarddev;

    if ($peermarks[$member->id]->standarddev <= get_config('peerassessment', 'standard_deviation')) {
        $peermarks[$member->id]->indaverage = $indaverage;
    } else {
        $peermarks[$member->id]->indaverage = 0;
    }

    if ($peermarks[$member->id]->standarddev > get_config('peerassessment', 'standard_deviation')) {
        $peermarks[$member->id]->indaverage = $groupaverage;
    }

    $peermarks[$member->id]->mm = round(($peermarks[$member->id]->indaverage - $groupaverage) * $multiplier, 2);

    if (abs($peermarks[$member->id]->mm) < get_config('peerassessment', 'moderation')) {
        $peermarks[$member->id]->mm = 0;
    }

    $grade = $submission->grade + $peermarks[$member->id]->mm;

    if ($grade > 100) {
        $grade = 100;
    }

    if ($grade < 0) {
        $grade = 0;
    }

    return $grade;
}

/**
 * Fill up missing assessments with grade "0"
 */
function peerassessment_fillup() {

}

/**
 * Get submission files.
 * @param $context
 * @param $group
 * @return array
 */
function peerassessment_submission_files($context, $group) {
    $allfiles = array();
    $fs = get_file_storage();

    if ($files = $fs->get_area_files($context->id, 'mod_peerassessment', 'submission', $group->id, 'sortorder', false)) {
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

            $allfiles[] = "<a href='$fileurl'>" . $file->get_filename() . '</a>';
        }
    }

    return $allfiles;
}

/**
 * Get feedback files.
 * @param $context
 * @param $group
 * @return array
 */
function peerassessment_feedback_files($context, $group) {
    $allfiles = array();
    $fs = get_file_storage();

    if ($files = $fs->get_area_files($context->id, 'mod_peerassessment', 'feedback_files', $group->id, 'sortorder', false)) {
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

            $allfiles[] = "<a href='$fileurl'>" . $file->get_filename() . '</a>';
        }
    }

    return $allfiles;
}

/**
 * Get grades given to others by this user.
 * @param $peerassessment
 * @param $user
 * @param $membersgradeable
 * @param $group
 * @return stdClass
 */
function peerassessment_grade_by_user($peerassessment, $user, $membersgradeable, $group) {
    global $DB;

    $members = groups_get_members($group->id);
    $peers = array_keys($members);
    list($insql1, $inparams1) = $DB->get_in_or_equal($peers, SQL_PARAMS_NAMED);

    $conditions[] = 'p.peerassessment = :peerassessment';
    $conditions[] = 'p.groupid = :groupid';
    $conditions[] = 'gradefor ' . $insql1;
    $conditions[] = 'gradedby = :userid';
    $params = [
        'peerassessment' => $peerassessment->id,
        'groupid' => $group->id,
        'userid' => $user->id
    ];

    $params = array_merge($params, $inparams1);
    $conditions = implode(' AND ', $conditions);

    $sql = "SELECT p.gradefor, p.feedback, p.grade
        FROM {peerassessment_peers} p
        WHERE $conditions";

    $mygrades = $DB->get_records_sql($sql, $params);
    $data = new stdClass();

    foreach ($membersgradeable as $member) {
        if (isset($mygrades[$member->id])) {
            $data->feedback[$member->id] = $mygrades[$member->id]->feedback;
            $data->grade[$member->id] = $mygrades[$member->id]->grade;
        } else {
            $data->feedback[$member->id] = '-';
            $data->grade[$member->id] = '-';
        }
    }

    return $data;
}

/**
 * Description.
 * @param $peerassessment
 * @return array
 */
function peerassessment_get_fileoptions($peerassessment) {
    return array('mainfile' => '', 'subdirs' => 0, 'maxbytes' => -1, 'maxfiles' => $peerassessment->maxfiles,
        'accepted_types' => '*', 'return_types' => null);
}

/**
 * Find members of the group that did not submit feedback and graded peers.
 * @param $peerassessment
 * @param $group
 * @return array|bool Returns an array of the users for the specified
 * group or false if no users or an error returned.
 */
function peerassessment_outstanding($peerassessment, $group) {
    global $DB;

    $members = groups_get_members($group->id);

    foreach ($members as $k => $member) {
        if ($DB->get_record('peerassessment_peers', ['peerassessment' => $peerassessment->id, 'groupid' => $group->id,
            'gradedby' => $member->id], 'id', IGNORE_MULTIPLE)) {
            unset($members[$k]);
        }

    }

    return $members;
}

/**
 * Description.
 * @param $context
 * @return array
 */
function peerassessment_teachers($context) {
    global $CFG;

    $contacts = [];
    if (empty($CFG->coursecontact)) {
        return $contacts;
    }
    $coursecontactroles = explode(',', $CFG->coursecontact);
    foreach ($coursecontactroles as $roleid) {
        $contacts += get_role_users($roleid, $context, true);
    }
    return $contacts;
}

/**
 * Save the submission data.
 * @param $peerassessment
 * @param $submission
 * @param $group
 * @param $course
 * @param $cm
 * @param $context
 * @param $data
 * @param $draftitemid
 * @param $context
 * @param $membersgradeable
 */
function peerassessment_save($peerassessment, $submission, $group, $course, $cm, $context, $data, $draftitemid, $membersgradeable) {
    global $USER, $DB;

    // Form has been submitted, commit, display confirmation and redirect.
    // Create submission record if none yet.
    if (!$submission) {
        $submission = new stdClass();
        $submission->assignment = $peerassessment->id;
        $submission->userid = $USER->id;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $submission->groupid = $group->id;

        $submission->id = $DB->insert_record('peerassessment_submission', $submission);

        $params = array(
                'objectid' => $submission->id,
                'context' => $context,
                'other' => array('groupid' => $group->id)
            );

        $event = \mod_peerassessment\event\submission_created::create($params);
        $event->trigger();
    } else {
        // Just update.
        $submission->timemodified = time();
        $DB->update_record('peerassessment_submission', $submission);

        $params = array(
                'objectid' => $submission->id,
                'context' => $context,
                'other' => array('groupid' => $group->id)
            );

        $event = \mod_peerassessment\event\submission_updated::create($params);
        $event->add_record_snapshot('peerassessment_submission', $submission);
        $event->trigger();
    }

    // Save the file submitted.
    // Check if the file is different or the same.
    $fs = get_file_storage();
    $usercontext = context_user::instance($USER->id);
    $draftfiles = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id', false);

    // Check special case when there were no files at the time of submission and none were added.
    $skipfile = false;

    if ($data->files == 0 && count($draftfiles) == 0) {
        $skipfile = true;
    }

    if (!$skipfile) {
        // Get all contenthashes being submitted.
        $newhashes = array();

        foreach ($draftfiles as $file) {
            $newhashes[$file->get_contenthash()] = $file->get_contenthash();
        }

        // Get all contenthashes that are already submitted.
        $files = $fs->get_area_files($context->id, 'mod_peerassessment', 'submission', $group->id, 'sortorder', false);
        $oldhashes = array();
        foreach ($files as $file) {
            $oldhashes[$file->get_contenthash()] = $file->get_contenthash();
        }

        $samehashes = array_intersect($newhashes, $oldhashes);
        $addedhashes = array_diff($newhashes, $oldhashes);
        $deletedhashes = array_diff($oldhashes, $newhashes);
        $filesubmissioncount = count($newhashes);
        $filelist = array();
        $filedeletedcount = count($deletedhashes);

        if ($samehashes) {
            $filelist[] = ' Resubmitted:<br/>' . join('<br/>', $samehashes);
        }

        if ($addedhashes) {
            $filelist[] = ' Added:<br/>' . join('<br/>', $addedhashes);
        }

        if ($deletedhashes) {
            $deletedlist = 'Deleted:<br/>' . join('<br/>', $deletedhashes);
        }

        $filelist = join('<br/>', $filelist);

        if ($deletedhashes) {
            $params = array(
                    'objectid' => $submission->id,
                    'context' => $context,
                    'other' => array(
                        'filedeletedcount' => $filedeletedcount,
                        'deletedlist' => $deletedlist
                        )
                );

            $event = \mod_peerassessment\event\submission_files_deleted::create($params);
            $event->trigger();
        }

        if ($filelist) {
            $params = array(
                    'objectid' => $submission->id,
                    'context' => $context,
                    'other' => array(
                        'filesubmissioncount' => $filesubmissioncount,
                        'filelist' => $filelist
                        )
                );

            $event = \mod_peerassessment\event\submission_files_uploaded::create($params);
            $event->trigger();
        }

        if (count($newhashes) && $oldhashes != $newhashes) {
            // Hashes are different, submission has changed.
            $submission->submissionmodified = time();
            $submission->submissionmodifiedby = $USER->id;

            $DB->update_record('peerassessment_submission', $submission);
        }

        file_save_draft_area_files($draftitemid, $context->id, 'mod_peerassessment', 'submission', $group->id,
            peerassessment_get_fileoptions($peerassessment));
    }

    // Remove existing grades, in case it's an update.
    $DB->delete_records('peerassessment_peers',
        array('peerassessment' => $peerassessment->id, 'groupid' => $group->id, 'gradedby' => $USER->id));

    // Save the grades for your peers.
    foreach ($membersgradeable as $member) {
        $peer = new stdClass();
        $peer->peerassessment = $peerassessment->id;
        $peer->groupid = $group->id;
        $peer->gradedby = $USER->id;
        $peer->gradefor = $member->id;
        $peer->timecreated = time();

        if (isset($data->grade[$member->id])) {
            $peer->grade = $data->grade[$member->id];
        } else {
            $peer->grade = 0;
        }

        $peer->feedback = $data->feedback[$member->id];
        $peer->id = $DB->insert_record('peerassessment_peers', $peer);
        $fullname = fullname($member);

        $params = array(
                'objectid' => $peer->id,
                'context' => $context,
                'relateduserid' => $member->id,
                'other' => array(
                    'grade' => $peer->grade,
                    'fullname' => $fullname
                    )
            );

        $event = \mod_peerassessment\event\peer_grade_created::create($params);
        $event->add_record_snapshot('peerassessment_peers', $peer);
        $event->trigger();
    }

    // Send email confirmation.
    if (!mail_confirmation_submission($course, $submission, $draftfiles, $membersgradeable, $data)) {
        throw new Exception("Submission saved but no email sent.");
    }
}

/**
 * Send confirmation of submission to all members.
 * @param $course
 * @param $submission
 * @param $draftfiles
 * @param $membersgradeable
 * @param $data
 * @return string The formatted ID ready for appending to the email headers.
 */
function mail_confirmation_submission($course, $submission, $draftfiles, $membersgradeable, $data) {
    global $CFG, $USER;

    $subject = get_string('confirmationmailsubject', 'peerassessment', $course->fullname);
    $a = new stdClass();
    $a->time = userdate(time());
    $files = array();

    foreach ($draftfiles as $draftfile) {
        $files[] = $draftfile->get_filename();
    }

    $a->files = implode("\n", $files);
    $grades = '';

    foreach ($membersgradeable as $member) {
        $grades .= fullname($member) . ': ' . $data->grade[$member->id] . "\n";
    }

    $a->grades = $grades;
    $a->url = $CFG->wwwroot . "/mod/peerassessment/view.php?n=" . $submission->assignment;
    $body = get_string('confirmationmailbody', 'peerassessment', $a);

    return email_to_user($USER, core_user::get_noreply_user(), $subject, $body);
}

/**
 * Calculate standard deviation.
 * @param $a
 * @param $sample
 * @return float
 */
function peerassessment_get_pstd_dev(array $a, $sample = false) {
    $n = count($a);

    if ($n === 0) {
        trigger_error("The array has zero elements", E_USER_WARNING);
        return false;
    }

    if ($sample && $n === 1) {
        trigger_error("The array has only 1 element", E_USER_WARNING);
        return false;
    }

    $mean = array_sum($a) / $n;
    $carry = 0.0;

    foreach ($a as $val) {
        $d = ((double) $val) - $mean;

        $carry += ($d * $d);
    }

    if ($sample) {
        --$n;
    }

    return sqrt($carry / $n);
}
