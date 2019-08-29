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
 * CUL Peerassessment plugin event handlers.
 *
 * @package    mod_peerassessment
 * @copyright  2019 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Event observer.
 *
 * Responds to group events emitted by the Moodle event manager.
 */
class mod_peerassessment_observer {

    /**
     * Event handler.
     *
     * Called by observers to handle notification sending.
     *
     * @param \core\event\base $event The event object.
     *
     * @return boolean true
     *
     */
    protected static function group_members_updated(\core\event\base $event) {
            global $CFG, $DB;

            // $course = $DB->get_record('course', array('id' => $event->courseid));
            // $module = $event->other['modulename'];
            // $modulename = $event->other['name'];
            // $messagetext = get_string($event->action, 'mod_peerassessment', $modulename);
            // $coursename = $course->idnumber ? $course->idnumber : $course->fullname;
            // $messagetext .= get_string('incourse', 'mod_peerassessment', $coursename);

            // $message = new stdClass();
            // $message->userfromid = $event->userid;
            // $message->courseid = $event->courseid;
            // $message->cmid = $event->objectid;
            // $message->smallmessage     = $messagetext;
            // $message->component = 'mod_peerassessment';
            // $message->modulename = $module;
            // $message->timecreated = time();
            // $message->contexturl = "$CFG->wwwroot/mod/$module/view.php?id=$event->objectid";
            // $message->contexturlname  = $modulename;

            // // Add base message to queue - message_culactivity_queue.
            // $result = $DB->insert_record('message_culactivity_stream_q', $message);

            // return $result;
    }
}
