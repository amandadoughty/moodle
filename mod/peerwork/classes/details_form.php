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
 * @package    mod_peerwork
 * @copyright  2013 LEARNING TECHNOLOGY SERVICES
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Creates UI elements for the tutor to enter an overall grade to a submission.
 */
class mod_peerwork_details_form extends moodleform {

    /** @var bool Whether the page requirements were initialised. */
    protected $pageinitialised = false;

    public static $fileoptions = array('mainfile' => '', 'subdirs' => 1, 'maxbytes' => -1, 'maxfiles' => -1,
        'accepted_types' => '*', 'return_types' => null);

    // Define the form.
    protected function definition() {
        global $USER, $CFG, $COURSE;

        $this->init_page_requirements();

        $mform = $this->_form;
        $userid = $USER->id;
        $strrequired = get_string('required');

        $peerwork = $this->_customdata['peerwork'];
        $members = $this->_customdata['members'];
        $justifications = $this->_customdata['justifications'];

        $mform->addElement('header', 'mod_peerwork_details', get_string('general'));
        $mform->addElement('static', 'groupname', get_string('group'));
        $mform->addElement('static', 'status', get_string('status'));

        $mform->addElement('header', 'mod_peerwork_peers', get_string('peersubmissionandgrades', 'mod_peerwork'));
        $mform->addElement('static', 'submission', get_string('submission', 'peerwork'));
        $mform->addHelpButton('submission', 'submission', 'peerwork');

        // This gets replaced in details.php with a table of grades peers have awarded.
        $mform->addElement('static', 'peergradesawarded', '');

        if ($peerwork->justification != MOD_PEERWORK_JUSTIFICATION_DISABLED) {
            $mform->addElement('header', 'justificationshdr', get_string('justifications', 'mod_peerwork'));
            foreach ($members as $gradedby) {
                $rows = [];
                $theirjustifs = !empty($justifications[$gradedby->id]) ? $justifications[$gradedby->id] : [];
                foreach ($members as $gradefor) {
                    if (!$peerwork->selfgrading && $gradedby->id == $gradefor->id) {
                        continue;
                    }
                    $justif = isset($theirjustifs[$gradefor->id]) ? $theirjustifs[$gradefor->id]->justification : null;
                    $rows[] = new html_table_row([
                        fullname($gradefor),
                        ($justif ? s($justif) : html_writer::tag('em', get_string('nonegiven', 'mod_peerwork')))
                    ]);
                }
                $t = new html_table();
                $t->data = $rows;
                $mform->addElement(
                    'static',
                    "justif_{$gradedby->id}",
                    get_string('justificationbyfor', 'mod_peerwork', fullname($gradedby)),
                    html_writer::table($t)
                );
            }
        }

        $mform->addElement('header', 'mod_peerwork_grading', get_string('tutorgrading', 'mod_peerwork'));

        $mform->addElement('text', 'grade', get_string('groupgradeoutof100', 'mod_peerwork'), ['maxlength' => 15, 'size' => 10]);
        $mform->setType('grade', PARAM_INT);

        $mform->addElement('text', 'paweighting', get_string('paweighting', 'mod_peerwork'), ['maxlength' => 15, 'size' => 10]);
        $mform->setType('paweighting', PARAM_INT);

        foreach ($members as $member) {
            $mform->addElement('hidden', 'grade_' . $member->id, '');
            $mform->setType('grade_' . $member->id, PARAM_RAW); // We don't want the value to be forced to 0.
        }

        $mform->addElement('static', 'finalgrades', get_string('calculatedgrades', 'mod_peerwork'));

        $mform->addElement('editor', 'feedback', get_string('feedback', 'peerwork'), ['rows' => 6]);
        $mform->setType('feedback', PARAM_CLEANHTML);

        $mform->addElement('filemanager', 'feedback_files', get_string('feedbackfiles', 'peerwork'),
            null, self::$fileoptions);

        $this->add_action_buttons();
    }

    /**
     * Get the data.
     *
     * @return object
     */
    public function get_data() {
        $data = parent::get_data();
        if (!is_object($data)) {
            return $data;
        }

        $revisedgrades = [];
        foreach ($data as $key => $value) {
            if (strpos($key, 'grade_') === 0) {
                $memberid = (int) substr($key, 6);
                $grade = unformat_float($value);
                $revisedgrades[$memberid] = $grade !== null ? max(0, min(100, $grade)) : null;
                unset($data->{$key});
            }
        }

        $data->revisedgrades = $revisedgrades;

        return $data;
    }

    /**
     * Called from details.php to populate the form from existing data.
     */
    public function set_data($data) {
        global $OUTPUT, $PAGE;

        if (array_key_exists('finalgrades', $data)) {

            $t = new html_table();
            $t->id = 'mod-peerwork-grader-table';
            $t->head = [
                get_string('name'),
                get_string('contibutionscore', 'mod_peerwork') . $OUTPUT->help_icon('contibutionscore', 'mod_peerwork'),
                get_string('calculatedgrade', 'mod_peerwork') . $OUTPUT->help_icon('calculatedgrade', 'mod_peerwork'),
                get_string('penalty', 'mod_peerwork'),
                get_string('finalweightedgrade', 'mod_peerwork'),
                get_string('revisedgrade', 'mod_peerwork') . $OUTPUT->help_icon('revisedgrade', 'mod_peerwork'),
            ];

            $totalcalculated = 0;
            $totalfinalweighted = 0;
            foreach ($data['finalgrades'] as $member) {
                $row = new html_table_row();

                $default = $member['calcgrade'];
                $revisedgrade = $member['revisedgrade'];

                $row->cells[] = $member['fullname'];
                $row->cells[] = format_float($member['contribution'], 4);
                $row->cells[] = format_float($member['calcgrade'], 2);
                $row->cells[] = format_float($member['penalty'] * 100, 0) . '%';
                $row->cells[] = format_float($member['finalweightedgrade'], 2);
                $row->cells[] = $this->_form->createElement('text', 'grade_' . $member['memberid'], '',
                    ['maxlength' => 15, 'size' => 10, 'value' => format_float($revisedgrade ?? null, 5)])->toHtml();

                $totalcalculated += $member['calcgrade'];
                $totalfinalweighted += $member['finalweightedgrade'];

                $t->data[] = $row;
            }

            // Add totals.
            $row = new html_table_row();
            $row->attributes['class'] = 'grading-summary-totals';
            $calculatedtotal = new html_table_cell(format_float($totalcalculated, 2));
            $calculatedtotal->attributes = [
                'class' => 'total-calculated-grade',
                'data-total' => $totalcalculated
            ];
            $finalweightedtotal = new html_table_cell(format_float($totalfinalweighted, 2));
            $finalweightedtotal->attributes = [
                'class' => 'total-final-weighted-grade',
                'data-total' => $totalfinalweighted
            ];
            $revisedtotal = new html_table_cell();
            $revisedtotal->attributes['class'] = 'total-revised-grade';
            $row->cells = [
                get_string('total'),
                '',
                $calculatedtotal,
                '',
                $finalweightedtotal,
                $revisedtotal,
            ];
            $t->data[] = $row;

            $data['finalgrades'] = html_writer::table($t);

        } else {
            $data['finalgrades'] = html_writer::tag('em', get_string('notyetgraded', 'mod_peerwork'));
        }

        return parent::set_data($data);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['grade'] < 0 || $data['grade'] > 100) {
            $errors['grade'] = get_string('invalidgrade', 'mod_peerwork');
        }

        if ($data['paweighting'] < 0 || $data['paweighting'] > 100) {
            $errors['paweighting'] = get_string('invalidpaweighting', 'mod_peerwork');
        }

        return $errors;
    }

    /**
     * Init the page requirements.
     *
     * @return void
     */
    protected function init_page_requirements() {
        global $PAGE;
        if ($this->pageinitialised) {
            return;
        }
        $this->pageinitialised = true;
        $PAGE->requires->js_call_amd('mod_peerwork/revised-grades-total-calculator', 'init', ['#mod-peerwork-grader-table']);
    }
}
