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
 * Admin settings for CUL Course Listing
 *
 * @package    block
 * @subpackage culcourse_listing
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    // GENERAL SETTINGS
    $settings->add(new admin_setting_heading(
        'headergeneral',
        get_string('adminheadergeneral', 'block_culcourse_listing'),
        get_string('admindescgeneral', 'block_culcourse_listing')
    ));

    $coursefields = $DB->get_columns('course');
    $fieldoptions = array();

    foreach ($coursefields as $key => $coursefield) {
        $fieldoptions[$key] = $key;
    }

    $settings->add(new admin_setting_configselect(
        'block_culcourse_listing/displayname',
        new lang_string('displayname', 'block_culcourse_listing'),
        new lang_string('displayname_help', 'block_culcourse_listing'),
        'shortname',
        $fieldoptions)
    );

    // FILTER SETTINGS  
    $settings->add(new admin_setting_heading(
        'headerfilters',
        get_string('adminheaderfilters', 'block_culcourse_listing'),
        get_string('admindescfilters', 'block_culcourse_listing')
    )); 
    $settings->add(new admin_setting_configcheckbox(
    'block_culcourse_listing/filterbyyear',
    new lang_string('filterbyyear', 'block_culcourse_listing'),
    new lang_string('filterbyyear_help', 'block_culcourse_listing'),
    0)
    );
    $settings->add(new admin_setting_configcheckbox(
        'block_culcourse_listing/filterbyperiod',
        new lang_string('filterbyperiod', 'block_culcourse_listing'),
        new lang_string('filterbyperiod_help', 'block_culcourse_listing'),
        0)
    );

    $regex = new lang_string('regex', 'block_culcourse_listing');
    $date = new lang_string('date', 'block_culcourse_listing');
    $options = array("$regex" => $regex, "$date" => $date);

    $settings->add(new admin_setting_configselect(
        'block_culcourse_listing/filtertype',
        new lang_string('filtertype', 'block_culcourse_listing'),
        new lang_string('filtertype_help', 'block_culcourse_listing'),
        'regex',
        $options)
    );

    // REGEX SETTINGS
    $settings->add(new admin_setting_heading(
        'headerregex',
        get_string('adminheaderregex', 'block_culcourse_listing'),
        get_string('admindescregex', 'block_culcourse_listing')
    ));
    $settings->add(new admin_setting_configselect(
        'block_culcourse_listing/filterfield',
        new lang_string('filterfield', 'block_culcourse_listing'),
        new lang_string('filterfield_help', 'block_culcourse_listing'),
        'shortname',
        $fieldoptions)
    );
    $settings->add(new admin_setting_configtext(
        'block_culcourse_listing/filterglue',
        new lang_string('filterglue', 'block_culcourse_listing'),
        new lang_string('filterglue_help', 'block_culcourse_listing'),
        '_')
    );
    $settings->add(new admin_setting_configtext(
        'block_culcourse_listing/filterperiodregex',
        new lang_string('filterperiodregex', 'block_culcourse_listing'),
        new lang_string('filterperiodregex_help', 'block_culcourse_listing'),
        '/PRD\d{1}/')
    );
    $settings->add(new admin_setting_configtext(
        'block_culcourse_listing/filteryearregex',
        new lang_string('filteryearregex', 'block_culcourse_listing'),
        new lang_string('filteryearregex_help', 'block_culcourse_listing'),
        '/^\d{4}\-\d{2}$/')
    );

    // DATE SETTINGS
    $settings->add(new admin_setting_heading(
        'headerdate',
        get_string('adminheaderdate', 'block_culcourse_listing'),
        get_string('admindescdate', 'block_culcourse_listing')
    ));

    $url = new moodle_url('/blocks/culcourse_listing/settings_filterdates.php');
    $link = '<a href="' . $url . '">' . get_string('editperiod', 'block_culcourse_listing') . '</a>';

    $settings->add(new admin_setting_heading('block_culcourse_listing_addfilter', '', $link));

    $filters = $DB->get_records('block_culcourse_listing_prds');

    foreach ($filters as $filter) {

        $filterid = $filter->id;
        $filterdetail = $filter->name;

        $startdate = userdate($filter->startdate, '%d/%m/%Y');
        $enddate = userdate($filter->enddate, '%d/%m/%Y');

        $filterdetail .= " ($startdate - $enddate) ";
        $url = new moodle_url('/blocks/culcourse_listing/period_post.php', array('sesskey' => sesskey()));
        $url->param('id', $filterid);

        $filterdetail .= '<a href="' . $url . '">delete</a>';
        $settings->add(new admin_setting_heading('block_culcourse_listing_filter' . $filterid, '', $filterdetail));
    }
}