<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/.
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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/peerassessment/locallib.php');

/**
 * Module instance settings form
 */
class mod_peerassessment_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $DB, $COURSE;

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('peerassessmentname', 'peerassessment'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'peerassessmentname', 'peerassessment');

        // Adding the standard "intro" and "introformat" fields.
        $this->standard_intro_elements();
        if ($this->current->id) {
            $peerassessment = $DB->get_record('peerassessment', array('id' => $this->current->id), '*', MUST_EXIST);
        }

        // Adding the rest of peerassessment settings, spreeading all them into this fieldset,
        // or adding more fieldsets ('header' elements) if needed for better logic.
        $mform->addElement('header', 'peerassessmentfieldset', get_string('peerassessmentfieldset', 'peerassessment'));
        $mform->addElement('advcheckbox', 'selfgrading', get_string('selfgrading', 'peerassessment'));
        $mform->setType('selfgrading', PARAM_BOOL);
        $mform->addHelpButton('selfgrading', 'selfgrading', 'peerassessment');
        // $mform->disabledIf('selfgrading', 'value1', 'eq|noteq', 'value2');
        // $mform->addRule('selfgrading', $strrequired, 'required', null, 'client');
        // $mform->setAdvanced('selfgrading');

        $mform->addElement('date_time_selector', 'fromdate', get_string('fromdate', 'peerassessment'), array('optional' => true));
        $mform->setDefault('fromdate', time());
        $mform->addHelpButton('fromdate', 'fromdate', 'peerassessment');

        $mform->addElement('date_time_selector', 'duedate', get_string('duedate', 'peerassessment'), array('optional' => true));
        $mform->setDefault('duedate', time() + DAYSECS);
        $mform->addHelpButton('duedate', 'duedate', 'peerassessment');

        $mform->addElement('selectyesno', 'allowlatesubmissions', get_string('allowlatesubmissions', 'peerassessment'));
        $mform->setType('allowlatesubmissions', PARAM_BOOL);
        $mform->addHelpButton('allowlatesubmissions', 'allowlatesubmissions', 'peerassessment');

        $choices = array(1 => 1, 2, 3, 4, 5);
        $mform->addElement('select', 'maxfiles', get_string('maxfiles', 'peerassessment'), $choices);
        $mform->setType('maxfiles', PARAM_INT);
        $mform->addHelpButton('maxfiles', 'maxfiles', 'peerassessment');

        $calculations = array('simple' => 'simple', 'outlier' => 'outlier');
        $mform->addElement('select', 'calculationtype', get_string('calculationtype', 'peerassessment'), $calculations);
        $mform->setType('calculationtype', PARAM_TEXT);
        $mform->setDefault('calculationtype', 'simple');
        $mform->addHelpButton('calculationtype', 'calculationtype', 'peerassessment');
        if (($this->current->id) && has_been_graded($peerassessment)) {
            $mform->freeze('calculationtype');
        }

        // $mform->addElement('text', 'multiplyby', get_string('multiplyby', 'peerassessment'), array('size' => '10'));
        // $mform->setType('multiplyby', PARAM_INT);
        // // $mform->addRule('multiplyby', null, 'required', null, 'client');
        // $mform->addRule('multiplyby', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        // $mform->addHelpButton('multiplyby', 'multiplyby', 'peerassessment');

        $mform->addElement('selectyesno', 'notifylatesubmissions', get_string('notifylatesubmissions', 'peerassessment'));
        $mform->setType('notifylatesubmissions', PARAM_BOOL);
        $mform->addHelpButton('notifylatesubmissions', 'notifylatesubmissions', 'peerassessment');

        $mform->addElement('selectyesno', 'treat0asgrade', get_string('treat0asgrade', 'peerassessment'));
        $mform->setType('treat0asgrade', PARAM_BOOL);
        $mform->setDefault('treat0asgrade', true);
        $mform->addHelpButton('treat0asgrade', 'treat0asgrade', 'peerassessment');

        $mform->addElement('header', 'groupsubmissionsettings', get_string('groupsubmissionsettings', 'peerassessment'));

        $groupings = groups_get_all_groupings($COURSE->id);
        $options = array();
        $options[0] = get_string('none');
        foreach ($groupings as $grouping) {
            $options[$grouping->id] = $grouping->name;
        }

        $name = get_string('submissiongroupingid', 'peerassessment');
        $mform->addElement('select', 'submissiongroupingid', $name, $options);
        $mform->addHelpButton('submissiongroupingid', 'submissiongroupingid', 'peerassessment');
        $mform->disabledIf('submissiongroupingid', 'teamsubmission', 'eq', 0);
        if (($this->current->id) && has_been_graded($peerassessment)) {
            $mform->freeze('calculationtype');
        }

        // Add standard elements, common to all modules.
        $this->standard_coursemodule_elements();

        // NW - DO I NEED TO ADD THIS??  Add admin defaults.
        $this->apply_admin_defaults();

        $this->add_action_buttons();
    }
}