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

require_sesskey();
require_login();

// Update the favourites.
$result = block_culcourse_listing_update_from_favourites_api();
$result['data'] = false;

if (($result['action'] == 'add') && $result['cid']) {
    $chelper = new block_culcourse_listing_helper();
    $config = get_config('block_culcourse_listing'); 
    $preferences = block_culcourse_listing_get_preferences();
    $course = $DB->get_record('course', array('id' => $result['cid']), '*', MUST_EXIST);
    $course = new core_course_list_element($course);
    $renderer = $PAGE->get_renderer('block_culcourse_listing');
    $renderer->set_preferences($preferences);
    $renderer->set_config($config); 
    $move = [];
    $move['spacer'] = $this->output->image_url('spacer', 'moodle')->out();
    $move['moveupimg'] = $this->output->image_url('t/up', 'moodle')->out();
    $move['moveup'] = true;
    $move['movedown'] = false;
    $coursebox = new block_culcourse_listing\output\coursebox($chelper, $config, $preferences, $course, '', $move, true);
    $data = $coursebox->export_for_template($renderer);

    $result['data'] = $data;
}

echo json_encode($result);



