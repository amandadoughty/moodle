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
 * @package local
 * @subpackage culcron
 * @copyright 2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>, Tim Gagen <tim.gagen.1@city.ac.uk>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    require_once($CFG->dirroot . '/local/culcron/lib.php');

    $settings = new admin_settingpage('local_culcron', get_string('pluginname', 'local_culcron'));
    $ADMIN->add('localplugins', $settings);

    /*----------------------------
     * Course visibility settings
     *---------------------------*/
    $settings->add(new admin_setting_heading('local_culcron/coursevisibility_heading',
                                             get_string('coursevisibility', 'local_culcron'),
                                             ''));

    // Add a checkbox to enable/disable course visibility auto-update (cron).
    $settings->add(new admin_setting_configcheckbox('local_culcron/coursevisibility_autoupdate',
                                                    get_string('autoupdate', 'local_culcron'),
                                                    get_string('coursevisibility_autoupdate_desc', 'local_culcron'),
                                                    0));

}
