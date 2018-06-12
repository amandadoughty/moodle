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
 * CUL Boost.
 *
 * @package    theme_cul_boost
 * @copyright  2018 Stephen Sharpe, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
                                                             
defined('MOODLE_INTERNAL') || die();

/**
 * Returns the required JS files, including core Boost JS
 */
function theme_cul_boost_page_init(moodle_page $page) {
    global $CFG, $DB, $OUTPUT, $USER;

    $page->requires->js_call_amd('theme_cul_boost/loader', 'init');
    $page->requires->js_call_amd('theme_cul_boost/slider', 'init');
    $page->requires->js_call_amd('theme_cul_boost/dropdowns', 'init');
    $page->requires->js_call_amd('theme_cul_boost/drawermenu', 'init');
    $page->requires->js_call_amd('theme_cul_boost/navsearch', 'init');
    $page->requires->js_call_amd('theme_cul_boost/courselisting', 'init');
    $page->requires->js_call_amd('theme_cul_boost/dashpanel', 'init');
    $page->requires->js_call_amd('theme_cul_boost/settingsmenu', 'init');
    $page->requires->js_call_amd('theme_cul_boost/navigation', 'init');
    $page->requires->js_call_amd('theme_cul_boost/stickynav', 'init');
    $page->requires->js_call_amd('theme_cul_boost/fixedbuttons', 'init');

    return true;
}

/**
 * Returns the conditional settings for block region placements
 */
function theme_cul_boost_bootstrap_grid($hassidepost) {

    if ($hassidepost) {
        $regions = array('content' => 'col-xs-12 col-lg-10 col-xl-9 pb-2 mb-4');
        $regions['post'] = 'd-flex flex-wrap flex-column col-xs-12 col-lg-10 col-xl-3 pb-2 mb-4';
    } else {
        $regions = array('content' => 'col-xs-12 pb-2 mb-4');
        $regions['post'] = 'empty';
    }
    
    return $regions;
}

/**
 * Returns the correct url for files uploaded into theme settings
 */
function theme_cul_boost_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;

    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('acca_educationhub');
        theme_cul_boost_store_in_localcache($filearea, $args, $options);
        exit;
    } else {
        send_file_not_found();
    }
}

/**
 * Speeds up the delivery of theme setting files.
 */
function theme_cul_boost_store_in_localcache($filearea, $args, $options) {
    global $CFG;
    $filename = $args[1];
    $candidate = $CFG->localcachedir.'/theme_cul_boost/'.$filename;
    if (file_exists($candidate)) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header("Content-type:   ".finfo_file($finfo, $candidate));
        finfo_close($finfo);
        echo file_get_contents($candidate);
        return true;
    } else {
        require_once("$CFG->libdir/filelib.php");

        $syscontext = context_system::instance();
        $component = 'theme_cul_boost';

        if (!file_exists(dirname($candidate))) {
            @mkdir(dirname($candidate), $CFG->directorypermissions, true);
        }

        $revision = array_shift($args);
        if ($revision < 0) {
            $lifetime = 0;
        } else {
            $lifetime = 60*60*24*60;
            // By default, theme files must be cache-able by both browsers and proxies.
            if (!array_key_exists('cacheability', $options)) {
                $options['cacheability'] = 'public';
            }
        }

        $fs = get_file_storage();
        $relativepath = implode('/', $args);

        $fullpath = "/{$syscontext->id}/{$component}/{$filearea}/0/{$relativepath}";
        $fullpath = rtrim($fullpath, '/');
        $file = $fs->get_file_by_hash(sha1($fullpath));
        if ($file) {
            $contents = $file->get_content();
        } else {
            send_file_not_found();
        }
        if ($fp = fopen($candidate.'.tmp', 'xb')) {
            fwrite($fp, $contents);
            fclose($fp);
            rename($candidate.'.tmp', $candidate);
            @chmod($candidate, $CFG->filepermissions);
            @unlink($candidate.'.tmp'); // just in case anything fails
        }
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header("Content-type:   ".finfo_file($finfo, $candidate));
        finfo_close($finfo);
        echo file_get_contents($candidate);
        return true;
    }
}



// theme_cul functions

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_cul_boost_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG, $COURSE;

    $theme = theme_config::load('cul');

    $return = new stdClass;

    $return->hascourseimage = 'noimage';


    $return->courseimage = '';
    if ($COURSE->id > 1) {
        require_once($CFG->libdir. '/coursecatlib.php');
        $course = new course_in_list($COURSE);
        // Get course overview files.
        $contentimages = $contentfiles = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                $style = 'background-image: url('.$url.');';
                if ($dimensions = $file->get_imageinfo()) {
                    $ratio = $dimensions['width'] / $dimensions['height'];
                    if ($ratio < 2) {
                        $style .= 'background-size: auto 100%; background-position: right center; ratio:' .$ratio .';';
                    } else {
                        $style .= 'background-size: cover;';
                    }
                }
                $return->courseimage = html_writer::tag('div','', array('style' => $style, 'class' => 'imagediv'));
                $return->hascourseimage = 'hasimage';
            }
        }
    }

    $return->mainbuttons = '';
    $return->loginout = '';
    if ($page->bodyid != 'page-login-index') {
        $return->mainbuttons = theme_cul_boost_mainbuttons();
        // $return->loginout = theme_cul_login_logout();
    }

    $return->gradebookdisclaimer = theme_cul_boost_gradebook_disclaimer($page);

    return $return;
}

function theme_cul_boost_gradebook_disclaimer($page) {

    $gradebookids = array (
        'page-grade-report-user-index',
        'page-grade-report-culuser-index',
        'page-grade-report-overview-index',
        'page-course-user'
    );

    $content = '';
    if (in_array($page->bodyid, $gradebookids)) {
        $disclaimer = html_writer::tag('p', get_string('gradebookdisclaimer', 'theme_cul_boost'));
        $content = html_writer::tag('div', $disclaimer,
            array('class' => 'alert alert-warning', 'role' => 'note'));
    }
    return $content;
}

/**
 * Serve the grading panel as a fragment.
 *
 * @param array $args List of named arguments for the fragment loader.
 * @return string
 */
function theme_cul_boost_output_fragment_gradealert($args) {
    global $CFG, $OUTPUT;

    require_once($CFG->libdir.'/gradelib.php');
    require_once($CFG->dirroot . '/mod/assign/locallib.php');

    $o = '';    
    $courseid = clean_param($args['courseid'], PARAM_INT); 
    $assignid = clean_param($args['assignid'], PARAM_INT); 
    $userid = clean_param($args['userid'], PARAM_INT); 
    $context = $args['context'];
    $cangrade = has_capability('mod/assign:grade', $context);

    if ($context->contextlevel != CONTEXT_MODULE) {
        return null;
    }  
   

    // if($cangrade) {

        $gradinginfo = grade_get_grades(
            $courseid,
            'mod',
            'assign',
            $assignid,
            $userid
        );

        $gradingitem = null;
        $gradebookgrade = null;

        if (isset($gradinginfo->items[0])) {
            $gradingitem = $gradinginfo->items[0];
            $gradebookgrade = $gradingitem->grades[$userid];
        }

        if ($gradebookgrade->hidden){
            $o .= $OUTPUT->notification(get_string('gradehidden', 'theme_cul_boost'), 'error hazard');
        } else {
            $o .= $OUTPUT->notification(get_string('gradenothidden', 'theme_cul_boost'), 'error hazard');
        }
    // }

    return $o;
}

function theme_cul_boost_get_visible() {
    global $COURSE;
    return $COURSE->visible;
}

function theme_cul_boost_get_edituser($page) {
    $iseditprofilepage = $page->bodyid == 'page-user-editadvanced' || $page->bodyid == 'page-user-edit';
   
    if($iseditprofilepage && has_capability('moodle/user:editprofile', context_system::instance())) {
        return 'edituser';        
    }

    return '';
}

/**
 * Loads the JavaScript for the dynamic updating of the favourites menu.
 * Loads the JavaScript for the dynamic adding/removing current course from favourites. 
 *
 * @param moodle_page $page Pass in $PAGE.
 */
function theme_cul_boost_initialise_favourites(moodle_page $page) {
    $page->requires->yui_module('moodle-theme_cul_boost-favourites', 'M.theme_cul_boost.favourites.init', array());
    $page->requires->yui_module('moodle-theme_cul_boost-favourite', 'M.theme_cul_boost.favourite.init', array());
}

/**
 * Edits the user preference 'culcourse_listing_course_favourites'
 * Adds or deletes course id's
 *
 * @param string $action add or delete
 * @param int $cid course id
 * @return array $favourites a sorted array of course id's
 */
function theme_cul_boost_edit_favourites($action, $cid) {
    $favourites = array();

    if (!is_null($myfavourites = get_user_preferences('culcourse_listing_course_favourites'))) {
        $favourites = unserialize($myfavourites);
    }

    switch ($action) {
        case 'add':
            if (!in_array($cid, $favourites)){
                $favourites[] = $cid;
            }
            break;
        case 'remove':
            $key = array_search($cid, $favourites);
            if ($key !== false){
                unset($favourites[$key]);
            }
            break;
        default:
            break;
    }

    return $favourites;
}

/**
 * Sets user course favourites preference in culcourse_listing block
 *
 * @param array $favourites of course ids in sort order
 */
function theme_cul_boost_update_favourites($favourites) {
    set_user_preference('culcourse_listing_course_favourites', serialize($favourites));
}