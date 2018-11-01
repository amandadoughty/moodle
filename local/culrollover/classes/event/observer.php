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
 * Event observers used in Rollover.
 *
 * @package    local_culrollover
 * @copyright 2018 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_culrollover.
 */
class local_culrollover_observer {

    /**
     * Triggered via assessable_uploaded event.
     *
     * @param \assignsubmission_file\event\assessable_uploaded $event
     */
    public static function assessable_uploaded(\assignsubmission_file\event\assessable_uploaded $event) {
        global $CFG;

        $fs = get_file_storage();
        $contenthashes = [];

        foreach ($event->other['pathnamehashes'] as $pathnamehash) {
            $file = $fs->get_file_by_hash($pathnamehash);
            $contenthashes[] = $file->get_contenthash();
        }

        $info = get_string('uploaded', 'local_culobserver', count($event->other['pathnamehashes']));
        $info .= "\n\n";
        $info .= join("; \n\n", $contenthashes);
        $info = nl2br($info);

        $params = array(
            'context' => $event->get_context(),
            'objectid' => $event->objectid,
            'other' => array('contenthashes' => $info)
        );

        $event = local_culobserver\event\assessable_uploaded::create($params);
        $event->trigger();
    }


    /**
     * Triggered via course changing events.
     *
     * @param event_interface $event
     */
    public static function course_edited(event_interface $event) {
        global $DB;

        // If there is no courseid then the value of $event->data['courseid'] is 0.
        $courseid = $event->data['courseid'];
        $userid = $event->data['userid'];
        $configname = 'rolloverlocked';
        $table = 'cul_rollover_config';
        $record = $DB->get_record($table, ['courseid' => $courseid, 'name' => $configname]);

        $data = new stdClass();
        $data->courseid = $courseid;        
        $data->name = 'rolloverlocked';
        $data->value = 1;
        $data->timemodified = time();
        $data->userid = $userid;
        
        if (!$record) {
            $DB->insert_record($table, $data);        
        } else if ($record->value != 1) {
            $DB->update_record($table, $data);
        }

        $params = [
            'context' => $event->get_context(),
            'objectid' => $event->objectid,
            'userid' => $userid,
            'courseid' => $courseid
        ];

        $event = local_culrollover\event\course_locked::create($params);
        $event->trigger();
    }



}