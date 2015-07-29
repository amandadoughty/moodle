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
 * CUL School HTML block caps
 *
 * @package    block_culschool_html
 * @copyright  1999 onwards Naomi Wilce (Naomi.Wilce.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $DB;

if ($ADMIN->fulltree) {

    $categories = $DB->get_records('course_categories', null, 'path', 'id, name, path');

    // ...@TODO no file options in the admin class admin_setting_confightmleditor, so we may need
    // to extend it. First find out the logic for not including file picker.
    /*
    Leopoldo, the reason it doesn't work is that admin_setting_confightmleditor doesn't initialise
    Atto with file picker options ($fpoptions) - so it has nowhere to put the files. That's why Atto
    editors created using admin_setting_confightmleditor don't allow you to browse repositories to
    pick an image (only specify a direct URL) and don't have a "manage files" button. Any instance of
    Atto which has been initialised with file picker options will be supported by the image drag &
    drop plugin; if you're developing something, you might want to create a subclass of
    admin_setting_confightmleditor which initialises the file picker options (and performs any
    necessary handling on the submitted form).
    */

    $categoryid = optional_param('id', 0, PARAM_INT);

    if ($categoryid) {

            $settings->add(new admin_setting_confightmleditor('block_culschool_html/student'.$categoryid,
                new lang_string('student'.$categoryid, 'block_culschool_html'),
                new lang_string('studentdesc'.$categoryid, 'block_culschool_html'), '', PARAM_RAW));

            $settings->add(new admin_setting_confightmleditor('block_culschool_html/staff'.$categoryid,
                new lang_string('staff'.$categoryid, 'block_culschool_html'),
                new lang_string('staffdesc'.$categoryid, 'block_culschool_html'), '', PARAM_RAW));

    } else {

        foreach ($categories as $category) {

            $space = '';
            $catid = $category->id;
            $catname = 'Edit ' . $category->name . ' HTML';
            $catpath = $category->path;
            $catpathlength = substr_count($catpath, '/');
            $url = new moodle_url('/admin/settings.php?section=blocksettingculschool_html');
            $url->param('id', $catid);

            if ($catpathlength == 1) {
                $catname = '<h4>Edit ' . $category->name . ' HTML</h4>';
            }

            $i = 1;
            while ($i < $catpathlength) {
                $space .= '&nbsp;&nbsp;&nbsp;';
                $i++;
            }

            $link = $space . '<a href='.$url.'>'.$catname.'</a>';
            $settings->add(new admin_setting_heading('block_culschool_html_addlink'.$catid, '', $link));
        }
    }
}