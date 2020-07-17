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
 * Details.
 *
 * @package    mod_peerwork
 * @copyright  2013 LEARNING TECHNOLOGY SERVICES
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/peerwork/lib.php');
require_once($CFG->dirroot . '/lib/grouplib.php');
require_once($CFG->dirroot . '/mod/peerwork/locallib.php');

$id = required_param('id', PARAM_INT);
$groupid = required_param('groupid', PARAM_INT);

$cm             = get_coursemodule_from_id('peerwork', $id, 0, false, MUST_EXIST);
$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$peerwork = $DB->get_record('peerwork', array('id' => $cm->instance), '*', MUST_EXIST);
$submission     = $DB->get_record('peerwork_submission', array('peerworkid' => $peerwork->id, 'groupid' => $groupid));
$members        = groups_get_members($groupid);
$group          = $DB->get_record('groups', array('id' => $groupid), '*', MUST_EXIST);
$status         = peerwork_get_status($peerwork, $group);

// Print the standard page header and check access rights.
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$PAGE->set_url('/mod/peerwork/details.php', ['id' => $cm->id, 'groupid' => $groupid]);
$PAGE->set_title(format_string($peerwork->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
require_capability('mod/peerwork:grade', $context);

// Start the form, initialise with some data.
$fileoptions = mod_peerwork_details_form::$fileoptions;
$draftitemid = file_get_submitted_draft_itemid('feedback_files');
file_prepare_draft_area($draftitemid, $context->id, 'mod_peerwork', 'feedback_files', $group->id, $fileoptions);
$data = [
    'paweighting' => $peerwork->paweighting,
    'feedback_files' => $draftitemid
];

// Load the submission data.
if ($submission && peerwork_was_submission_graded_from_status($status)) {
    $data['grade'] = $submission->grade;
    $data['paweighting'] = $submission->paweighting;
    $data['feedback'] = [
        'text' => $submission->feedbacktext ?? '',
        'format' => $submission->feedbackformat ?? FORMAT_HTML
    ];
}

// Get the justifications.
$justifications = [];
if ($peerwork->justification != MOD_PEERWORK_JUSTIFICATION_DISABLED) {
    $justifications = peerwork_get_justifications($peerwork->id, $group->id);
}

$lockedgraders = mod_peerwork_get_locked_graders($peerwork->id);
$isopen = peerwork_is_open($peerwork, $group->id);
$canunlock = !empty($isopen->code) && $submission && !$submission->timegraded;
$mform = new mod_peerwork_details_form($PAGE->url->out(false), [
    'peerwork' => $peerwork,
    'justifications' => $justifications,
    'submission' => $submission,
    'members' => $members,
    'canunlock' => $canunlock,
]);
$data['groupname'] = $group->name;
$data['status'] = $status->text;
$submissionfiles = peerwork_submission_files($context, $group);
$data['submission'] = empty($submissionfiles) ? get_string('nothingsubmitted', 'peerwork') : implode('<br/>', $submissionfiles);


// Get the peer grades awarded so far, then for each criteria
// output a HTML tabulation of the peers and the grades awarded and received.
// TODO instead of HTML fragment can we build this with form elments?
$grades = peerwork_get_peer_grades($peerwork, $group, $members, false);
$pac = new mod_peerwork_criteria( $peerwork->id );
$data['peergradesawarded'] = '';
// Is there per criteria justification enabled?
$justenabled = $peerwork->justification != MOD_PEERWORK_JUSTIFICATION_DISABLED;
$justcrit = $peerwork->justificationtype == MOD_PEERWORK_JUSTIFICATION_CRITERIA;
$justenabledcrit = $justenabled && $justcrit;

foreach ($pac->get_criteria() as $criteria) {
    $critid = $criteria->id;
    $extraclasses = $justenabledcrit ? 'crit' : '';

    $t = new html_table();
    $t->attributes['class'] = "userenrolment $extraclasses";
    $t->id = 'mod-peerwork-summary-table';
    $t->head[] = '';
    $t->caption = $criteria->description;

    foreach ($members as $member) {
        $t->head[] = fullname($member);

        $label = fullname($member);
        if ($canunlock && in_array($member->id, $lockedgraders)) {
            $label .= $OUTPUT->action_icon('#',
                new pix_icon('t/locked', get_string('editinglocked', 'mod_peerwork'), 'core'),
                null,
                [
                    'data-peerworkid' => $peerwork->id,
                    'data-graderid' => $member->id,
                    'data-graderfullname' => fullname($member),
                    'data-graderunlock' => 'true',
                ]
            );
        }

        $row = new html_table_row();
        $row->cells = array();
        $row->cells[] = $label;

        foreach ($members as $peer) {
            if (!isset($grades->grades[$critid]) || !isset($grades->grades[$critid][$member->id])
                    || !isset($grades->grades[$critid][$member->id][$peer->id])) {
                $row->cells[] = '-';
            } else {
                $feedbacktext = '';

                if (
                    $justenabledcrit &&
                    isset($justifications[$member->id]) &&
                    isset($justifications[$member->id][$critid]) &&
                    isset($justifications[$member->id][$critid][$peer->id])
                ) {
                    $feedbacktext = print_collapsible_region(
                        $justifications[$member->id][$critid][$peer->id]->justification,
                        'peerwork-feedback',
                        'peerwork-feedback-' . $member->id . '-' . $critid . '-' . $peer->id,
                        shorten_text($justifications[$member->id][$critid][$peer->id]->justification, 20), '', true, true);
                }

                $row->cells[] = $grades->grades[$critid][$member->id][$peer->id] . $feedbacktext;
            }
        }
        $t->data[] = $row;
    }
    $data['peergradesawarded'] .= html_writer::table($t); // Write the table for this criterion into the HTML placeholder element.
}

// If assignment has been graded then pass the required data to create a table showing calculated grades.
if (peerwork_was_submission_graded_from_status($status)) {
    $result = peerwork_get_webpa_result($peerwork, $group);
    $localgrades = peerwork_get_local_grades($peerwork->id, $submission->id);

    $data['finalgrades'] = [];
    foreach ($members as $member) {
        $data['finalgrades'][] = array(
            'memberid' => $member->id,
            'fullname' => fullname($member),
            'contribution' => $result->get_score($member->id),
            'calcgrade' => $result->get_preliminary_grade($member->id),
            'penalty' => $result->get_non_completion_penalty($member->id),
            'finalweightedgrade' => $result->get_grade($member->id),
            'revisedgrade' => $localgrades[$member->id]->revisedgrade ?? null
        );
    }
}
$mform->set_data($data);

if ($mform->is_cancelled()) {
    // Form cancelled, redirect.
    redirect(new moodle_url('view.php', ['id' => $cm->id]));

} else if (($data = $mform->get_data())) {

    // We only save anything when the grade was given.
    if ($data->grade !== null) {
        $grader = new mod_peerwork\group_grader($peerwork, $groupid, $submission);
        $grader->set_grade($data->grade, $data->paweighting);
        $grader->set_revised_grades($data->revisedgrades);
        $grader->set_feedback($data->feedback['text'], $data->feedback['format'], $draftitemid);
        $grader->commit();

        redirect(new moodle_url('details.php', array('id' => $id, 'groupid' => $groupid)),
            get_string('gradesandfeedbacksaved', 'mod_peerwork'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    // Redirect to home page because there were no changes.
    redirect(new moodle_url('view.php', ['id' => $cm->id]));
}

//
// Form should now be setup to display, so do the output.
//
$params = array(
    'objectid' => $cm->id,
    'context' => $context,
    'other' => array('groupid' => $group->id)
);
$event = \mod_peerwork\event\submission_grade_form_viewed::create($params);
$event->trigger();

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
