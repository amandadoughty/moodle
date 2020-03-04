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
 * CUL Course Dashboard Config Settings
 *
 * @package    block_culcourse_dashboard
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>, Tim Gagen <tim.gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Show the course summary.
// 1 => No.
// 2 => Yes.
$name = 'block_culcourse_dashboard/defaultshowcoursesummary';
$title = get_string('defaultshowcoursesummary', 'block_culcourse_dashboard');
$description = get_string('defaultshowcoursesummary_desc', 'block_culcourse_dashboard');
$default = 2;
$choices = array(
    1 => new lang_string('no'),
    2 => new lang_string('yes')
);
$settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

$elements = array('readinglists', 'timetable', 'graderreport', 'calendar', 'photoboard', 'media');

foreach ($elements as $element) {
    $name = 'block_culcourse_dashboard/defaultshow' . $element;
    $title = get_string('defaultshow' . $element, 'block_culcourse_dashboard');
    $description = get_string('defaultshow' . $element . '_desc', 'block_culcourse_dashboard');
    $default = 2;
    $choices = array(
        1 => new lang_string('no'),
        2 => new lang_string('yes')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}

$settings->add(new admin_setting_configtext('block_culcourse_dashboard/connection_timeout',
    get_string('config_connection_timeout', 'block_culcourse_dashboard'),
    get_string('config_connection_timeout_desc', 'block_culcourse_dashboard'),
    get_string('config_connection_timeout_ex', 'block_culcourse_dashboard'),
    PARAM_INT, 2));

$settings->add(new admin_setting_configtext('block_culcourse_dashboard/transfer_timeout',
    get_string('config_transfer_timeout', 'block_culcourse_dashboard'),
    get_string('config_transfer_timeout_desc', 'block_culcourse_dashboard'),
    get_string('config_transfer_timeout_ex', 'block_culcourse_dashboard'),
    PARAM_INT, 2));

$settings->add(new admin_setting_configtextarea('block_culcourse_dashboard/timetable_weekoptions',
        get_string('config_timetable_weekoptions', 'block_culcourse_dashboard'),
        get_string('config_timetable_weekoptions_desc', 'block_culcourse_dashboard'),
        ''));
$settings->add(new admin_setting_configtextarea('block_culcourse_dashboard/timetable_formatoptions',
    get_string('config_timetable_formatoptions', 'block_culcourse_dashboard'),
    get_string('config_timetable_formatoptions_desc', 'block_culcourse_dashboard'),
    ''));



