<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/
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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/mod/peerassessment/lib.php');
require_once($CFG->dirroot . '/lib/grouplib.php');
require_once($CFG->dirroot . '/mod/peerassessment/add_submission_form.php');
require_once($CFG->dirroot . '/mod/peerassessment/locallib.php');
require_once($CFG->libdir . '/csvlib.class.php');

$id = required_param('id', PARAM_INT);
$groupid = required_param('groupid', PARAM_INT);
$cm = get_coursemodule_from_id('peerassessment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
$peerassessment = $DB->get_record('peerassessment', array('id' => $cm->instance), '*', MUST_EXIST);
$submission = $DB->get_record('peerassessment_submission', array('assignment' => $peerassessment->id, 'groupid' => $groupid));
$members = groups_get_members($group->id);
$groupingid = $peerassessment->submissiongroupingid;

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$params = array(
        'objectid' => $submission->id,
        'context' => $context,
        'other' => array('groupid' => $group->id)
    );

$event = \mod_peerassessment\event\submission_exported::create($params);
$event->add_record_snapshot('peerassessment_submission', $submission);
$event->trigger();

require_capability('mod/peerassessment:grade', $context);
$membersgradeable = peerassessment_get_peers($course, $peerassessment, $groupingid, $groupid);

$data = array();
$header = array('Student');
foreach ($members as $member) {
    $row = array(fullname($member));
    $header[] = 'Grade for ' . fullname($member);
    $header[] = 'Feedback for ' . fullname($member);
    // How I graded others.
    $grades = peerassessment_grade_by_user($peerassessment, $member, $membersgradeable);
    foreach ($members as $peer) {
        $row[] = $grades->grade[$peer->id];
        $row[] = html_to_text($grades->feedback[$peer->id]);
    }
    $row[] = peerassessment_get_individualaverage($peerassessment, $group, $member);
    $row[] = peerassessment_get_grade($peerassessment, $group, $member);
    $data[] = $row;
}
$header[] = 'Average group score';
$header[] = 'Final grade';

$filename = clean_filename($peerassessment->name . "-$id-$groupid");
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);
$csvexport->add_data($header);
foreach ($data as $row) {
    $csvexport->add_data($row);
}

// Add information common to the whole group
$csvexport->add_data(array());

$row = array('Group average');
$row[] = peerassessment_get_groupaverage($peerassessment, $group);
$csvexport->add_data($row);

$row = array('Course work grade');
if (isset($submission->grade)) {
    $row[] = $submission->grade;
}
$csvexport->add_data($row);

$row = array('Graded on');
if (isset($submission->timegraded)) {

    $row[] = userdate($submission->timegraded);
}
$csvexport->add_data($row);

$row = array('Graded by');
if (isset($submission->gradedby)) {
    $teacher = $DB->get_record('user', array('id' => $submission->gradedby));
    $row[] = fullname($teacher);
}
$csvexport->add_data($row);

$row = array('Feedback');
if (isset($submission->feedbacktext)) {
    $row[] = html_to_text($submission->feedbacktext);
}
$csvexport->add_data($row);

$csvexport->download_file();
