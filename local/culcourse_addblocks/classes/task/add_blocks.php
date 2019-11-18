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
 * A scheduled task for adding blocks to courses.
 *
 * @package   local_culcourse_addblocks
 * @category  task
 * @copyright 2019 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_culcourse_addblocks\task;

// To run on command line:
// php admin/tool/task/cli/schedule_task.php --execute=\\local_culcourse_addblocks\\task\\add_blocks
// .

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/format/lib.php');

/**
 * Simple task to add the defaukt blocks.
 *
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class add_blocks extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('addblocks', 'local_culcourse_addblocks');
    }

    /**
     * Add default blocks.
     *
     * @return void
     */
    public function execute() {
        global $CFG, $DB;

        $start = time();
        $config = get_config('local_culcourse_addblocks');

        if ($config->courselist) {
            // Get list of courses to update.
            mtrace("\n  Searching for courses to add blocks to ...");

            $courses = explode(',', $config->courselist);

            foreach ($courses as $courseid) {
                try {
                    $courseid = trim((int)$courseid);                    
                    
                    if ($courseid) {
                        $course = course_get_format($courseid)->get_course();

                        if ($course) {
                            $numblocks = $this->checkBlocks($courseid);

                            if ($numblocks == 0) {
                                // Setup the blocks
                                blocks_add_default_course_blocks($course);

                                $params = array(
                                    'context' => $context = \context_course::instance($courseid),
                                    'objectid' => $courseid,
                                    'relateduserid' => 0
                                );

                                $event = \local_culcourse_addblocks\event\addblocks_success::create($params);
                                $event->trigger();

                                mtrace("\n  Added blocks to " . $course->fullname);
                            } else {
                                mtrace("\n  There are already blocks in " . $course->fullname);
                            }
                        } else{
                            mtrace("\n  Problem with course id " . $courseid);
                        }
                    }                        

                } catch (exception $e) {
                    mtrace(" ... {$e->getMessage()} \n\n");
                }
            }

        } else {
            mtrace("\n  No courses to add blocks to ...");
        }

        $end = time();
        mtrace(($end - $start) / 60 . ' mins');
    }

    /*
    *
    *
    */
    private function checkBlocks($courseid) {
        global $DB;

        $sql = "SELECT COUNT(bi.blockname) as numblocks
            FROM {course} c
            JOIN {context} cx
            ON c.id = cx.instanceid
            JOIN {block_instances} bi
            ON cx.id = bi.parentcontextid
            WHERE c.id = :courseid
            AND contextlevel = 50
            ";

        $params = ['courseid' => $courseid];
        $result = $DB->get_record_sql($sql, $params);

        return $result->numblocks;
    }
}
