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
 * Upgrade scripts for format_culcourse.
 *
 * @package   format_culcourse
 * @copyright 2018 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for format_culcourse
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_format_culcourse_upgrade($oldversion) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/course/format/culcourse/db/upgradelib.php');

    if ($oldversion < 2017020200) {

        // Remove 'numsections' option and hide or delete orphaned sections.
        format_culcourse_upgrade_remove_numsections();

        upgrade_plugin_savepoint(true, 2017020200, 'format', 'culcourse');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    return true;
}
