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
 * @copyright  1999 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $DB;

if ($ADMIN->fulltree) {
    // TODO Remove this setting - the user can't add content so no need to let them add css
    // $settings->add(new admin_setting_configcheckbox('block_culschool_html_allowcssclasses',
    //     get_string('allowadditionalcssclasses', 'block_culschool_html'),
    //     get_string('configallowadditionalcssclasses', 'block_culschool_html'), 0));

    $categories = $DB->get_records('course_categories', null, 'path', 'id, name');

    // @TODO no file options in the admin class admin_setting_confightmleditor, so we may need
    // to extend it. First find out the logic for not including file picker.
    /**
    Leopoldo, the reason it doesn't work is that admin_setting_confightmleditor doesn't initialise
    Atto with file picker options ($fpoptions) - so it has nowhere to put the files. That's why Atto
    editors created using admin_setting_confightmleditor don't allow you to browse repositories to
    pick an image (only specify a direct URL) and don't have a "manage files" button. Any instance of
    Atto which has been initialised with file picker options will be supported by the image drag &
    drop plugin; if you're developing something, you might want to create a subclass of
    admin_setting_confightmleditor which initialises the file picker options (and performs any
    necessary handling on the submitted form).
    **/

// @TODO if statement to display only one category from parameter. If no parameter then list the categories 
// with a link to include parameter

$categoryId = optional_param('id', 0, PARAM_INT);

    if ($categoryId) {

            $settings->add(new admin_setting_confightmleditor('block_culschool_html/student'.$categoryId,
                new lang_string('student'.$categoryId, 'block_culschool_html'),
                new lang_string('studentdesc'.$categoryId, 'block_culschool_html'), '', PARAM_RAW));

            $settings->add(new admin_setting_confightmleditor('block_culschool_html/staff'.$categoryId,
                new lang_string('staff'.$categoryId, 'block_culschool_html'),
                new lang_string('staffdesc'.$categoryId, 'block_culschool_html'), '', PARAM_RAW));

    } else {

        foreach ($categories as $category) {

            $catid = $category->id;
            $catname = $category->name;

            $url = new moodle_url('/admin/settings.php?section=blocksettingculschool_html');
            $url->param('id', $catid);

            $link ='<p><a href='.$url.'>Edit '.$catname.'</a></p>'; 
            
            $settings->add(new admin_setting_heading('block_culschool_html_addlink'.$catid, '', $link));
        
        }          
    }
}