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
 * A scheduled task for transferring favourite courses.
 *
 * @package   local_culcourse_favourites
 * @category  task
 * @copyright 2019 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_culcourse_favourites\task;

// To run on command line:
// php admin/tool/task/cli/schedule_task.php --execute=\\local_culcourse_favourites\\task\\trffavourites
// .

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/format/lib.php');

/**
 * Simple task to add the defaukt blocks.
 *
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class trffavourites extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('trffavourites', 'local_culcourse_favourites');
    }

    /**
     * Add default blocks.
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;

        $start = time();
        $config = get_config('local_culcourse_favourites');

        if ($config->limit) {
            // Get list of users favourites to transfer.
            mtrace("\n  Searching for favourites to transfer ...");

            $limit = $config->limit;
            $userpreferences = $DB->get_records('user_preferences', ['name' => 'culcourse_listing_course_favourites'], '', '*', 0, $limit);

            foreach ($userpreferences as $userpreference) {
                $userid = $userpreference->userid;
                $usercontext = \context_user::instance($userid);
                $courseids = unserialize($userpreference->value);
                $i = 1;

                foreach ($courseids as $courseid) {
                    try {
                        if ($coursecontext = \context_course::instance($courseid, IGNORE_MISSING)) {
                            // Is course already in Favourite API?                            
                            $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);
                            $exists = $ufservice->favourite_exists('core_course', 'courses', $courseid, $coursecontext);
                            // If not add it.
                            if (!$exists) {
                                // New favourite api.
                                $ufservice->create_favourite('core_course', 'courses', $courseid, $coursecontext, $i);
                                mtrace("\n  Added favourite course: " . $courseid);
                                $i++;                         
                            } else {
                                mtrace("\n  Already in favourite courses: " . $courseid);
                            }
                        } else{
                            mtrace("\n  Problem with course id " . $courseid);
                        } 
                    } catch (exception $e) {
                        mtrace(" ... {$e->getMessage()} \n\n");
                    }
                }

                // Rename the user preference
                $userpreference->name = 'culcourse_listing_course_favourites_trf';
                $DB->update_record('user_preferences', $userpreference, true);

                // Trigger event for each user preference transferred.
                $params = array(
                    'context' => $usercontext,
                    'objectid' => $userid,
                    'relateduserid' => 0
                );

                $event = \local_culcourse_favourites\event\trffavourites_success::create($params);
                $event->trigger();
            }

        } else {
            mtrace("\n  No courses to add to favourites ...");
        }

        $end = time();
        mtrace(($end - $start) / 60 . ' mins');
    }
}
