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
 * Settings for format_singleactivity
 *
 * @package    format_singleactivity
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/* Base course format.
*/
$name = 'format_cul/defaultbaseclass';
$title = get_string('defaultbaseclass', 'format_cul');
$description = get_string('defaultbaseclass_desc', 'format_cul');
$default = 1;
$choices = [
    1 => new lang_string('pluginname', 'format_topics'),
    2 => new lang_string('pluginname', 'format_weeks')
        
        // 1 => new lang_string('setlayoutstructuretopic', 'format_cul'), // Topic.
        // 2 => new lang_string('setlayoutstructureweek', 'format_cul'), // Week.
        // 3 => new lang_string('setlayoutstructurelatweekfirst', 'format_cul'), // Latest Week First.
        // 4 => new lang_string('setlayoutstructurecurrenttopicfirst', 'format_cul'), // Current Topic First.
        // 5 => new lang_string('setlayoutstructureday', 'format_cul')                // Day.
];
$settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

// Show the section summary when collapsed.
// 1 => No.
// 2 => Yes.
$name = 'format_cul/defaultshowsectionsummary';
$title = get_string('defaultshowsectionsummary', 'format_cul');
$description = get_string('defaultshowsectionsummary_desc', 'format_cul');
$default = 1;
$choices = [
    1 => new lang_string('no'),
    2 => new lang_string('yes')
];
$settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

/* Default blocks.
*/
$name = 'format_cul/defaultblocks_cul';
$title = get_string('defaultblocks', 'format_cul');
$description = get_string('defaultblocks_desc', 'format_cul');
$default = 'settings,culactivity_stream,culupcoming_events,school_html,quickmail';
$settings->add(new admin_setting_configtextarea($name, $title, $description, $default));

/* Quicklinks.
*/
$elements = [
    'readinglists', 
    'timetable', 
    'graderreport', 
    'calendar', 
    'students',
    'lecturers',
    'courseofficers',
    'media'
];

foreach ($elements as $element) {
    $name = 'format_cul/defaultshow' . $element;
    $title = get_string('defaultshow' . $element, 'format_cul');
    $description = get_string('defaultshow' . $element . '_desc', 'format_cul');
    $default = 2;
    $choices = [
        1 => new lang_string('no'),
        2 => new lang_string('yes')
    ];
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}