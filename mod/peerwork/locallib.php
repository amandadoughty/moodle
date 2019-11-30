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
 * @package    mod_peerwork
 * @copyright  2013 LEARNING TECHNOLOGY SERVICES
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
define('PEERWORK_STATUS_NOT_SUBMITTED', 0);
define('PEERWORK_STATUS_SUBMITTED', 1);
define('PEERWORK_STATUS_GRADED', 2);
define('PEERWORK_STATUS_NOT_SUBMITTED_CLOSED', 3);
define('PEERWORK_STATUS_RELEASED', 4);

define('PEERWORK_DUEDATE_NOT_USED', 0);
define('PEERWORK_DUEDATE_OK', 1);
define('PEERWORK_DUEDATE_PASSED', 2);

define('PEERWORK_FROMDATE_NOT_USED', 0);
define('PEERWORK_FROMDATE_OK', 1);
define('PEERWORK_FROMDATE_BEFORE', 2);

define('MOD_PEERWORK_JUSTIFICATION_DISABLED', 0);       // No justification required.
define('MOD_PEERWORK_JUSTIFICATION_HIDDEN', 1);         // Justification hidden to students and peers.
define('MOD_PEERWORK_JUSTIFICATION_VISIBLE_ANON', 2);   // Justification visible to all but anonymously.
define('MOD_PEERWORK_JUSTIFICATION_VISIBLE_USER', 3);   // Justification visible to all with identity visible.

define('MOD_PEERWORK_PEER_GRADES_HIDDEN', 0);           // Peer grades hidden to students.
define('MOD_PEERWORK_PEER_GRADES_VISIBLE_ANON', 2);     // Peer grades visible to all but anonymously.
define('MOD_PEERWORK_PEER_GRADES_VISIBLE_USER', 3);     // Peer grades visible to all with identity visible.

require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/grouplib.php');

/**
 * Get peers.
 *
 * @param object $course The course.
 * @param object $peerwork The instance.
 * @param int $groupingid The grouping ID.
 * @param int $group The group ID.
 * @param int $userid The ID of the user that is retrieving its peers.
 * @return array
 */
function peerwork_get_peers($course, $peerwork, $groupingid, $group = null, $userid = null) {
    global $USER;
    $userid = !$userid ? $USER->id : $userid;

    if (!$group) {
        $group = peerwork_get_mygroup($course, $userid, $groupingid);
    }

    $members = groups_get_members($group);
    $membersgradeable = $members;

    if (!$peerwork->selfgrading) {
        unset($membersgradeable[$userid]);
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
 * @return int|null The group id.
 */
function peerwork_get_mygroup($courseid, $userid, $groupingid = 0, $die = true) {
    global $CFG;

    $mygroups = groups_get_all_groups($courseid, $userid, $groupingid);

    if (count($mygroups) == 0) {
        if ($die) {
            print_error("You do not belong to any group.");
        }
        return null;
    } else if (count($mygroups) > 1) {
        if ($die) {
            print_error("You belong to more than one group, this is currently not supported.");
        }
        return null;
    }

    $mygroup = array_shift($mygroups);
    return $mygroup->id;
}

/**
 * Gets the status, one of PEERWORK_STATUS_*
 * @param $peerwork
 * @param int $group returns only groups in the specified grouping.
 */
function peerwork_get_status($peerwork, $group) {
    global $DB;
    $submission = $DB->get_record('peerwork_submission', array('peerworkid' => $peerwork->id, 'groupid' => $group->id));
    $status = new stdClass();
    $duedate = peerwork_due_date($peerwork);

    if ($submission && $submission->released) {
        $status->code = PEERWORK_STATUS_RELEASED;
        $user = $DB->get_record('user', array('id' => $submission->releasedby));
        $status->text = get_string('releasedbyon', 'mod_peerwork', [
            'name' => fullname($user),
            'date' => userdate($submission->released)
        ]);
        return $status;
    }

    if ($submission && $submission->timegraded) {
        $status->code = PEERWORK_STATUS_GRADED;
        $user = $DB->get_record('user', array('id' => $submission->gradedby));
        $status->text = get_string('gradedbyon', 'mod_peerwork', [
            'name' => fullname($user),
            'date' => userdate($submission->timegraded)
        ]);
        return $status;
    }

    if (!$submission && $duedate == PEERWORK_DUEDATE_PASSED) {
        $status->code = PEERWORK_STATUS_NOT_SUBMITTED_CLOSED;
        $text = get_string('nothingsubmittedyetduedatepassednago', 'mod_peerwork', format_time(time() - $peerwork->duedate));
        $status->text = $text;
        return $status;
    }

    if (!$submission) {
        $status->code = PEERWORK_STATUS_NOT_SUBMITTED;
        $status->text = get_string('nothingsubmittedyet', 'mod_peerwork');
        return $status;
    }

    if ($duedate == PEERWORK_DUEDATE_PASSED) {
        $user = $DB->get_record('user', array('id' => $submission->userid));
        $status->code = PEERWORK_STATUS_SUBMITTED;
        $status->text = get_string('firstsubmittedbyon', 'mod_peerwork', [
            'name' => fullname($user),
            'date' => userdate($submission->timecreated)
        ]) . ' ' . get_string('duedatepassedago', 'mod_peerwork', format_time(time() - $peerwork->duedate));
        return $status;

    } else {
        $user = $DB->get_record('user', array('id' => $submission->userid));
        $status->code = PEERWORK_STATUS_SUBMITTED;
        $status->text = get_string('firstsubmittedbyon', 'mod_peerwork', [
            'name' => fullname($user),
            'date' => userdate($submission->timecreated)
        ]);
        return $status;
    }
}

/**
 * Get the justifications.
 *
 * @param int $peerworkid The peerwork ID.
 * @param int $groupid The group ID.
 * @return Array indexed by grader, then graded.
 */
function peerwork_get_justifications($peerworkid, $groupid) {
    global $DB;
    $justifications = $DB->get_records('peerwork_justification', ['peerworkid' => $peerworkid, 'groupid' => $groupid]);
    return array_reduce($justifications, function($carry, $row) {
        if (!isset($carry[$row->gradedby])) {
            $carry[$row->gradedby] = [];
        }
        $carry[$row->gradedby][$row->gradefor] = $row;
        return $carry;
    }, []);
}

/**
 * Get the justifications received.
 *
 * @param int $peerworkid The peerwork ID.
 * @param int $groupid The group ID.
 * @param int $userid The user ID.
 * @return Array indexed by grader
 */
function peerwork_get_justifications_received($peerworkid, $groupid, $userid) {
    global $DB;
    $justifications = $DB->get_records('peerwork_justification', [
        'peerworkid' => $peerworkid,
        'groupid' => $groupid,
        'gradefor' => $userid
    ]);
    return array_reduce($justifications, function($carry, $row) {
        $carry[$row->gradedby] = $row;
        return $carry;
    }, []);
}

/**
 * Get the peer grades.
 *
 * @param int $peerworkid The peerwork ID.
 * @param int $groupid The group ID.
 * @param int $userid The user ID.
 * @return Array indexed by criteriaid, then graderid.
 */
function peerwork_get_peer_grades_received($peerworkid, $groupid, $userid) {
    global $DB;
    $peergrades = $DB->get_records('peerwork_peers', [
        'peerwork' => $peerworkid,
        'groupid' => $groupid,
        'gradefor' => $userid
    ]);
    return array_reduce($peergrades, function($carry, $row) {
        if (!isset($carry[$row->criteriaid])) {
            $carry[$row->criteriaid] = [];
        }
        $carry[$row->criteriaid][$row->gradedby] = $row;
        return $carry;
    }, []);
}

/**
 * Was due date used and has it passed?
 * @param $peerwork
 */
function peerwork_due_date($peerwork) {
    if (!$peerwork->duedate) {
        return PEERWORK_DUEDATE_NOT_USED;
    }

    if ($peerwork->duedate < time()) {
        return PEERWORK_DUEDATE_PASSED;
    } else {
        return PEERWORK_DUEDATE_OK;
    }
}

/**
 * Was from date used and is it after?
 * @param $peerwork
 */
function peerwork_from_date($peerwork) {
    if (!$peerwork->fromdate) {
        return PEERWORK_FROMDATE_NOT_USED;
    }

    if ($peerwork->fromdate > time()) {
        return PEERWORK_FROMDATE_BEFORE;
    } else {
        return PEERWORK_FROMDATE_OK;
    }
}

/**
 * Whether the student can view their grade and feedback.
 *
 * @param object $status The status.
 * @return bool
 */
function peerwork_can_student_view_grade_and_feedback_from_status($status) {
    return $status->code == PEERWORK_STATUS_RELEASED;
}

/**
 * Return whether students can view their peer grades.
 *
 * @param object $peerwork The peerwork instance.
 * @return bool
 */
function peerwork_can_students_view_peer_grades($peerwork) {
    return in_array($peerwork->peergradesvisibility, [
        MOD_PEERWORK_PEER_GRADES_VISIBLE_ANON,
        MOD_PEERWORK_PEER_GRADES_VISIBLE_USER,
    ]);
}

/**
 * Return whether students can view their peer justifications.
 *
 * @param object $peerwork The peerwork instance.
 * @return bool
 */
function peerwork_can_students_view_peer_justification($peerwork) {
    return in_array($peerwork->justification, [
        MOD_PEERWORK_JUSTIFICATION_VISIBLE_ANON,
        MOD_PEERWORK_JUSTIFICATION_VISIBLE_USER,
    ]);
}

/**
 * Whether the submission was graded, from its status.
 *
 * @param object $status The status.
 * @return bool
 */
function peerwork_was_submission_graded_from_status($status) {
    return in_array($status->code, [PEERWORK_STATUS_GRADED, PEERWORK_STATUS_RELEASED]);
}

/**
 * Can student $user submit/edit based on the current status?
 * @param $peerwork
 */
function peerwork_is_open($peerwork, $groupid = 0) {
    global $DB;
    $status = new stdClass();
    $status->code = false;

    // Is it before from date?
    $fromdate = peerwork_from_date($peerwork);
    if ($fromdate == PEERWORK_FROMDATE_BEFORE) {
        $status->text = get_string('assessmentnotopenyet', 'mod_peerwork');
        return $status;
    }

    $course = $DB->get_record('course', array('id' => $peerwork->course), '*', MUST_EXIST);
    $group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);

    // Is it already graded?
    $pstatus = peerwork_get_status($peerwork, $group);
    if (peerwork_was_submission_graded_from_status($pstatus)) {
        $status->text = get_string('assessmentalreadygraded', 'mod_peerwork');
        return $status;
    }

    // Is it after due date?
    $duedate = peerwork_due_date($peerwork);
    if ($duedate == PEERWORK_DUEDATE_PASSED) {
        if ($peerwork->allowlatesubmissions) {
            $status->code = true;
            $status->text = get_string('latesubmissionsallowedafterduedate', 'mod_peerwork');
        } else {
            $status->text = get_string('latesubmissionsnotallowedafterduedate', 'mod_peerwork');
        }
        return $status;
    }

    // If we are here it means it's between from date and due date.
    $status->code = true;
    $status->text = get_string('assessmentopen', 'mod_peerwork');
    return $status;
}

/**
 * Get grades for all peers in a group
 * @param $peerwork
 * @param $group
 */
function peerwork_get_peer_grades($peerwork, $group, $membersgradeable = null, $full = true) {
    global $DB;

    $return = new stdClass();

    $peers = $DB->get_records('peerwork_peers', array('peerwork' => $peerwork->id, 'groupid' => $group->id));
    $grades = array();
    $feedback = array();

    foreach ($peers as $peer) {
        $grades[$peer->criteriaid][$peer->gradedby][$peer->gradefor] = $peer->grade;
        $feedback[$peer->criteriaid][$peer->gradedby][$peer->gradefor] = $peer->feedback;
    }

    // Anthing not proceessed about gets a default string.
    if ($full) {
        foreach (array_keys($grades) as $critid) {
            foreach ($membersgradeable as $member1) {
                if (!isset($grades[$member1->id])) {
                    $grades[$member1->id] = [];
                }
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
    }

    $return->grades = $grades;
    $return->feedback = $feedback;

    return $return;
}

/**
 * Get the number of peers graded.
 *
 * @param object $peerwork The intance.
 * @param object $group The group.
 */
function peerwork_get_number_peers_graded($peerworkid, $groupid) {
    global $DB;
    return $DB->count_records_select('peerwork_peers', 'peerwork = ? AND groupid = ?', [$peerworkid, $groupid],
        'COUNT(DISTINCT gradedby)');
}

/**
 * Calculate and return the WebPA result, but cached for the request.
 *
 * @param object $peerwork The module instance.
 * @param object $group The group.
 * @param object $submission The submission, to prevent a double fetch.
 * @return mod_peerwork\webpa_result|null Null when the submission was not found or graded.
 */
function peerwork_get_cached_webpa_result($peerwork, $group, $submission = null) {
    return peerwork_get_webpa_result($peerwork, $group, $submission);
}

/**
 * Calculate and return the WebPA result.
 *
 * @param object $peerwork The module instance.
 * @param object $group The group.
 * @param object $submission The submission, to prevent a double fetch.
 * @return mod_peerwork\webpa_result|null Null when the submission was not found or graded.
 */
function peerwork_get_webpa_result($peerwork, $group, $submission = null) {
    global $DB;

    if (!$submission) {
        $submission = $DB->get_record('peerwork_submission', [
            'peerworkid' => $peerwork->id,
            'groupid' => $group->id
        ]);
    }

    if (!$submission || !isset($submission->grade)) {
        return;
    } else if ($submission->groupid != $group->id || $submission->peerworkid != $peerwork->id) {
        throw new coding_exception('Invalid submission provided');
    }

    $groupmark = $submission->grade;
    $paweighting = $submission->paweighting / 100;
    $noncompletionpenalty = $peerwork->noncompletionpenalty / 100;

    $marks = [];
    $members = groups_get_members($group->id);
    foreach ($members as $member) {
        $awarded = peerwork_grade_by_user($peerwork, $member, $members);
        $marks[$member->id] = array_filter($awarded->grade, function($grade) {
            return is_numeric($grade);
        });
    }

    $calculator = new \mod_peerwork\webpa_calculator($paweighting, $noncompletionpenalty);
    return $calculator->calculate($marks, $groupmark);
}

/**
 * Create HTML links to files that have been submitted to the assignment.
 * Used by view.php and details.php
 * @return string[] array of formated <A href= strings, possibly empty array
 */
function peerwork_submission_files($context, $group) {
    $allfiles = array();
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'mod_peerwork', 'submission', $group->id, 'sortorder', false)) {
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

            $allfiles[] = "<a href='$fileurl'>" . $file->get_filename() . '</a>';
        }
    }
    return $allfiles;
}


function peerwork_feedback_files($context, $group) {
    $allfiles = array();
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'mod_peerwork', 'feedback_files', $group->id, 'sortorder', false)) {
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());

            $allfiles[] = "<a href='$fileurl'>" . $file->get_filename() . '</a>';
        }
    }
    return $allfiles;
}

/**
 * Total all the grades awarded by the $user to other members of the group.
 *
 * @param object $peerwork The instance.
 * @param object $user The user.
 * @param object[] $membersgradeable The user's peers.
 */
function peerwork_grade_by_user($peerwork, $user, $membersgradeable) {
    global $DB;

    $data = new stdClass();
    $data->grade = array();
    $data->feedback = array();

    $mygrades = $DB->get_records('peerwork_peers', array('peerwork' => $peerwork->id,
        'gradedby' => $user->id), '', 'id,criteriaid,gradefor,feedback,grade');

    foreach ($mygrades as $grade) {
        $peerid = $grade->gradefor;
        @$data->grade[$peerid] += $grade->grade;
        @$data->feedback[$peerid] |= $grade->feedback;
    }

    // Make sure all the peers have an entry in the returning data array.
    foreach ($membersgradeable as $member) {
        if (!array_key_exists( $member->id, $data->grade)) {
            $data->grade[$member->id] = '-';
        }
        if (!array_key_exists( $member->id, $data->feedback)) {
            $data->feedback[$member->id] = '-';
        }
    }
    return $data;
}

/**
 * Get submission file options.
 *
 * @param stdClass $peerwork The instance from database.
 * @return array
 */
function peerwork_get_fileoptions($peerwork) {
    return array('mainfile' => '', 'subdirs' => 0, 'maxbytes' => -1, 'maxfiles' => $peerwork->maxfiles,
        'accepted_types' => '*', 'return_types' => null);
}

/**
 * Find members of the group that did not submit feedback and graded peers.
 * @param $peerwork
 * @param $group
 */
function peerwork_outstanding($peerwork, $group) {
    global $DB;

    $members = groups_get_members($group->id);
    foreach ($members as $k => $member) {
        if ($DB->get_record('peerwork_peers', array('peerwork' => $peerwork->id, 'groupid' => $group->id,
            'gradedby' => $member->id), 'id', IGNORE_MULTIPLE)) {
            unset($members[$k]);
        }

    }
    return $members;
}

function peerwork_teachers($context) {
    global $CFG;

    $contacts = array();
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
 * Get the local grade of a user.
 *
 * @param int $peerworkid The peerwork ID.
 * @param int $submissionid The submission ID.
 * @param int $userid The user ID.
 * @return object|null
 */
function peerwork_get_user_local_grade($peerworkid, $submissionid, $userid) {
    global $DB;
    $record = $DB->get_record('peerwork_grades', [
        'peerworkid' => $peerworkid,
        'submissionid' => $submissionid,
        'userid' => $userid
    ]);

    if (!$record) {
        return null;
    }

    return $record->revisedgrade != null ? $record->revisedgrade : $record->grade;
}

/**
 * Get local grades.
 *
 * @param int $peerworkid The peerwork ID.
 * @param int $submissionid The submission ID.
 * @return array Indexed by userid.
 */
function peerwork_get_local_grades($peerworkid, $submissionid) {
    global $DB;
    $records = $DB->get_records('peerwork_grades', [
        'peerworkid' => $peerworkid,
        'submissionid' => $submissionid
    ], '', '*');
    $userids = array_map(function($record) {
        return $record->userid;
    }, $records);
    return array_combine($userids, $records);
}

/**
 * Update local grades.
 *
 * @param object $peerwork The instance.
 * @param object $group The group.
 * @param object $submission The submission.
 * @param array $userids The list of user IDs.
 * @param array|null $revisedgrades The full list of revised grades indexed by member id. A missing key means not revised.
 *                                  If null, we assume that none should be changed.
 */
function peerwork_update_local_grades($peerwork, $group, $submission, $userids, $revisedgrades = null) {
    global $DB;

    $result = peerwork_get_webpa_result($peerwork, $group, $submission);
    $existingrecords = peerwork_get_local_grades($peerwork->id, $submission->id);

    foreach ($userids as $userid) {
        $record = isset($existingrecords[$userid]) ? $existingrecords[$userid] : null;
        if (!$record) {
            $record = (object) [
                'peerworkid' => $peerwork->id,
                'submissionid' => $submission->id,
                'userid' => $userid,
            ];
        }

        $record->grade = $result->get_grade($userid);
        if ($revisedgrades !== null) {
            $record->revisedgrade = $revisedgrades[$userid] ?? null;
        }

        if (!empty($record->id)) {
            $DB->update_record('peerwork_grades', $record);
        } else {
            $DB->insert_record('peerwork_grades', $record);
        }
    }

    if ($submission->released) {
        peerwork_update_grades($peerwork);
    }
}

/**
 * Student has provided some grades on their peers using the add_submission_form, save into database and trigger events.
 *
 * @param unknown $peerwork
 * @param unknown $submission - database record in stdClass
 * @param unknown $group
 * @param unknown $course
 * @param unknown $cm
 * @param unknown $context
 * @param unknown $data
 * @param unknown $draftitemid
 * @param unknown $membersgradeable
 * @throws Exception
 */
function peerwork_save($peerwork, $submission, $group, $course, $cm, $context, $data, $draftitemid, $membersgradeable) {
    global $CFG, $USER, $DB;

    $event = \mod_peerwork\event\assessable_submitted::create(['context' => $context]);
    $event->trigger();

    // Create submission record if none yet.
    if (!$submission) {
        $submission = new stdClass();
        $submission->peerworkid = $peerwork->id;
        $submission->userid = $USER->id;
        $submission->timecreated = time();
        $submission->timemodified = time();
        $submission->groupid = $group->id;

        $submission->id = $DB->insert_record('peerwork_submission', $submission);

        $params = array(
            'objectid' => $submission->id,
            'context' => $context,
            'other' => array('groupid' => $group->id)
        );

        $event = \mod_peerwork\event\submission_created::create($params);
        $event->trigger();

    } else {
        // Just update.
        $submission->timemodified = time();
        $DB->update_record('peerwork_submission', $submission);

        $params = array(
            'objectid' => $submission->id,
            'context' => $context,
            'other' => array('groupid' => $group->id)
        );

        $event = \mod_peerwork\event\submission_updated::create($params);
        $event->add_record_snapshot('peerwork_submission', $submission);
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
        $files = $fs->get_area_files($context->id, 'mod_peerwork', 'submission', $group->id, 'sortorder', false);
        $oldhashes = array();
        foreach ($files as $file) {
            $oldhashes[$file->get_contenthash()] = $file->get_contenthash();
        }

        $addedhashes = array_diff($newhashes, $oldhashes);
        $deletedhashes = array_diff($oldhashes, $newhashes);

        if ($deletedhashes) {
            $params = array(
                'objectid' => $submission->id,
                'context' => $context,
                'other' => array(
                    'deletedlist' => $deletedhashes
                )
            );

            $event = \mod_peerwork\event\submission_files_deleted::create($params);
            $event->trigger();
        }

        if ($addedhashes) {
            $params = array(
                'objectid' => $submission->id,
                'context' => $context,
                'other' => array(
                    'filelist' => $addedhashes
                )
            );

            $event = \mod_peerwork\event\submission_files_uploaded::create($params);
            $event->trigger();
        }

        if (count($newhashes) && $oldhashes != $newhashes) {
            // Hashes are different, submission has changed.
            $submission->submissionmodified = time();
            $submission->submissionmodifiedby = $USER->id;

            $DB->update_record('peerwork_submission', $submission);
        }

        file_save_draft_area_files($draftitemid, $context->id, 'mod_peerwork', 'submission', $group->id,
            peerwork_get_fileoptions($peerwork));
    }

    // Remove existing grades, in case it's an update.
    $DB->delete_records('peerwork_peers',
        array('peerwork' => $peerwork->id, 'groupid' => $group->id, 'gradedby' => $USER->id));

    // Save the grades.
    $pac = new mod_peerwork_criteria($peerwork->id);
    $criteria = $pac->get_criteria();
    foreach ($criteria as $criterion) {
        foreach ($membersgradeable as $member) {
            $peer = new stdClass();
            $peer->peerwork = $peerwork->id;
            $peer->criteriaid = $criterion->id;
            $peer->groupid = $group->id;
            $peer->gradedby = $USER->id;
            $peer->gradefor = $member->id;
            $peer->feedback = null;
            $peer->timecreated = time();
            $field = 'grade_idx_'. $criterion->id;
            if (isset($data->{$field}[$peer->gradefor])) {
                $peer->grade = max(0, (int) $data->{$field}[$peer->gradefor]);
            } else {
                $peer->grade = 0;
            }

            $peer->id = $DB->insert_record('peerwork_peers', $peer, true);

            $params = array(
                'objectid' => $peer->id,
                'context' => $context,
                'relateduserid' => $member->id,
                'other' => array(
                    'grade' => $peer->grade,
                )
            );

            $event = \mod_peerwork\event\peer_grade_created::create($params);
            $event->add_record_snapshot('peerwork_peers', $peer);
            $event->trigger();
        }
    }

    // Save the justification.
    if ($peerwork->justification != MOD_PEERWORK_JUSTIFICATION_DISABLED) {
        foreach ($membersgradeable as $member) {
            $params = [
                'peerworkid' => $peerwork->id,
                'groupid' => $group->id,
                'gradefor' => $member->id,
                'gradedby' => $USER->id
            ];
            $record = $DB->get_record('peerwork_justification', $params);
            if (!$record) {
                $record = (object) $params;
            }
            $record->justification = isset($data->justifications[$member->id]) ? $data->justifications[$member->id] : '';
            if (!empty($record->id)) {
                $DB->update_record('peerwork_justification', $record);
            } else {
                $DB->insert_record('peerwork_justification', $record);
            }
        }
    }

    // Suggest to check, and eventually update, the completion state.
    $completion = new completion_info($course);
    if ($completion->is_enabled($cm) && $peerwork->completiongradedpeers) {
        $completion->update_state($cm, COMPLETION_COMPLETE);
    }

    // Send email confirmation.
    if (!mod_peerwork_mail_confirmation_submission($course, $submission, $draftfiles, $membersgradeable, $data)) {
        throw new moodle_exception("Submission saved but no email sent.");
    }
}

/**
 * Mail confirmation.
 *
 * @param object $course The course.
 * @param object $submission The submission.
 * @param array $draftfiles The files.
 * @param array $membersgradeable The members.
 * @param object $data The data.
 * @return bool
 */
function mod_peerwork_mail_confirmation_submission($course, $submission, $draftfiles, $membersgradeable, $data) {
    global $CFG, $USER;
    // TODO Mail confirmation.
    return true;

    $subject = get_string('confirmationmailsubject', 'peerwork', $course->fullname);

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

    $a->url = $CFG->wwwroot . "/mod/peerwork/view.php?n=" . $submission->peerworkid;

    $body = get_string('confirmationmailbody', 'peerwork', $a);
    return email_to_user($USER, core_user::get_noreply_user(), $subject, $body);
}

function peerwork_get_pstd_dev(array $a, $sample = false) {
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
