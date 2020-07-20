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
 * Settings for local_culcourse_dashboard.
 *
 * @package   local_culcourse_dashboard
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_culcourse_dashboard', 'CUL Course Dashboard');
    $ADMIN->add('localplugins', $settings);

    /* Quicklinks.
    */
    $elements = [
        'readinglists', 
        'libguides',
        'timetable',
        'graderreport',
        'calendar',
        'students',
        'lecturers',
        'courseofficers',
        'media'
    ];

    foreach ($elements as $element) {
        $name = 'local_culcourse_dashboard/defaultshow' . $element;
        $title = get_string('defaultshow' . $element, 'local_culcourse_dashboard');
        $description = get_string('defaultshow' . $element . '_desc', 'local_culcourse_dashboard');
        $default = 2;
        $choices = [
            1 => new lang_string('no'),
            2 => new lang_string('yes')
        ];
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    }

    // Connection timeout in s.
    $name = 'local_culcourse_dashboard/connect_timeout';
    $title = get_string('connect_timeout', 'local_culcourse_dashboard');
    $description = get_string('connect_timeout_desc', 'local_culcourse_dashboard');
    $default = 4;
    $type = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));

    // Transfer timeout in s.
    $name = 'local_culcourse_dashboard/transfer_timeout';
    $title = get_string('transfer_timeout', 'local_culcourse_dashboard');
    $description = get_string('transfer_timeout_desc', 'local_culcourse_dashboard');
    $default = 8;
    $type = PARAM_INT;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));

    // Reading List API url.
    $name = 'local_culcourse_dashboard/aspireAPI';
    $title = get_string('aspireAPI', 'local_culcourse_dashboard');
    $description = get_string('aspireAPI_desc', 'local_culcourse_dashboard');
    $default = 'http://readinglists.city.ac.uk';
    $type = PARAM_RAW;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));

    // Libguides url.
    $name = 'local_culcourse_dashboard/libAppsAPI';
    $title = get_string('libAppsAPI', 'local_culcourse_dashboard');
    $description = get_string('libAppsAPI_desc', 'local_culcourse_dashboard');
    $default = 'http://lgapi-eu.libapps.com/1.1/guides/';
    $type = PARAM_RAW;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));

    // Libguides default url.
    $name = 'local_culcourse_dashboard/libAppsDefaultURL';
    $title = get_string('libAppsDefaultURL', 'local_culcourse_dashboard');
    $description = get_string('libAppsDefaultURL_desc', 'local_culcourse_dashboard');
    $default = 'http://libguides.city.ac.uk/home';
    $type = PARAM_RAW;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));

    // Libguides site ID.
    $name = 'local_culcourse_dashboard/libAppsSiteId';
    $title = get_string('libAppsSiteId', 'local_culcourse_dashboard');
    $description = get_string('libAppsSiteId_desc', 'local_culcourse_dashboard');
    $default = '426';
    $type = PARAM_RAW;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));

    // Libguides API key.
    $name = 'local_culcourse_dashboard/libAppsKey';
    $title = get_string('libAppsKey', 'local_culcourse_dashboard');
    $description = get_string('libAppsKey_desc', 'local_culcourse_dashboard');
    $default = 'e4706d90b346c209c37b32a6a94781d7';
    $type = PARAM_RAW;
    $settings->add(new admin_setting_configtext($name, $title, $description, $default, $type));
}