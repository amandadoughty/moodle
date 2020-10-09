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

function theme_cul_boost_process_css($css, $theme) {
    global $CFG;
    if(empty($CFG->themewww)){
        $themewww = $CFG->wwwroot."/theme";
    }
    $tag = '[[fontsdir]]';
    $css = str_replace($tag, $themewww.'/cul_boost/fonts/', $css);

    $customcss = $theme->settings->customcss;
    $css = $css . $customcss;

    $tag = "fill='#";
    $css = str_replace($tag, "fill='%23", $css);

    return $css;
}

/**
 * Returns the required JS files, including core Boost JS
 */
function theme_cul_boost_page_init(moodle_page $page) {
    global $CFG, $DB, $OUTPUT, $USER, $COURSE;

    $page->requires->jquery_plugin('ui');
    $page->requires->jquery_plugin('ui-css');
    $page->requires->js_call_amd('theme_cul_boost/loader', 'init');
    $page->requires->js_call_amd('theme_cul_boost/dropdowns', 'init');
    $page->requires->js_call_amd('theme_cul_boost/drawermenu', 'init');
    $page->requires->js_call_amd('theme_cul_boost/navsearch', 'init');
    $page->requires->js_call_amd('theme_cul_boost/courselisting', 'init');
    // $page->requires->js_call_amd('theme_cul_boost/dashpanel', 'init');

    $adminnode = $page->settingsnav->find('siteadministration', navigation_node::TYPE_SITE_ADMIN);
    $arguments = array(
            'adminnodeid' => $adminnode ? $adminnode->id : null
        );

    $page->requires->js_call_amd('theme_cul_boost/settingsmenu', 'init', $arguments);
    $page->requires->js_call_amd('theme_cul_boost/navigation', 'init');
    $page->requires->js_call_amd('theme_cul_boost/stickynav', 'init');
    $page->requires->js_call_amd('theme_cul_boost/fixedbuttons', 'init');
    $page->requires->js_call_amd('theme_cul_boost/modalposition', 'init');
    $page->requires->js_call_amd('theme_cul_boost/showcourse', 'init', ['courseid' => $COURSE->id]);
    // Save scroll position when editing is turned on/off
    // $page->requires->js_call_amd('theme_cul_boost/savescrollpos', 'init');
    $page->requires->js_call_amd('theme_cul_boost/offsetheader', 'init');
    $page->requires->js_call_amd('theme_cul_boost/favourites', 'init');
    $page->requires->js_call_amd('theme_cul_boost/favourite', 'init');

    return true;
}

/**
 * Returns the conditional settings for block region placements
 */
function theme_cul_boost_bootstrap_grid($hassidepost) {

    if ($hassidepost) {
        $regions = array('content' => 'col-12 col-lg-10 col-xl-9 border-0 py-0 pb-2 mb-4');
        $regions['post'] = 'd-flex flex-wrap flex-column col-12 col-lg-10 col-xl-3 pb-2 mb-4';
    } else {
        $regions = array('content' => 'col-12 pb-2 mb-4 border-0');
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
        $theme = theme_config::load('cul_boost');
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

/**
 * Serves the grading panel as a fragment.
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

    if($cangrade) {
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
    }

    return $o;
}

// Override all of the messaging api ajax calls to remove hidden photos.
function theme_cul_boost_override_webservice_execution($function, $params) {
    switch ($function->name) {
        case 'core_message_get_member_info':
        case 'core_message_get_user_contacts':
        case 'core_message_get_contact_requests':
        case 'core_message_get_conversation_members':       
            $result = call_user_func_array([$function->classname, $function->methodname], $params);
            $result = theme_cul_boost_edit_message_contact_photo ($result);

            return $result;
            break;

        case 'core_message_get_conversation_messages':
            $result = call_user_func_array([$function->classname, $function->methodname], $params);
            $result['members'] = theme_cul_boost_edit_message_contact_photo ($result['members']);

            return $result;
            break;

        case 'core_message_message_search_users':
            $result = call_user_func_array([$function->classname, $function->methodname], $params);
            $result['contacts'] = theme_cul_boost_edit_message_contact_photo ($result['contacts']);
            $result['noncontacts'] = theme_cul_boost_edit_message_contact_photo ($result['noncontacts']);

            return $result;
            break;

        case 'core_message_get_conversations':
            $result = call_user_func_array([$function->classname, $function->methodname], $params);

            foreach ($result->conversations as $key => &$conversation) {
                $conversation->members = theme_cul_boost_edit_message_contact_photo ($conversation->members);
            }

            return $result;
            break;

        default:
            return false;
    }
}

function theme_cul_boost_edit_message_contact_photo ($members) {
    global $CFG, $USER, $PAGE, $DB;

    $renderer = $PAGE->get_renderer('core');

    foreach ($members as $member) {
        if ($member->id == $USER->id) {
            continue;
        }

        $context = \context_system::instance();

        if (!has_capability('moodle/user:viewhiddendetails', $context)) { 
            $sql = 'SELECT shortname, data
                    FROM {user_info_data} uid
                    JOIN {user_info_field} uif
                    ON uid.fieldid = uif.id
                    WHERE uid.userid = :userid';

            if ($result = $DB->get_records_sql($sql, array('userid' => $member->id))){
                if(isset($result['publicphoto']->data) && $result['publicphoto']->data == 0) {

                    $defaultprofileimageurl = $renderer->image_url('u/f1'); // default image.
                    $defaultprofileimageurlsmall = $renderer->image_url('u/f2'); // default image.

                    if (!empty($CFG->enablegravatar)) {
                        // Build a gravatar URL with what we know.

                        // Find the best default image URL we can (MDL-35669)
                        if (empty($CFG->gravatardefaulturl)) {
                            $absoluteimagepath = $page->theme->resolve_image_location('u/'.$filename, 'core');
                            if (strpos($absoluteimagepath, $CFG->dirroot) === 0) {
                                $gravatardefault = $CFG->wwwroot . substr($absoluteimagepath, strlen($CFG->dirroot));
                            } else {
                                $gravatardefault = $CFG->wwwroot . '/pix/u/' . $filename . '.png';
                            }
                        } else {
                            $gravatardefault = $CFG->gravatardefaulturl;
                        }

                        // If the currently requested page is https then we'll return an
                        // https gravatar page.
                        if (is_https()) {
                            $defaultprofileimageurl = new moodle_url("https://secure.gravatar.com/avatar", array('s' => 100, 'd' => $gravatardefault));
                            $defaultprofileimageurlsmall = new moodle_url("https://secure.gravatar.com/avatar", array('s' => 35, 'd' => $gravatardefault));
                        } else {
                            $defaultprofileimageurl =  new moodle_url("http://www.gravatar.com/avatar/{$md5}", array('s' => 100, 'd' => $gravatardefault));
                            $defaultprofileimageurlsmall =  new moodle_url("http://www.gravatar.com/avatar/{$md5}", array('s' => 35, 'd' => $gravatardefault));
                        }
                    }

                    $member->profileimageurl = $defaultprofileimageurl->out(false);
                    $member->profileimageurlsmall = $defaultprofileimageurlsmall->out(false);
                    
                    

                }
            }
        }
    }

    return $members;
}

