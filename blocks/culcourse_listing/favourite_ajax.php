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
 * Edit favourites functionality for culcourse_listing block.
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/renderer.php');

require_sesskey();
require_login();
$PAGE->set_context(context_system::instance());

$action = required_param('action', PARAM_RAW);
$cid = required_param('cid', PARAM_INT);
// Edit the favourites.
$success = block_culcourse_listing_edit_favourites($action, $cid);

if ($success !== false) { // Could be an empty array.
	block_culcourse_listing_edit_favourites_api($action, $cid);
	$preferences = block_culcourse_listing_get_preferences();
	$favourites = block_culcourse_listing_get_favourite_courses($preferences);
	$chelper = new block_culcourse_listing_helper();
	$course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
	$course = new core_course_list_element($course);
	$renderer = $PAGE->get_renderer('block_culcourse_listing');
	$coursebox = new block_culcourse_listing\output\coursebox($course, false, false, $favourites);
	$data = $coursebox->export_for_template($renderer);
} else {
	$data = ['error' => get_string('favouritefailed', 'block_culcourse_listing')];
}

echo json_encode($data);