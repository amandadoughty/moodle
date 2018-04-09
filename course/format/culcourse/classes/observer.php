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
 * CUL Course Information
 *
 * A topic based format that solves the issue of the 'Scroll of Death' when a course has many topics. All topics
 * except zero have a toggle that displays that topic. One or more topics can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer by a small
 * code change. Full installation instructions, code adaptions and credits are included in the 'Readme.txt' file.
 *
 * @package    course/format
 * @subpackage cul
 * @category   event
 * @version    See the value of '$plugin->version' in below.
 * @copyright  &copy; 2017-onwards G J Barnard based upon work done by Marina Glancy.
 * @author     G J Barnard - gjbarnard at gmail dot com and {@link http://moodle.org/user/profile.php?id=442195}
 * @link       http://docs.moodle.org/en/Collapsed_Topics_course_format
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers supported by this format.
 */
class format_culcourse_observer {

    /**
     * Observer for the event course_content_deleted.
     *
     * @param \core\event\course_content_deleted $event
     */
    public static function course_content_deleted(\core\event\course_content_deleted $event) {
        global $DB;

        $DB->delete_records('user_preferences', array('name' => 'format_culcourse_expanded' . $event->objectid)); // This is the $courseid.
    }

    /**
     * Triggered via \core\event\course_updated event.
     *
     * @param \core\event\course_updated $event
     */
    public static function course_updated(\core\event\course_updated $event) {
        if (class_exists('format_culcourse', false)) {
            // If class format_culcourse was never loaded, this is definitely not a course in 'format_culcourse' format.
            // Course may still be in another format but format_culcourse::format_weeks_update_end_date() will check it.
            format_culcourse::format_weeks_update_end_date($event->courseid);
        }
    }

    /**
     * Triggered via \core\event\course_section_created event.
     *
     * @param \core\event\course_section_created $event
     */
    public static function course_section_created(\core\event\course_section_created $event) {
        if (class_exists('format_culcourse', false)) {
            // If class format_culcourse was never loaded, this is definitely not a course in 'format_culcourse' format.
            // Course may still be in another format but format_culcourse::format_weeks_update_end_date() will check it.
            format_culcourse::format_weeks_update_end_date($event->courseid);
        }
    }

    /**
     * Triggered via \core\event\course_section_deleted event.
     *
     * @param \core\event\course_section_deleted $event
     */
    public static function course_section_deleted(\core\event\course_section_deleted $event) {
        if (class_exists('format_culcourse', false)) {
            // If class format_culcourse was never loaded, this is definitely not a course in 'format_culcourse' format.
            // Course may still be in another format but format_culcourse::format_weeks_update_end_date() will check it.
            format_culcourse::format_weeks_update_end_date($event->courseid);
        }
    }    
}
