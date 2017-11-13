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

require_once($CFG->dirroot . '/course/format/culcourse/togglelib.php');

$run = false;
if ($run) {
    // TEST CODE.
    for ($i = 0; $i < 64; $i++) {
        user_preference_allow_ajax_update('culcourse_toggle_a'.$i.'_' . $course->id, PARAM_culcourse);
        user_preference_allow_ajax_update('culcourse_toggle_b'.$i.'_' . $course->id, PARAM_culcourse);
        user_preference_allow_ajax_update('culcourse_toggle_c'.$i.'_' . $course->id, PARAM_culcourse);
    }
    user_preference_allow_ajax_update('culcourse_toggle_bf_' . $course->id, PARAM_culcourse);
    user_preference_allow_ajax_update('culcourse_toggle_bf2_' . $course->id, PARAM_culcourse);
    user_preference_allow_ajax_update('culcourse_toggle_bf3_' . $course->id, PARAM_culcourse);
    user_preference_allow_ajax_update('culcourse_toggle_af_' . $course->id, PARAM_culcourse);
    user_preference_allow_ajax_update('culcourse_toggle_af2_' . $course->id, PARAM_culcourse);
    user_preference_allow_ajax_update('culcourse_toggle_af3_' . $course->id, PARAM_culcourse);
    // Test clean_param to see if it accepts '<' and '>' for PARAM_TEXT as stated in moodlelib.php.
    echo '<h3>PARAM_TEXT < : '.clean_param('<',PARAM_TEXT).'</h3>';
    echo '<h3>PARAM_TEXT > : '.clean_param('>',PARAM_TEXT).'</h3>';
    echo '<h3>PARAM_RAW  < : '.clean_param('<',PARAM_RAW).'</h3>';
    echo '<h3>PARAM_RAW  > : '.clean_param('>',PARAM_RAW).'</h3>';
}