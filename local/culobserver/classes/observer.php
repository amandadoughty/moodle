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
 * Event observers used in forum.
 *
 * @package    local_culobserver
 * @copyright 2014 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_forum.
 */
class local_culobserver_observer {

    /**
     * Triggered via assessable_uploaded event.
     *
     * @param \assignsubmission_file\event\assessable_uploaded $event
     */
    public static function assessable_uploaded(\assignsubmission_file\event\assessable_uploaded $event) {
        global $CFG;

        $info = get_string('uploaded', 'local_culobserver', count($event->other['pathnamehashes']));
        $info .= "\n\n";
        $info .= join("; \n\n", $event->other['pathnamehashes']);
        $info = nl2br($info);

        $params = array(
            'context' => $event->get_context(),
            'objectid' => $event->objectid,
            'other' => array('pathnamehashes' => $info)
        );

        $event = local_culobserver\event\assessable_uploaded::create($params);
        $event->trigger();
    }
}