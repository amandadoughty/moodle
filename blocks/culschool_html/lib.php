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
 * CUL School HTML block
 *
 * @package    block_culschool_html
 * @copyright  1999 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing HTML block instances.
 *
 * @param stdClass $course course object
 * @param stdClass $birecord_or_cm block instance record
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool
 * @todo MDL-36050 improve capability check on stick blocks, so we can check user capability before sending images.
 */
function block_culschool_html_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    // If block is in course context, then check if user has capability to access course.
    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        // Get parent context and see if user have proper permission.
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            // Check if category is visible and user can view this category.
            $category = $DB->get_record('course_categories', array('id' => $parentcontext->instanceid), '*', MUST_EXIST);
            if (!$category->visible) {
                require_capability('moodle/category:viewhiddencategories', $parentcontext);
            }
        }
        // At this point there is no way to check SYSTEM or USER context, so ignoring it.
    }

    if ($filearea !== 'content') {
        send_file_not_found();
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = $args ? '/'.implode('/', $args).'/' : '/';

    if (!$file = $fs->get_file($context->id, 'block_culschool_html', 'content', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    if ($parentcontext = context::instance_by_id($birecord_or_cm->parentcontextid, IGNORE_MISSING)) {
        if ($parentcontext->contextlevel == CONTEXT_USER) {
            // force download on all personal pages including /my/
            //because we do not have reliable way to find out from where this is used
            $forcedownload = true;
        }
    } else {
        // weird, there should be parent context, better force dowload then
        $forcedownload = true;
    }

    session_get_instance()->write_close();
    send_stored_file($file, 60*60, 0, $forcedownload, $options);
}

/**
 * Perform global search replace such as when migrating site to new URL.
 * @param  $search
 * @param  $replace
 * @return void
 */
function block_culschool_html_global_db_replace($search, $replace) {
    global $DB;

    $instances = $DB->get_recordset('block_instances', array('blockname' => 'html'));
    foreach ($instances as $instance) {
        // TODO: intentionally hardcoded until MDL-26800 is fixed
        $config = unserialize(base64_decode($instance->configdata));
        if (isset($config->text) and is_string($config->text)) {
            $config->text = str_replace($search, $replace, $config->text);
            $DB->set_field('block_instances', 'configdata', base64_encode(serialize($config)), array('id' => $instance->id));
        }
    }
    $instances->close();
}

// /*
//  * Returns array of departments that the user can see/edit
//  *
//  */
// function block_culschool_html_get_dept() {
//     global $USER;
//     // TODO
//     // If admin
//     if (is_siteadmin()) {
//         return array('cass', 'smcse', 'law', 'sass', 'shs', 'other');
//     }

//     $deptcodes = array (
//         'UU' => 'other',
//         'XX' => 'other',
//         'LL' => 'law',
//         'BB' => 'cass',
//         'AA' => 'sass',
//         'SS' => 'sass',
//         'EE' => 'smcse',
//         'HS' => 'shs',
//         'HA' => 'shs',
//         'HN' => 'shs',
//         'II' => 'smcse'
//     );

//     // else get school code
//     if (!empty($USER->institution)) {
//         $userschool = (substr(trim($USER->institution),0,2));

//         if (array_key_exists($userschool, $deptcodes)) {
//             $depts = array($deptcodes[$userschool]);
//         }

//     } /*elseif (isset($USER->department)) {
//         $userdept = (substr(trim($USER->department),0,2));

//         if (array_key_exists($userdept, $deptcodes)) {
//             $depts = array($deptcodes[$userdept]);
//         }

//     }*/

//     if (empty($depts)) {
//         $depts = array ('other');
//     }

//     return $depts;
// }

/*
 * Returns array of types that the user can see/edit
 *
 */
function block_culschool_html_get_type() {
    global $USER, $COURSE; 
    // if admin
    //$types = array('student', 'staff');
     
        $context = context_course::instance($COURSE->id);

        $can_edit = has_capability('moodle/course:update', $context, $USER->id, false);

        if ($can_edit) {
            $types = array('staff');
        } else {
            $types = array('student');
        }

    return $types;
}

/*
 * Returns array of categories in the hierarchy of the current course.
 *
 */
function block_culschool_html_get_category() {
    global $USER, $COURSE, $DB;

    $category = $COURSE->category;
    //$catnames = array();
    $cats = array();
    $select = "path LIKE '%/$category'";

    if ($result = $DB->get_record_select('course_categories', $select, null, 'path', $strictness=MUST_EXIST)) {
        
                 $longerpath = format_string($result->path);
                 $path = substr($longerpath, 1);
                 $cats = explode("/", $path);
                 //$paths = array_reverse($backpaths);
    }

    return $cats;
}