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
 * This script allows the number of sections in a course to be increased
 * or decreased, redirecting to the course page.
 *
 * @package    course/format
 * @subpackage culcollapsed
 * @copyright 2017 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

$courseid = required_param('courseid', PARAM_INT);
$destsection = required_param('destsection', PARAM_INT); // The section that we want our new one to go before.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseformatoptions = course_get_format($course)->get_format_options();
$PAGE->set_url('/course/format/culcollapsed/changenumsections.php', array('courseid' => $courseid));

// Authorisation checks.
require_login($course);
require_capability('moodle/course:update', context_course::instance($course->id));
require_sesskey();

if (isset($courseformatoptions['numsections'])) {

    // Are there any orphaned sections? if there are then we move them down one position
    // to leave a gap for our new section.
    $sections = $DB->get_records('course_sections', ['course' => $courseid]);
    // Start from the end otherwise we could have unique index errors.
    $sections = array_reverse($sections);

    foreach ($sections as $section) {
        if ($section->section > $courseformatoptions['numsections']) {
            $section->section++;
            $DB->update_record('course_sections', $section);
        }
    }

    // Add an additional section.
    $courseformatoptions['numsections']++;
    update_course((object)array('id' => $course->id,
            'numsections' => $courseformatoptions['numsections']));
    course_create_sections_if_missing($course, range(0, $courseformatoptions['numsections']));

    // Move added section to position required.
    $sectionnum = $courseformatoptions['numsections'];            

    if (!move_section_to($course, $sectionnum, $destsection)) {
        echo $OUTPUT->notification('An error occurred while moving a section');
    }
}

$url = course_get_url($course);
$url->set_anchor('section-' . $destsection);

// Redirect to where we were..
redirect($url);
