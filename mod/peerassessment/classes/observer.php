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
    public static function group_members_updated(\core\event\base $event) {
            global $CFG, $DB;

            require($CFG->dirroot . '/mod/peerassessment/lib.php');

            $groupid = $event->objectid;
            $members = groups_get_members($groupid);

            $sql = "SELECT DISTINCT p.*
                FROM {peerassessment} p
                INNER JOIN {peerassessment_submission} ps
                ON p.id = ps.assignment
                INNER JOIN {groups} g
                ON ps.groupid = g.id
                WHERE g.id = :groupid";

            $params = ['groupid' => $groupid];

            try {
                $peerassessments = $DB->get_records_sql($sql, $params);
            } catch (exception $e) {

            }

            if ($peerassessments) {
                foreach ($peerassessments as $id => $peerassessment) {
                    foreach ($members as $member) {
                        try {
                            peerassessment_update_grades($peerassessment, $member->id);
                        } catch (exception $e) {

                        }
                    }
                }
            }




    
  


            // return $result;
    }
}
