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
 * Topics course format.  Display the whole course as "CUL Course" made of modules.
 *
 * @package format_culcourse
 * @copyright 2006 The Open University
 * @author N.D.Freear@open.ac.uk, and others.
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

$context = context_course::instance($course->id);
// Retrieve course format option fields and add them to the $course object.
$course = course_get_format($course)->get_course();
$ajaxurl = '/course/format/culcourse/dashboard/dashlink_edit_ajax.php';

if (($marker >=0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_culcourse');

if (!empty($displaysection)) {
    $renderer->print_single_section_page($course, null, null, null, null, $displaysection);
} else {
    $renderer->print_multiple_section_page($course, null, null, null, null);
}

user_preference_allow_ajax_update('format_culcourse_expanded' . $course->id, PARAM_INT);

// Include course format js module
$PAGE->requires->js('/course/format/culcourse/format.js');
$PAGE->requires->js_call_amd('format_culcourse/sectiontoggle', 'init', ['courseid' => $course->id]);
// $PAGE->requires->js_call_amd('format_culcourse/updatebaseclass', 'init');
// $PAGE->requires->yui_module('moodle-course-dragdrop', 'M.course.init_section_dragdrop',
//             array(array(
//                 'courseid' => $course->id,
//                 'ajaxurl' => $config->sectionurl,
//                 'config' => $config,
//             )), null, true);


$PAGE->requires->yui_module('moodle-format_culcourse-dragdrop', 'M.format_culcourse.init_dragdrop',
            [[
                'courseid' => $course->id,
                'ajaxurl' => $ajaxurl,
                'config' => 0,
            ]], null, true);