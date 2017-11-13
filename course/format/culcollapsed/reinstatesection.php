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
 * This script allows an orphaned section to be reinstated,
 * redirecting to the course page.
 *
 * @package    course/format
 * @subpackage culcollapsed
 * @copyright 2017 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../../config.php');
require_once($CFG->dirroot.'/course/lib.php');

$sectionnum = required_param('id', PARAM_INT);    // course_sections.id
$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$courseformatoptions = course_get_format($course)->get_format_options();

$PAGE->set_url('/course/format/culcollapsed/reinstatesection.php', array('id' => $sectionnum, 'courseid' => $courseid));

// Authorisation checks.
require_login($course);
require_capability('moodle/course:update', context_course::instance($course->id));
require_sesskey();

if (isset($courseformatoptions['numsections'])) {
	// Increase number of sections.
	$courseformatoptions['numsections']++;
    // Move this orphan to position below last non-orphan.
    if (!move_section_to($course, $sectionnum, $courseformatoptions['numsections'], true)) {
        echo $OUTPUT->notification('An error occurred while moving a section');
    }

    update_course((object)array('id' => $course->id,
                'numsections' => $courseformatoptions['numsections']));
}

$url = course_get_url($course, $sectionnum);
// Redirect to where we were..
redirect($url);