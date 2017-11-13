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
 * CUL Course Format Information
 *
 * A collapsed format that solves the issue of the 'Scroll of Death' when a course has many sections. All sections
 * except zero have a toggle that displays that section. One or more sections can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer.
 *
 * @package    course/format
 * @subpackage culcourse
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/course/format/culcourse/togglelib.php');

$context = context_course::instance($course->id);

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure all sections are created.
$courseformat = course_get_format($course);
$course = $courseformat->get_course();
course_create_sections_if_missing($course, range(0, $course->numsections));

$renderer = $PAGE->get_renderer('format_culcourse');

$defaulttogglepersistence = clean_param(get_config('format_culcourse', 'defaulttogglepersistence'), PARAM_INT);

if ($defaulttogglepersistence == 1) {
    user_preference_allow_ajax_update('culcourse_toggle_' . $course->id, PARAM_RAW);
    $userpreference = get_user_preferences('culcourse_toggle_' . $course->id);
} else {
    $userpreference = null;
}
$renderer->set_user_preference($userpreference);

$defaultuserpreference = clean_param(get_config('format_culcourse', 'defaultuserpreference'), PARAM_INT);
$renderer->set_default_user_preference($defaultuserpreference);

$PAGE->requires->string_for_js('hidefromothers', 'format_culcourse');
$PAGE->requires->string_for_js('showfromothers', 'format_culcourse');

$PAGE->requires->js_init_call('M.format_culcourse.init', array(
    $course->id,
    $userpreference,
    $course->numsections,
    $defaulttogglepersistence,
    $defaultuserpreference));

// $PAGE->requires->yui_module('moodle-format_culcourse-dragdrop-interceptor', 'M.format_culcourse.init_dragdrop_interceptor',
//             array(array(
//                 'courseid' => $course->id,
//                 'ajaxurl' => 0,
//                 'config' => 0,
//             )), null, true);

$tcsettings = $courseformat->get_settings();

$renderer->print_multiple_section_page($course, null, null, null, null);

// Include course format js module.
$PAGE->requires->js('/course/format/culcourse/format.js');