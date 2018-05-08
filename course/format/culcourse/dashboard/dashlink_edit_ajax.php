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
require_once(dirname(__FILE__) . '/../../../lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$courseid = required_param('courseid', PARAM_INT);
$action = required_param('action', PARAM_INT);
// $linktype = optional_param('linktype', 0, PARAM_INT);
$name = optional_param('name', null, PARAM_RAW);
$showhide = optional_param('showhide', 0, PARAM_INT);
$copy = optional_param('copy', null, PARAM_RAW);
$moveto = optional_param('moveto', null, PARAM_RAW);
$cancelcopy = optional_param('cancelcopy', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

// This page should always redirect
$url = new moodle_url('/course/format/culcourse/dashlink_edit.php');

foreach (compact('name', 'copy', 'moveto', 'cancelcopy', 'confirm') as $key => $value) {
    if ($value) {
        $url->param($key, $value);
    }
}

$url->param('showhide', $showhide);
$PAGE->set_url($url);

require_login();

$usercanedit = has_capability('moodle/course:update', context_course::instance($courseid));

if (!$usercanedit) {
    print_error('noeditcoursesettings', 'format_culcourse');
}

if ($action == SHOWHIDE) {
    if ($name) {
        format_culcourse_quicklink_visibility($courseid, $name, $showhide);
        redirect(course_get_url($course));
    } else {
        print_error('noname', 'format_culcourse');
    }
}

if ($action == MOVE) {
    if (!empty($moveto) && !empty($copy) && !empty($name) && confirm_sesskey()) {

        $updated = format_culcourse_dashlink_move($courseid, $name, $copy, $moveto); 

        if (!$updated) {
            print_error('courseformatmissing');
        }

        // redirect(course_get_url($course));
    
    } else {
        print_error('unknowaction');
    }
} else {
    print_error('unknowaction');
}