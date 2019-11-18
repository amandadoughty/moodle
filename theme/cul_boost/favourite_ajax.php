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

global $PAGE;

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_sesskey();
require_login();

$PAGE->set_context(context_system::instance());
$action = required_param('action', PARAM_RAW);
$cid = required_param('cid', PARAM_INT);
// Update the user preference if it exists.
$favourites = theme_cul_boost_edit_favourites($action, $cid);
// Update the Favourites API.
theme_cul_boost_edit_favourites_api($action, $cid);
// Retrieve the favourites again in case the update did not work, we want  
// to ensure the link reflects the current status of favourites.
$renderer = $PAGE->get_renderer('theme_cul_boost', 'core');
$favourites = $renderer::get_user_favourites();

if (in_array($cid, $favourites)) {
	$newaction = 'remove';
} else {
	$newaction = 'add';
}

$properties = array();
$properties['action'] = $action;
$properties['newaction'] = $newaction;
$properties['text'] = get_string('favourite' . $newaction, 'theme_cul_boost');
echo json_encode($properties);