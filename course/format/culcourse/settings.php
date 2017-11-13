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
 * CUL Course Format Information
 *
 * A collapsed format that solves the issue of the 'Scroll of Death' when a course has many sections. All sections
 * except zero have a toggle that displays that section. One or more sections can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer.
 *
 * @package    course/format
 * @subpackage culcourse
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('format_culcourse_defaults', get_string('defaultheadingsub', 'format_culcourse'),
        format_text(get_string('defaultheadingsubdesc', 'format_culcourse'), FORMAT_MARKDOWN)));

    /* Layout configuration.
       Here you can see what numbers in the array represent what layout for setting the default value below.
       1 => Toggle word, toggle section x and section number - default.
       2 => Toggle word and section number.
       3 => Toggle word and toggle section x.
       4 => Toggle word.
       5 => Toggle section x and section number.
       6 => Section number.
       7 => No additions.
       8 => Toggle section x.
       Default layout to use - used when a new Collapsed Topics course is created or an old one is accessed for the first time
       after installing this functionality introduced in CONTRIB-3378. */
    $name = 'format_culcourse/defaultlayoutelement';
    $title = get_string('defaultlayoutelement', 'format_culcourse');
    $description = get_string('defaultlayoutelement_descpositive', 'format_culcourse');
    $default = 1;
    $choices = array( // In insertion order and not numeric for sorting purposes.
        1 => new lang_string('setlayout_all', 'format_culcourse'),                             // Toggle word, toggle section x and section number - default.
        3 => new lang_string('setlayout_toggle_word_section_x', 'format_culcourse'),           // Toggle word and toggle section x.
        2 => new lang_string('setlayout_toggle_word_section_number', 'format_culcourse'),      // Toggle word and section number.
        5 => new lang_string('setlayout_toggle_section_x_section_number', 'format_culcourse'), // Toggle section x and section number.
        4 => new lang_string('setlayout_toggle_word', 'format_culcourse'),                     // Toggle word.
        8 => new lang_string('setlayout_toggle_section_x', 'format_culcourse'),                // Toggle section x.
        6 => new lang_string('setlayout_section_number', 'format_culcourse'),                  // Section number.
        7 => new lang_string('setlayout_no_additions', 'format_culcourse')                     // No additions.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Structure configuration.
       Here so you can see what numbers in the array represent what structure for setting the default value below.
       1 => Topic.
       2 => Week.
       3 => Latest Week First.
       4 => Current Topic First.
       5 => Day.
       Default structure to use - used when a new Collapsed Topics course is created or an old one is accessed for the first time
       after installing this functionality introduced in CONTRIB-3378. */
    $name = 'format_culcourse/defaultlayoutstructure';
    $title = get_string('defaultlayoutstructure', 'format_culcourse');
    $description = get_string('defaultlayoutstructure_desc', 'format_culcourse');
    $default = 1;
    $choices = array(
        1 => new lang_string('setlayoutstructuretopic', 'format_culcourse'),             // Topic.
        2 => new lang_string('setlayoutstructureweek', 'format_culcourse'),              // Week.
        3 => new lang_string('setlayoutstructurelatweekfirst', 'format_culcourse'),      // Latest Week First.
        4 => new lang_string('setlayoutstructurecurrenttopicfirst', 'format_culcourse'), // Current Topic First.
        5 => new lang_string('setlayoutstructureday', 'format_culcourse')                // Day.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    /* Default blocks
    */
    $name = 'format_culcourse/defaultblocks_culcourse';
    $title = get_string('defaultblocks', 'format_culcourse');
    $description = get_string('defaultblocks_desc', 'format_culcourse');
    // $default = array('news_items' => 'news_items');
    // $blocks = $DB->get_records_select_menu('block', '', null, '', 'name, name as name2');
    $default = 'settings,culactivity_stream,culupcoming_events,school_html,quickmail';

    $settings->add(new admin_setting_configtextarea($name, $title, $description, $default));


    // $defaultblocks = get_config('format_culcourse', 'defaultblocks_culcourse');
    // $defaultblocks = explode(',', $defaultblocks);
    // $numblocks = max(count($defaultblocks) * 1.5, 5);
    // $settings->add(new admin_setting_configmultiselect($name, $title, $description, $default, $blocks));



    // Show the section summary when collapsed.
    // 1 => No.
    // 2 => Yes.
    $name = 'format_culcourse/defaultshowsectionsummary';
    $title = get_string('defaultshowsectionsummary', 'format_culcourse');
    $description = get_string('defaultshowsectionsummary_desc', 'format_culcourse');
    $default = 2;
    $choices = array(
        1 => new lang_string('no'),
        2 => new lang_string('yes')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $settings->add(new admin_setting_heading('format_culcourse_configuration', get_string('configurationheadingsub', 'format_culcourse'),
        format_text(get_string('configurationheadingsubdesc', 'format_culcourse'), FORMAT_MARKDOWN)));

    /* Toggle persistence - 1 = on, 0 = off.  You may wish to disable for an AJAX performance increase.
       Note: If turning persistence off remove any rows containing 'culcourse_toggle_x' in the 'name' field
             of the 'user_preferences' table in the database.  Where the 'x' in 'culcourse_toggle_x' will be
             a course id. */
    $name = 'format_culcourse/defaulttogglepersistence';
    $title = get_string('defaulttogglepersistence', 'format_culcourse');
    $description = get_string('defaulttogglepersistence_desc', 'format_culcourse');
    $default = 1;
    $choices = array(
        0 => new lang_string('off', 'format_culcourse'), // Off.
        1 => new lang_string('on', 'format_culcourse')   // On.
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    // Toggle preference for the first time a user accesses a course.
    // 0 => All closed.
    // 1 => All open.
    $name = 'format_culcourse/defaultuserpreference';
    $title = get_string('defaultuserpreference', 'format_culcourse');
    $description = get_string('defaultuserpreference_desc', 'format_culcourse');
    $default = 0;
    $choices = array(
        0 => new lang_string('culcourseclosed', 'format_culcourse'),
        1 => new lang_string('culcourseopened', 'format_culcourse')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));


    $settings->add(new admin_setting_heading('format_culcourse_dashboard', get_string('dashboardheadingsub', 'format_culcourse'),
        format_text(get_string('dashboardheadingsubdesc', 'format_culcourse'), FORMAT_MARKDOWN)));


    // Show the course summary.
    // 1 => No.
    // 2 => Yes.
    $name = 'format_culcourse/defaultshowcoursesummary';
    $title = get_string('defaultshowcoursesummary', 'format_culcourse');
    $description = get_string('defaultshowcoursesummary_desc', 'format_culcourse');
    $default = 2;
    $choices = array(
        1 => new lang_string('no'),
        2 => new lang_string('yes')
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));

    $elements = array(
        'readinglists', 
        'timetable', 
        'graderreport', 
        'calendar', 
        'students',
        'lecturers',
        'courseofficers',
        'media'
        );

    foreach ($elements as $element) {
        $name = 'format_culcourse/defaultshow' . $element;
        $title = get_string('defaultshow' . $element, 'format_culcourse');
        $description = get_string('defaultshow' . $element . '_desc', 'format_culcourse');
        $default = 2;
        $choices = array(
            1 => new lang_string('no'),
            2 => new lang_string('yes')
        );
        $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    }

    $settings->add(new admin_setting_configtext('format_culcourse/connection_timeout',
        get_string('config_connection_timeout', 'format_culcourse'),
        get_string('config_connection_timeout_desc', 'format_culcourse'),
        get_string('config_connection_timeout_ex', 'format_culcourse'),
        PARAM_INT, 2));

    $settings->add(new admin_setting_configtext('format_culcourse/transfer_timeout',
        get_string('config_transfer_timeout', 'format_culcourse'),
        get_string('config_transfer_timeout_desc', 'format_culcourse'),
        get_string('config_transfer_timeout_ex', 'format_culcourse'),
        PARAM_INT, 2));
}