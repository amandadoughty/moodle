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
 * CUL Timetable API settings.
 *
 * @package    local_cultimetable_api
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
global $OUTPUT;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_cultimetable_api', get_string('pluginname', 'local_cultimetable_api'));
    $ADMIN->add('localplugins', $settings);

    // Heading
    $settings->add(new admin_setting_heading(
        'local_cultimetable_api/connection_details_heading', 
        get_string('timetable_connection_details', 'local_cultimetable_api'), 
        get_string('timetable_connection_details_description', 'local_cultimetable_api')
        ));

    // Connection timeout in ms.
    $settings->add(new admin_setting_configtext(
        'local_cultimetable_api/connect_timeout', 
        get_string('connect_timeout', 'local_cultimetable_api'), 
        get_string('connect_timeout_description', 'local_cultimetable_api'), 
        60, 
        PARAM_INT
        ));

    // Timeout in ms.
    $settings->add(new admin_setting_configtext(
        'local_cultimetable_api/timeout', 
        get_string('timeout', 'local_cultimetable_api'), 
        get_string('timeout_description', 'local_cultimetable_api'), 
        60, 
        PARAM_INT
        ));

    // Login URL
    $settings->add(new admin_setting_configtext(
        'local_cultimetable_api/login_url', 
        get_string('login_url', 'local_cultimetable_api'), 
        get_string('login_url_description', 'local_cultimetable_api'), 
        get_string('default_login_url', 'local_cultimetable_api'), 
        PARAM_TEXT
        ));

    // Default Page URL
    $settings->add(new admin_setting_configtext(
        'local_cultimetable_api/default_url', 
        get_string('default_url', 'local_cultimetable_api'), 
        get_string('default_url_description', 'local_cultimetable_api'), 
        get_string('default_default_url', 'local_cultimetable_api'), 
        PARAM_TEXT
        ));

    // Timetable URL
    $settings->add(new admin_setting_configtext(
        'local_cultimetable_api/timetable_url', 
        get_string('timetable_url', 'local_cultimetable_api'), 
        get_string('timetable_url_description', 'local_cultimetable_api'), 
        get_string('default_timetable_url', 'local_cultimetable_api'), 
        PARAM_TEXT
        ));

    $settings->add(new admin_setting_configtextarea(
        'local_cultimetable_api/timetable_weekoptions',
        get_string('timetable_weekoptions', 'local_cultimetable_api'),
        get_string('timetable_weekoptions_desc', 'local_cultimetable_api'),
        ''));
    
    $settings->add(new admin_setting_configtextarea(
        'local_cultimetable_api/timetable_formatoptions',
        get_string('timetable_formatoptions', 'local_cultimetable_api'),
        get_string('timetable_formatoptions_desc', 'local_cultimetable_api'),
        ''));
}


