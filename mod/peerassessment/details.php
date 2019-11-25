<?php
// This file is part of 3rd party created module for Moodle - http://moodle.org/
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
require_once($CFG->dirroot . '/mod/peerassessment/grade_form.php');

$id = required_param('id', PARAM_INT);
$groupid = required_param('groupid', PARAM_INT);
$cm = get_coursemodule_from_id('peerassessment', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$peerassessment = $DB->get_record('peerassessment', array('id' => $cm->instance), '*', MUST_EXIST);
$submission = $DB->get_record('peerassessment_submission', array('assignment' => $peerassessment->id, 'groupid' => $groupid));
$members = groups_get_members($groupid);
$group = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
$status = peerassessment_get_status($peerassessment, $group);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

// Print the page header.
$PAGE->set_url('/mod/peerassessment/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($peerassessment->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// If teacher then display statuses for all groups.
require_capability('mod/peerassessment:grade', $context);

$mform = new mod_peerassessment_grade_form();
$draftitemid = file_get_submitted_draft_itemid('feedback_files');
file_prepare_draft_area($draftitemid, $context->id, 'mod_peerassessment', 'feedback_files',
    $groupid, peerassessment_get_fileoptions($peerassessment));
$data = array('id' => $id, 'groupid' => $groupid, 'feedback_files' => $draftitemid);

if ($status->code == PEERASSESSMENT_STATUS_GRADED) {
    $data['feedback']['text'] = $submission->feedbacktext;
    $data['feedback']['format'] = $submission->feedbackformat;
    $data['grade'] = $submission->grade;
}

$mform->set_data($data);

if ($mform->is_cancelled()) {
    // Form cancelled, redirect.
    redirect(new moodle_url('view.php', array('id' => $cm->id)));
    return;
} else if (($data = $mform->get_data())) {
    // Form has been submitted.
    if (!$submission) {
        $submission = new stdClass();
        $submission->assignment = $peerassessment->id;
        $submission->groupid = $group->id;
    }
    $submission->grade = $data->grade;
    $submission->gradedby = $USER->id;
    $submission->timegraded = time();
    $submission->feedbacktext = $data->feedback['text'];
    $submission->feedbackformat = $data->feedback['format'];

    // add final grade here
    //$submission->finalgrade = peerassessment_get_grade($peerassessment, $group, $member);
    if (isset($submission->id)) {
        $DB->update_record('peerassessment_submission', $submission);
    } else {
        $submission->id = $DB->insert_record('peerassessment_submission', $submission);
    }

    // Update grades for every group member.
    $members = groups_get_members($group->id);

    foreach ($members as $member) {
        peerassessment_update_grades($peerassessment, $member->id);
    }
    
    // Save the file submitted.
    file_save_draft_area_files($draftitemid, $context->id, 'mod_peerassessment', 'feedback_files',
        $group->id, mod_peerassessment_grade_form::$fileoptions);

    $params = array(
                'objectid' => $submission->id,
                'context' => $context,
                'other' => array(
                    'groupid' => $group->id,
                    'groupname' => $group->name,
                    'grade' => $data->grade
                    )
            );

    $event = \mod_peerassessment\event\submission_graded::create($params);
    $event->add_record_snapshot('peerassessment_submission', $submission);
    $event->trigger();

    redirect(new moodle_url('details.php', array('id' => $id, 'groupid' => $groupid)));
}

$params = array(
                'objectid' => $cm->id,
                'context' => $context,
                'other' => array('groupid' => $group->id)
            );

$event = \mod_peerassessment\event\submission_grade_form_viewed::create($params);
$event->trigger();

echo $OUTPUT->header();
echo $OUTPUT->heading("Group " . $group->name);
echo $OUTPUT->box('Status: ' . $status->text);

$submissionfiles = peerassessment_submission_files($context, $group);
echo $OUTPUT->box('Submission: ' . implode(',', $submissionfiles) . $OUTPUT->help_icon('submissiongrading', 'peerassessment'));

$t = new html_table();
$t->attributes['class'] = 'userenrolment';
$t->id = 'mod-peerassessment-summary-table';
$t->head[] = '';
$grades = peerassessment_get_peer_grades($peerassessment, $group, $members);
// Add Averages 1 line.
$indaverages = array('<b>Average</b>');

// PUTTING IN METHOD GET_GRADE.

foreach ($members as $member) {
    $t->head[] = fullname($member);
    $row = new html_table_row();;
    $row->cells = array();
    $row->cells[] = fullname($member);

    foreach ($members as $peer) {
        $feedbacktext = '';

        if ($grades->feedback[$member->id][$peer->id] != '-') {
            $feedbacktext = print_collapsible_region($grades->feedback[$member->id][$peer->id], 'peerassessment-feedback',
                'peerassessment-feedback-' . $member->id . '-' . $peer->id,
                shorten_text($grades->feedback[$member->id][$peer->id], 20), '', true, true);
        }

        $row->cells[] = $grades->grades[$member->id][$peer->id] . $feedbacktext;

    }
    $t->data[] = $row;
    // Add Averages 2 lines.
    $indaverage = peerassessment_get_individualaverage($peerassessment, $group, $member);

    if ($indaverage == 0) {
        $indaverage = '-';
    }

    $indaverages[] = '<b>' . $indaverage . '</b>';
}

// Add Averages 1 line.
$t->data[] = $indaverages;
echo html_writer::table($t);

// Add Averages 2 lines.
$gravg = peerassessment_get_groupaverage($peerassessment, $group);

if ($gravg == 0) {
    $gravg = '-';
}

echo $OUTPUT->box("Group Average grade: $gravg " . $OUTPUT->help_icon('groupaverage', 'peerassessment'));

// If graded then show grade for submission and adjusted grades for each peer.
if ($status->code == PEERASSESSMENT_STATUS_GRADED) {
    echo $OUTPUT->box_start();
    echo $OUTPUT->heading("Final grades ". $OUTPUT->help_icon('finalgrades', 'peerassessment'), 3);
    echo $OUTPUT->box_start();
    echo $OUTPUT->box("Grade by teacher: $submission->grade");
    echo $OUTPUT->box_end();

    $t = new html_table();
    $t->attributes['class'] = 'userenrolment';
    $t->id = 'mod-peerassessment-summary-table';
    $t->head = array('Name', 'Grade');

    foreach ($members as $member) {
        $gradinginfo = grade_get_grades(
            $course->id,
            'mod',
            'peerassessment',
            $peerassessment->id,
            array_keys($members)
        );

        $grade = $gradinginfo->items[0]->grades[$member->id];
        $gradetxt = '';

        // Format mixed bool/integer parameters.
        $grade->hidden = (empty($grade->hidden)) ? 0 : $grade->hidden;
        $grade->locked = (empty($grade->locked)) ? 0 : $grade->locked;
        $grade->overridden = (empty($grade->overridden)) ? 0 : $grade->overridden;

        if ($grade->overridden) {
            $gradetxt .= get_string('overridden', 'mod_peerassessment');
        }

        $t->data[] = array(fullname($member), $grade->str_grade . $gradetxt);
    }

    echo html_writer::table($t);
    echo $OUTPUT->box_end();
    echo $OUTPUT->box_start();
    echo $OUTPUT->heading("Feedback ". $OUTPUT->help_icon('teacherfeedback', 'peerassessment'), 3);
    echo $OUTPUT->box($submission->feedbacktext);
    $feedbackfiles = peerassessment_feedback_files($context, $group);
    echo $OUTPUT->box('Feedback files: ' . implode(',', $feedbackfiles));
    echo $OUTPUT->box_end();
}

$mform->display();

echo $OUTPUT->footer();
