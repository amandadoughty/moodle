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
 * CUL Course listing upgrade script.
 *
 * @package    block_culcourse_listing
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_block_culcourse_listing_upgrade($oldversion, $block) {
    global $CFG, $DB;

    $dbman = $DB->get_manager(); // Get database manager

    if($oldversion < 2017020100) {
		// Create new table to hold terms and ears defined in settings by date range.
        $table = new xmldb_table('block_culcourse_listing_prds');

        $table->add_field(
            'id', 
            XMLDB_TYPE_INTEGER, 
            '9', 
            XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, 
            XMLDB_SEQUENCE, 
            null
            );

        $table->add_field(
            'name', 
            XMLDB_TYPE_CHAR, 
            '255', 
            XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, 
            null, 
            null, 
            'id'
            );

        $table->add_field(
            'type', 
            XMLDB_TYPE_INTEGER, 
            '9', 
            XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, 
            null, 
            0, 
            'name'
            );

        $table->add_field(
            'startdate', 
            XMLDB_TYPE_INTEGER, 
            '9', 
            XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, 
            null, 
            null, 
            'type'
            );

        $table->add_field(
            'enddate', 
            XMLDB_TYPE_INTEGER, 
            '9', 
            XMLDB_UNSIGNED, 
            XMLDB_NOTNULL, 
            null, 
            null, 
            'startdate'
            );

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if(!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_block_savepoint(true, 2017020100, 'culcourse_listing');
    }
}