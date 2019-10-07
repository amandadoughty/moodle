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
 * Reset order of favourites functionality for culcourse_listing block.
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('AJAX_SCRIPT', true);

global $PAGE;

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_sesskey();
require_login();
$PAGE->set_context(context_system::instance());

$config = get_config('block_culcourse_listing');
$preferences = block_culcourse_listing_get_preferences();
$favourites = block_culcourse_listing_get_favourite_api_courses($preferences);
// Update the user preference.
$favourites = block_culcourse_listing_reorder_favourites_api($favourites);
// Render the favourites.
$renderer = $PAGE->get_renderer('block_culcourse_listing');
$renderer->set_preferences($preferences);
$renderer->set_config($config);
$chelper = new block_culcourse_listing_helper();
$chelper->set_favourites($favourites);
// Return the rendered favourites.
echo $renderer->favourites($chelper, $favourites, 'favourite_');