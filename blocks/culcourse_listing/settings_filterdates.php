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
 * @copyright  2016 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('forms/filter_dates_form.php');

// /blocks/culcourse_listing/settings_filterdates.php

require_login();
require_capability('block/culcourse_listing:adminsettings', context_system::instance());

admin_externalpage_setup('manageblocks'); // Shortcut function sets up page for block admin

// Set up the page
$PAGE->set_url('/blocks/moodletxt/settings_filters.php');
$PAGE->set_heading(get_string('adminheaderfilters', 'block_culcourse_listing'));
$PAGE->set_title(get_string('admintitlefilters', 'block_culcourse_listing'));
$PAGE->set_button(''); // Clear editing button
$PAGE->navbar->add(get_string('navculcourselisting', 'block_culcourse_listing'), $CFG->wwwroot . '/admin/settings.php?section=blocksettingculcourse_listing', navigation_node::TYPE_CUSTOM, 'culcourse_listing');
$PAGE->navbar->add(get_string('navfilters', 'block_culcourse_listing'), null, navigation_node::TYPE_CUSTOM, 'culcourse_listing');

$filterForm = new filter_dates_form();
$results = $DB->get_records('block_culcourse_listing_prds');

$i = 0;
$data = new stdClass();
$data->daterangename = array();
$data->daterangetype = array();

foreach($results as $result) {
	$data->daterangeid[] = $result->id;
    $data->daterangename[] = $result->name;
    $data->daterangetype[] = $result->type;
    // date_selectors within repeat_elements use the correct naming convention
    // for individual parts eg startdate[day][$i] but not for the value that is
    // posted. this is in the format "startdate[$i]" as a string.
    $data->{"startdate[$i]"} = $result->startdate;
    $data->{"enddate[$i]" } = $result->enddate;
    $i++;
}

$filterForm->set_data($data);
$formData = $filterForm->get_data();

// Form processing
if ($formData != null) {
// print_r($formData);
	$i = $formData->daterangerepeats - 1;

    while($i >= 0) {
        if(isset($formData->daterangename[$i]) && !empty($formData->daterangename[$i])) {
            $filter = new stdClass();
            $filter->id = $formData->daterangeid[$i];
            $filter->name = $formData->daterangename[$i];
            $filter->type = $formData->daterangetype[$i];
            $filter->startdate = $formData->startdate[$i];
            $filter->enddate = $formData->enddate[$i];
            $filter->enddate += DAYSECS - 1; // Set to end of the chosen day.

            if ($filter->id) {
            	$DB->update_record('block_culcourse_listing_prds', $filter);
            } else {
	            // insert or update? Add hidden value for id
	            $DB->insert_record('block_culcourse_listing_prds', $filter);
	        }
        }

        $i--;
    } 

    // @TODO redirect   
}

$output = $PAGE->get_renderer('block_culcourse_listing');

// Chuck the page out and go home
echo($output->header());
$filterForm->display();
echo($output->footer());