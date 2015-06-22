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
 * Strings for component 'block_culschool_html', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   block_culschool_html
 * @copyright  1999 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB;

$string['allowadditionalcssclasses'] = 'Allow additional CSS classes';
$string['configallowadditionalcssclasses'] = 'Adds a configuration option to HTML block instances allowing additional CSS classes to be set.';
$string['configclasses'] = 'Additional CSS classes';
$string['configclasses_help'] = 'The purpose of this configuration is to aid with theming by helping distinguish HTML blocks from each other. Any CSS classes entered here (space delimited) will be appended to the block\'s default classes.';
$string['configcontent'] = 'Content';

// @TODO remove  and array ('visible' => 1)
// $categories = $DB->get_records('course_categories', array ('visible' => 1), 'id, name');
$categories = $DB->get_records('course_categories', null, 'id, name');

foreach ($categories as $category) {

    $catid = $category->id;
    $catname = $category->name;

    $string['student'.$catid] = $catname . ' Student Content';
    $string['studentdesc'.$catid] = $catname . ' Student Content';
    $string['staff'.$catid] = $catname . ' Staff Content';
    $string['staffdesc'.$catid] = $catname . ' Staff Content';

    $string['studentblockname'] = 'Student Information';
    $string['staffblockname'] = 'Staff Information';

}

$string['culschool_html:addinstance'] = 'Add a new CUL School HTML block';
$string['culschool_html:myaddinstance'] = 'Add a new CUL School HTML block to My home';
$string['culschool_html:edit'] = 'Edit CUL School HTML block';
$string['leaveblanktohide'] = 'leave blank to hide the title';
$string['newhtmlblock'] = 'School Information';
$string['pluginname'] = 'CUL School HTML';
