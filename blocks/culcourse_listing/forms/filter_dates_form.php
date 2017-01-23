<?php
// This file is part of Moodle - http://moodle.org/
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
 * Admin settings for CUL Course Listing
 *
 * @package    block
 * @subpackage culcourse_listing
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class filter_dates_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;

        $mform =& $this->_form;
        $mform->addElement('header', 'setdateranges', get_string('setdateranges_header', 'block_culcourse_listing'));
        
        $repeatarray = array();
        $repeatedoptions = array();

        $repeatarray[] = $mform->createElement('hidden', 'daterangeid');
        $repeatedoptions['daterangeid']['type'] = PARAM_INT;
        $label = get_string('daterangename', 'block_culcourse_listing');
        $repeatarray[] = $mform->createElement('text', 'daterangename', $label);
        $repeatedoptions['daterangename']['type'] = PARAM_RAW;

        $label = get_string('daterangetype', 'block_culcourse_listing');

        $options = array(
            0 => get_string('yeartype', 'block_culcourse_listing'),
            1 => get_string('periodtype', 'block_culcourse_listing')
            );

        $repeatarray[] = $mform->createElement('select', 'daterangetype', $label, $options);
        $nonamecheck = array('daterangename', 'eq', null);
        $repeatedoptions['daterangetype']['disabledif'] = $nonamecheck;
        $repeatedoptions['daterangetype']['type'] = PARAM_RAW;

        $label = get_string('daterangestart', 'block_culcourse_listing');
        $repeatarray[] = $mform->createElement('date_selector', 'startdate', $label, array('optional' => false));
        $repeatedoptions['startdate']['disabledif'] = $nonamecheck;

        $label = get_string('daterangeend', 'block_culcourse_listing');
        $repeatarray[] = $mform->createElement('date_selector', 'enddate', $label, array('optional' => false));
        $repeatedoptions['enddate']['disabledif'] = $nonamecheck;

        $repeatarray[] = $mform->createElement('static', 'dummyspacer', '');
        $repeatedoptions['daterangename']['type'] = PARAM_RAW;

        $repeatno = $DB->count_records('block_culcourse_listing_prds');
        $repeatno += 1;   

        $this->repeat_elements(
            $repeatarray, 
            $repeatno,
            $repeatedoptions, 
            'daterangerepeats', 
            'daterangeaddfields', 
            1, 
            get_string('addmore', 'block_culcourse_listing'), 
            true
            );

        // Buttons for activity.
        $this->add_action_buttons(true, get_string('save','block_culcourse_listing'));
    }

    public function definitiongroups() {
        global $CFG, $DB, $PAGE;



        // Add group in repeated element (with extra inheritance).
        // $repeatarray = array();
        // $group = $mform->createElement('group', 'repeatgroup', 'repeatgroup', $groupelements, null, false);
        

        // Add named group in repeated element.
        // $groupelements = array(
        //     $mform->createElement('text', 'repeatnamedgroupel1', 'repeatnamedgroupel1'),
        //     $mform->createElement('text', 'repeatnamedgroupel2', 'repeatnamedgroupel2')
        // );
        // $group = $mform->createElement('group', 'repeatnamedgroup', 'repeatnamedgroup', $groupelements, null, true);
        // $this->repeat_elements(array($group), 2, array('repeatnamedgroup[repeatnamedgroupel1]' => array('type' => PARAM_INT),
        //     'repeatnamedgroup[repeatnamedgroupel2]' => array('type' => PARAM_INT)), 'repeatablenamedgroup', 'add', 0);



        $mform =& $this->_form;
        $mform->addElement('header', 'setdateranges', get_string('setdateranges_header', 'block_culcourse_listing'));
        
        $repeatarray = array();
        $repeatedoptions = array();

        $repeatarray[] = $mform->createElement('hidden', 'daterangeid');
        $repeatedoptions['daterangeid']['type'] = PARAM_INT;
        $label = get_string('daterangename', 'block_culcourse_listing');
        $repeatarray[] = $mform->createElement('text', 'daterangename', $label);
        $repeatedoptions['daterangename']['type'] = PARAM_RAW;

        $label = get_string('daterangetype', 'block_culcourse_listing');
        $options = array(
            0 => get_string('yeartype', 'block_culcourse_listing'),
            1 => get_string('periodtype', 'block_culcourse_listing')
            );
        $repeatarray[] = $mform->createElement('select', 'daterangetype', $label, $options);
        $nonamecheck = array('daterangename', 'eq', null);
        $repeatedoptions['daterangetype']['disabledif'] = $nonamecheck;
        $repeatedoptions['daterangetype']['type'] = PARAM_RAW;

        $label = get_string('daterangestart', 'block_culcourse_listing');
        $repeatarray[] = $mform->createElement('date_selector', 'startdate', $label, array('optional' => false));
        $repeatedoptions['startdate']['disabledif'] = $nonamecheck;

        $label = get_string('daterangeend', 'block_culcourse_listing');
        $repeatarray[] = $mform->createElement('date_selector', 'enddate', $label, array('optional' => false));
        $repeatedoptions['enddate']['disabledif'] = $nonamecheck;

        $group = $mform->createElement('group', 'repeatnamedgroup', 'repeatnamedgroup', $repeatarray, null, true);

        $repeatno = $DB->count_records('block_culcourse_listing_prds');
        $repeatno += 1;   

        $this->repeat_elements(
            array($group), 
            $repeatno,
            $repeatedoptions, 
            'daterangerepeats', 
            'daterangeaddfields', 
            1, 
            get_string('addmore', 'block_culcourse_listing'), 
            true
            );


        // Buttons for activity.
        $this->add_action_buttons(true, get_string('save','block_culcourse_listing'));
    }
}