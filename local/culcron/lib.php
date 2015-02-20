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
 * Library function for culcron.
 *
 * @package    local
 * @subpackage culcron
 * @copyright  2013 Tim Gagen <tim.gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * local_culcron_cron()
 * Called periodically by the Moodle cron job.
 * @return void
 */
function local_culcron_cron() {

    // If enabled in plugin settings, update the course visibility, if the current time is between 0100h and 0120h.
    if (get_config('local_culcron', 'coursevisibility_autoupdate')) {
        if ((date('H') == '01') && (date('i') < 20)) {
            update_course_visibility();
        }
    }
}


/**
 * update_course_visibility()
 * Update course visibility if the startdate has recently become due.
 * @return void
 */
function update_course_visibility() {
    global $DB;

    // If startdate within last $updatePeriod hours and visibility = 0 then set visibility = 1.
    $updateperiod = 48; // Hours

    // Get list of courses to update.
    mtrace("\n  Searching for courses to make visible ...");

    $now = time();
    $updateperiodago = $now - (60 * 60 * $updateperiod);
    $select = "visible = 0 AND startdate BETWEEN {$updateperiodago} AND {$now}";

    if ($courses = $DB->get_records_select('course', $select)) {
        foreach ($courses as $course) {
            if (!$DB->set_field('course', 'visible', 1 , array('id' => $course->id))) {
                mtrace("    {$course->id}: {$course->shortname} could not be updated for some reason.");
            } else {
                mtrace("    {$course->id}: {$course->shortname} is now visible");
            }
        }
    } else {
        mtrace("  Nothing to do, except ponder the boundless wonders of the Universe, perhaps. ;-)\n");
    }

    flush();
}
