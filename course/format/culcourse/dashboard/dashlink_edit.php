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

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once(dirname(__FILE__) . '/../../../lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$courseid = required_param('courseid', PARAM_INT);
$action = required_param('action', PARAM_INT);
// $linktype = optional_param('linktype', 0, PARAM_INT);
$name = optional_param('name', null, PARAM_RAW);
$value = optional_param('value', 0, PARAM_INT);
$copy = optional_param('copy', null, PARAM_RAW);
$moveto = optional_param('moveto', null, PARAM_RAW);
$cancelcopy = optional_param('cancelcopy', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

// This page should always redirect
$url = new moodle_url('/course/format/culcourse/dashlink_edit.php');

foreach (compact('name', 'copy', 'moveto', 'cancelcopy', 'confirm') as $key => $value) {
    if ($value) {
        $url->param($key, $value);
    }
}

$url->param('value', $value);
$PAGE->set_url($url);

require_login();

$usercanedit = has_capability('moodle/course:update', context_course::instance($courseid));

if (!$usercanedit) {
    print_error('noeditcoursesettings', 'format_culcourse');
}

if ($action == SHOWHIDE) {
    if ($name) {
        format_culcourse_quicklink_visibility($courseid, $name, $value);
        redirect(new moodle_url('/course/view.php', array('id' => $courseid)));
    } else {
        print_error('noname', 'format_culcourse');
    }
}
var_dump($copy);
var_dump($name);
if ($action == MOVE) {
    if (!empty($moveto) && !empty($name) && confirm_sesskey()) {
//         $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

//         if ($name == 'quicklinksequence') {
//             $linkcopy = $USER->linkcopy;

//             if (!ismovingquicklink($courseid)) {
//                 print_error('needcopy', '', "view.php?id=$courseid");
//             }

//             format_culcourse_dashlink_move($courseid, $name, $linkcopy, $moveto);
//             unset($USER->linkcopy);
//             unset($USER->linkcopycourse);        
//         } else if ($name == 'activitysequence') {
//             $linkcopy = $USER->linkcopy;

//             if (!ismovingactivitylink($courseid)) {
//                 print_error('needcopy', '', "view.php?id=$courseid");
//             }

//             format_culcourse_dashlink_move($courseid, $name, $linkcopy, $moveto);
//             unset($USER->linkcopy);
//             unset($USER->linkcopycourse);
//         }

//         redirect(course_get_url($course));

    } else if (!empty($copy) && !empty($name) && confirm_sesskey()) { // value = link
        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        if ($name == 'quicklinksequence') {
            $USER->linkcopy = $copy;
            $USER->linkcopycourse = $courseid;
        } else if ($name == 'activitysequence') {
            $USER->linkcopy = $copy;
            $USER->linkcopycourse = $courseid;
        }

        redirect(course_get_url($course));

    } else if (!empty($cancelcopy) and confirm_sesskey()) { // value = course module
//         $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
//         unset($USER->linkcopy);
//         unset($USER->linkcopy);
//         redirect(course_get_url($course);
    } else {
        print_error('unknowaction');
    }
} else {
    print_error('unknowaction');
}

// die('vboo');