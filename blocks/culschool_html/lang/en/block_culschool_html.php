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
 * @copyright  1999 onwards Naomi Wilce (Naomi.Wilce.1@city.ac.uk)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB;

$string['configcontent'] = 'Content';

try {
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
} catch (dml_exception $e) {
    // Required for cold installation of moodle code.
}

$string['categoryid'] = 'Category id';
$string['culschool_html:addinstance'] = 'Add a new CUL School HTML block';
$string['culschool_html:myaddinstance'] = 'Add a new CUL School HTML block to My home';
$string['culschool_html:edit'] = 'Edit CUL School HTML block';
$string['leaveblanktohide'] = 'leave blank to hide the title';
$string['newhtmlblock'] = 'School Information';
$string['pluginname'] = 'CUL School HTML';
$string['settings'] = 'Settings';
$string['changes'] = 'Changes to text';
$string['donot_edit'] = 'This is a centrally maintained block and can not be edited by staff members.
    If this information does need to be changed please ask your course officer to put in a request to ServiceNow';