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

/* Structure configuration.
    Here so you can see what numbers in the array represent what structure for setting the default value below.
    1 => Topic.
    2 => Week.
    3 => Latest Week First.
    4 => Current Topic First.
    5 => Day.
    Default structure to use - used when a new Collapsed Topics course is created or an old one is accessed for the first time
    after installing this functionality introduced in CONTRIB-3378. */
$name = 'format_cul/defaultlayoutstructure';
$title = get_string('defaultlayoutstructure', 'format_culcollapsed');
$description = get_string('defaultlayoutstructure_desc', 'format_culcollapsed');
$default = 1;
$choices = [
    1 => new lang_string('pluginname', 'format_topics'),
        2 => new lang_string('pluginname', 'format_weeks')
        
        // 1 => new lang_string('setlayoutstructuretopic', 'format_culcollapsed'), // Topic.
        // 2 => new lang_string('setlayoutstructureweek', 'format_culcollapsed'), // Week.
        // 3 => new lang_string('setlayoutstructurelatweekfirst', 'format_culcollapsed'), // Latest Week First.
        // 4 => new lang_string('setlayoutstructurecurrenttopicfirst', 'format_culcollapsed'), // Current Topic First.
        // 5 => new lang_string('setlayoutstructureday', 'format_culcollapsed')                // Day.
];
$settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));    

