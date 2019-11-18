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
 * Helps moodle-block_culcourse_listing-category to serve AJAX requests
 *
 * @see block_culcourse_listing_renderer::coursecat_include_js()
 * @see block_culcourse_listing_renderer::coursecat_ajax()
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

global $PAGE;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/blocks/culcourse_listing/renderer.php');
require_once($CFG->dirroot.'/blocks/culcourse_listing/locallib.php');

require_sesskey(); // Gotta have the sesskey.
require_login(); // Gotta be logged in (of course).

$PAGE->set_context(context_system::instance());

$config = get_config('block_culcourse_listing');
$preferences = block_culcourse_listing_get_preferences();
$chelper = new block_culcourse_listing_helper();

if($config->filtertype == 'date') {
    global $DB;

    $daterangeperiods = $DB->get_records('block_culcourse_listing_prds');
    $chelper->set_daterange_periods($daterangeperiods);
}

$renderer = $PAGE->get_renderer('block_culcourse_listing');
$renderer->set_config($config);
$renderer->set_preferences($preferences);


echo json_encode($renderer->coursecat_ajax($chelper));