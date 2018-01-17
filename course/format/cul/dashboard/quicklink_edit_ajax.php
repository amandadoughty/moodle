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
 * Quicklinks settings for CUL Course Format
 *
 * @package    course/format
 * @subpackage cul
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>, Tim Gagen
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_login();

if (!confirm_sesskey()) {
    $error = array('error' => get_string('invalidsesskey', 'error'));
    die(json_encode($error));
}

$courseid = required_param('courseid', PARAM_INT);
$name = required_param('name', PARAM_RAW);
$value = required_param('value', PARAM_INT);

// require_login();
// require_capability('moodle/course:update', context_course::instance($courseid));
// require_sesskey();

// Check permision
if (!has_capability('moodle/course:update', context_course::instance($courseid))) {
    header('HTTP/1.1 403 Forbidden');
    die();
}

format_cul_quicklink_visibility($courseid, $name, $value);
list($editurl, $editicon, $editattrs) = format_cul_get_edit_link($courseid, $name, $value);

echo json_encode(['editurl' => $editurl, 'editicon' => $editicon, 'editattrs' => $editattrs]);
die();