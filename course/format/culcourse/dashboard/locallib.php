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
 * Local functions for format_culcourse.
 *
 * @package   format_culcourse
 * @copyright 2018 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* CONSTANTS */
if (!defined('OK')) {
    define('OK', 1);
}

if (!defined('NODATA')) {
    define('NODATA', 0);
}

if (!defined('ERROR')) {
    define('ERROR', -1);
}

if (!defined('SHOWHIDE')) {
    define('SHOWHIDE', 1);
}

if (!defined('MOVE')) {
    define('MOVE', 2);
}

if (!defined('QUICKLINK')) {
    define('QUICKLINK', 1);
}

if (!defined('ACTIVITYLINK')) {
    define('ACTIVITYLINK', 2);
}

/**
 * format_culcourse_get_reading_list_url_data()
 *
 * @param mixed $course
 * @return
 */
function format_culcourse_get_reading_list_url_data($course) {
    global $CFG;

    $site = trim(get_config('aspirelists', 'targetAspire')); // 1.x: $CFG->block_aspirelists_targetAspire;
    $codedata = format_culcourse_get_coursecode_data($course);
    #echo(html_writer::tag('pre', var_export($codedata, true))); //TJGDEBUG 09/08/2013 12:10:57
    if (!file_exists($CFG->dirroot . '/blocks/aspirelists/block_aspirelists.php')) {
        return false;
    }

    if (!$codedata || empty($site)) {
        return array('status' => 'NODATA');
    }

    $targetKG = get_config('aspirelists', 'targetKG'); // 1.x: $CFG->block_aspirelists_targetKG;
    $targetKG = empty($targetKG) ? 'modules' : $targetKG;

    $code = (count($codedata['module_codes']) == 1)
          ? $codedata['module_code']
          : implode('-', $codedata['module_codes'])
          ;
    $code = strtolower($code);

    $path = "{$site}/{$targetKG}/{$code}";
    $url  = "{$path}/lists/{$codedata['year_description']}.json";

    // Get the config timeout values.
    $connectiontimeout = trim(get_config('cul', 'connection_timeout'));
    $transfertimeout   = trim(get_config('cul', 'transfer_timeout'));

    $data = format_culcourse_get_reading_list_data($path, $url, $connectiontimeout, $transfertimeout);

    if (OK == $data['status']) {

        $resourcelists = $data['data'][$path]['http://purl.org/vocab/resourcelist/schema#usesList'];

        // If there's more than 1 module code (i.e. a supermodule), and more than 1 list item, use the parent node.
        if ((count($codedata['module_codes']) > 1) && (count($resourcelists) > 1)) {
            return array('status' => OK, 'listtype' => 'module', 'url' => $path);
        }

        foreach ($resourcelists as $resourcelist) {
            if (!empty($data['data'][$resourcelist['value']])) {
                return array('status' => OK, 'listtype' => 'module-year', 'url' => $resourcelist['value']);
            }
        }

        return array('status' => OK, 'listtype' => 'module', 'url' => $path); // Should never get here, but just a fallback!

    } else if (NODATA === $data['status']) {
        // If no reading list is returned for specified year, try using just the module name.
        $data = format_culcourse_get_reading_list_data($path, "{$path}.json", $connectiontimeout, $transfertimeout);
        if (OK == $data['status']) {
            return array('status' => OK, 'listtype' => 'module', 'url' => $path);
        }
    }

    return $data;
}


/**
 * format_culcourse_get_reading_list_data()
 *
 * Request the JSON data from Talis Aspire using cURL.
 * If the year filter is included in the url, then the response includes:-
 *     1. An object representing each list for the module for that year (if any exist)
 *     2. An object representing the module
 * If the year filter is not included in the url, then the response includes:-
 *     1. An object representing every list for the module
 *     2. An object representing the module
 *     3. An object representing the subject
 *
 * @param string $path
 * @param string $url
 * @param integer $connectiontimeout
 * @param integer $transfertimeout
 * @return array
 * @todo This is a duplicate of block_aspirelists::get_reading_list_data(). Consider using that one (although maybe we
 *       don't want that dependancy. I'll think about it when my brain doesn't ache so much!
 */
function format_culcourse_get_reading_list_data($path, $url, $connectiontimeout = null, $transfertimeout = null) {
    $connectiontimeout = (empty($connectiontimeout)) ? 4 : $connectiontimeout;
    $transfertimeout   = (empty($transfertimeout))   ? 8 : $transfertimeout;

    // Check validity of $connectiontimeout, and limit maximum value.
    if (!preg_match('/\A\d+\z/', $connectiontimeout) || ($connectiontimeout > 6)) {
        $connectiontimeout = 6;
    }

    // Check validity of $transfertimeout, and limit maximum value.
    if (!preg_match('/\A\d+\z/', $transfertimeout) || ($transfertimeout > 16)) {
        $transfertimeout = 16;
    }

    $rldata = array('status' => null, 'data' => null);

    #$urldebug = 'http://www.muon.org.uk/tsonus/moodle/ba-simple-proxy.php?url=' . $url . '&mode=native&delay=8'; //TJGDEBUG 08/08/2013 10:35:06
    $urldebug = empty($urldebug) ? '' : $urldebug;
    #echo(html_writer::tag('pre', "DASHBOARD:-\n$path\n$url\n$urldebug")); //TJGDEBUG 09/08/2013 12:10:57

    $ch = curl_init();
    $options = array(
        CURLOPT_URL            => empty($urldebug) ? $url : $urldebug,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_FAILONERROR    => true,
        CURLOPT_HEADER         => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => $connectiontimeout,
        CURLOPT_TIMEOUT        => $transfertimeout,
        CURLOPT_FOLLOWLOCATION => true
    );
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $curlinfo = curl_getinfo($ch);
    $rldata['info'] = $curlinfo;

    $curlerrno = curl_errno($ch);
    if ($curlerrno) {
        curl_close($ch);
        $rldata['curl_errno'] = $curlerrno;

        if ((22 == $curlerrno) && ($curlinfo['http_code'] < 500)) { // 22 => CURLE_HTTP_RETURNED_ERROR
            $rldata['status'] = NODATA;
        } else {
            $rldata['status'] = ERROR;
        }

        return $rldata;
    }

    curl_close($ch);

    if (empty($response)) {
        $rldata['status'] = NODATA;
        return $rldata;
    }

    $responsedata = json_decode($response, true);

    $resourcelists = array();
    if (!empty($responsedata[$path]['http://purl.org/vocab/resourcelist/schema#usesList'])) {
        $resourcelists = $responsedata[$path]['http://purl.org/vocab/resourcelist/schema#usesList'];
    }

    foreach ($resourcelists as $resourcelist) {
        // If there's at least one list then return the response data.
        if (!empty($responsedata[$resourcelist['value']])) {
            $rldata['status'] = OK;
            $rldata['data']   = $responsedata;
            return $rldata;
        }
    }

    $rldata['status'] = NODATA;
    return $rldata;
}


/**
 * format_culcourse_get_timetable_url()
 *
 * @param mixed $course
 * @return string timetable URL
 */
function format_culcourse_get_timetable_url($course) {
    global $COURSE, $CFG;

    $ttdata = array('url' => null, 'status' => 1, 'httpcode' => null, 'error' => null);
    $codedata = format_culcourse_get_coursecode_data($COURSE->shortname);
    $module = $codedata['module_code'];
    $year = $codedata['year_description'];
    $ttdata['url'] = new moodle_url('/local/cultimetable_api/timetable.php',
        array(
            'cid' => $COURSE->id,
            'mcode' => $module,
            'yr' => $year
        )
    );

    try {
        require_once($CFG->dirroot . '/local/cultimetable_api/classes/timetable_class.php');
        list($weekoptions, $defaultweeks, $formatoptions, $defaultformat) = local_cultimetable_api\timetable::get_timetable_config();
        $timetable = new local_cultimetable_api\timetable();
        $result = $timetable->display_module_timetable($module, $defaultweeks, $defaultformat, $COURSE->id);

        $ttdata['error'] = $result['error'];
        $ttdata['httpcode'] = $result['http'];

        if($ttdata['httpcode'] == 500) { // No matching timetable page results in a 500 error!
            $ttdata['status'] = 0;
        } else if ($ttdata['httpcode'] <> 200) {
            $ttdata['status'] = -1;
        } else {
            $ttdata['status'] = 1;
        }
    } catch (Exception $e) {
        $ttdata['error'] = $e->getCode();
        $ttdata['httpcode'] = 520;
        $ttdata['status'] = -1;
    }

    return $ttdata;
}


/**
 * format_culcourse_get_photoboard_url()
 *
 * @param mixed $course
 * @return string photoboard URL
 */
function format_culcourse_get_photoboard_url($course, $roleid) {
    global $COURSE, $CFG;

    $cname = explode(" ",$COURSE->fullname);
    $cid = $cname[0];
    $coursecontext = context_course::instance($COURSE->id);
    $url = new moodle_url('/course/format/culcourse/dashboard/photoboard.php', array('contextid' => $coursecontext->id, 'roleid' => $roleid));

    return $url;
}


/**
 * format_culcourse_get_coursecode_data()
 *
 * @param mixed $coursecode Course object or course-code string.
 * @return mixed array of coursecode data, e.g.:-
 *     array (
 *         'full_code' => 'MDL_IN3001-INM370_PRD2_A_2013-14',
 *         'code_type' => 'MDL',
 *         'module_codes_string' => 'IN3001-INM370',
 *         'module_codes' => array (
 *                              0 => 'IN3001',
 *                              1 => 'INM370',
 *                          ),
 *         'module_code' => 'IN3001',
 *         'module_type' => 'undergraduate',
 *         'period' => 'PRD2',
 *         'period_num' => '2',
 *         'occurrence_code' => 'A',
 *         'year_description' => '2013-14',
 *         'year_start' => '2013',
 *         'year_end' => '2014',
 *     )
 * @todo This function should be centralised, rather than duplicated here! I'm sure it'll come in handy elsewhere.
 *       Perhaps we should have a City course (module) metadata utility class in local/culutily or something.
 */
function format_culcourse_get_coursecode_data($coursecode) { //TODO: Candidate for a static utility method.

    $codedata = array();

    if (is_object($coursecode) && property_exists($coursecode, 'shortname')) {
        $codedata['full_code'] = trim(strtoupper($coursecode->shortname));
    } else if (is_string($coursecode)) {
        $codedata['full_code'] = trim(strtoupper($coursecode));
    } else {
        debugging(__FUNCTION__ . '(): Parameter 1 must be a course object or course-code string.');
    }

    if (empty($codedata['full_code'])) {
        return false;
    }

    $codedatakeys = array('code_type',
                          'module_codes_string', 'module_codes', 'module_code', 'module_type',
                          'period', 'period_num', 'occurrence_code',
                          'year_description', 'year_start', 'year_end');

    // Initialise $codedata elements to null.
    foreach($codedatakeys as $key) {
        $codedata[$key] = null;
    }

    $components = explode('_', $codedata['full_code']);

    // Check code type prefix.
    if (preg_match('/\AMDL|GEN|PLAY|TEMP\z/sim', $components[0])) {
        $codedata['code_type'] = strtoupper($components[0]);
        $codedata['module_codes_string'] = strtoupper($components[1]);
    } else {
        $codedata['module_codes_string'] = strtoupper($components[0]);
    }

    // Populate module codes. A super-module will have multiple codes.
    if (strpos($codedata['module_codes_string'], '-')) {
        $codedata['module_codes'] = explode('-', $codedata['module_codes_string']);

        // Apply the prefix from the first code to subsequent ones, iff they consist entirely of digits. //TODO: Check this assumption!
        if (preg_match('/\A([a-z]+)\d+\z/sim', $codedata['module_codes'][0], $matches)) {
            for ($i = 1; $i < count($codedata['module_codes']); $i++) {
                if (preg_match('/\A\d+\z/', $codedata['module_codes'][$i])) {
                    $prefix = $matches[1];
                    $codedata['module_codes'][$i] = $prefix . $codedata['module_codes'][$i];
                }
            }
        }
    } else {
        $codedata['module_codes'][] = $codedata['module_codes_string'];
    }

    // Primary (first) module code.
    $codedata['module_code'] = $codedata['module_codes'][0];

    // Extract module type from the primary module code.
    if (preg_match('/\A[a-z]{2}\d{4}\z/sim', $codedata['module_code'])) {
        $codedata['module_type'] = 'undergraduate';
    } else if (preg_match('/\A[a-z]{3}\d{3}\z/sim', $codedata['module_code'])) {
        $codedata['module_type'] = 'postgraduate';
    }

    // Extract period and occurrence code, if present.
    if (preg_match('/\A.+_(PRD(\d+))_(?:(\w+)_)?.*\z/sim', $codedata['full_code'], $matches)) {
        $codedata['period']     = strtoupper($matches[1]);
        $codedata['period_num'] = $matches[2];
        if (!empty($matches[3])) {
            $codedata['occurrence_code'] = $matches[3];
        }
    }

    // Extract year components, if present.
    if (preg_match('/\A.+_((20)(\d{2})-(\d{2}))\z/sim', $codedata['full_code'], $matches)) {
        $codedata['year_description'] = $matches[1];
        $codedata['year_start'] = $matches[2] . $matches[3];
        $codedata['year_end']   = $matches[2] . $matches[4];
    }

    return $codedata;
}

function format_culcourse_quicklink_visibility($courseid, $name, $value) { 
    $format = course_get_format($courseid);
    $options = $format->get_format_options();    

    if ($options) {
        $options[$name] = $value;
        $options = (object)$options;
        $format->update_course_format_options($options);
    } else {
        $options = [];
        $options[$name] = $value;
    }

    $options = (object)$options;
    $format->update_course_format_options($options);
}

function format_culcourse_dashlink_move($courseid, $name, $link, $moveto = null) {
    $format = course_get_format($courseid);
    $options = $format->get_format_options();    

    if ($options) {
        if (isset($options[$name])) {
            $value = $options[$name];
            $value = explode(',', $value);
            $flipped = array_flip($value);
            $fromindex = $flipped[$link];

            if ($moveto == 'end') {
                $out = array_splice($value, $fromindex, 1);
                array_push($out, $value);
            } else {
                $toindex = $flipped[$moveto];
                $newvalue = [];
                $out = $value[$fromindex];

                foreach ($value as $key => $item) {
                    if ($key == $fromindex || $key == $toindex) {
                        if ($key == $fromindex) {
                            // We do not insert the moved item in the same place.
                            continue;
                        } else if ($fromindex < $toindex) {
                            $newvalue[] = $item;
                            // We insert the moved item in the new position.
                            $newvalue[] = $out;
                            continue;
                        } else {
                            // We insert the moved item in the new position.
                            $newvalue[] = $out; 
                        }
                    }
                    // All other items are inserted in their original order.
                    $newvalue[] = $item;
                }

                $value = $newvalue;
            }

            $value = join(',', $value);
            $options[$name] = $value;
            $options = (object)$options;
            $format->update_course_format_options($options);
echo(print_r($options));
            return true;            
        } 
    }

    return false;
}

function format_culcourse_get_edit_link($courseid, $name, $value) {    
    // Course format settings are 2 = show, 1 = hide.
    if ($value == 2) {
        $newvalue = 1;
        $editicon = 'fa-eye';        
        $title = 'dashhidelink';
    } else {
        $newvalue = 2;
        $editicon = 'fa-eye-slash';
        $title = 'dashshowlink';
    }

    $name = 'show' . $name;
    $editattrs['title'] = get_string($title, 'format_culcourse');
    $editattrs['class'] = 'dashlinkedit';
    $params = [
        'courseid' => $courseid,
        'action' => SHOWHIDE,
        'name' => $name,
        'showhide' => $newvalue,
        'sesskey' => sesskey()
    ];
    $editurl = new moodle_url(
        '/course/format/culcourse/dashboard/dashlink_edit.php', $params
        );
        

    $editurl = $editurl->out();

    return [$editurl, $editicon, $editattrs];
}

function format_culcourse_get_move_link($courseid, $copy, $name) {    
    $moveicon = 'fa-arrows';        
    $title = 'dashmovelink';
    $moveattrs['title'] = get_string('move');
    $moveattrs['class'] = 'dashlinkmove';
    $params = [
        'courseid' => $courseid,
        'action' => MOVE,
        'copy' => $copy,
        'name' => $name . 'sequence',
        'sesskey' => sesskey()
    ];

    $moveurl = new moodle_url(
        '/course/format/culcourse/dashboard/dashlink_edit.php', $params
        );

    $moveurl = $moveurl->out();

    return [$moveurl, $moveicon, $moveattrs];
}

function format_culcourse_get_moveto_link($courseid, $moveto, $type) {
    global $USER, $OUTPUT;

    $setting = $type . 'sequence';
    $name = $type . 'copy';
    $movetourl = '';
    $movetoicon = '';
    $movetoattrs = [];

    if (!isset($USER->$name)) {
        return [$movetourl, $movetoicon, $movetoattrs];
    }

    if (get_string_manager()->string_exists($USER->$name, 'format_culcourse')) {
        $copyfullname = get_string($USER->$name, 'format_culcourse');
    } else if (get_string_manager()->string_exists('pluginname', 'mod_' . $USER->$name)) {
        $copyfullname = get_string('pluginname', 'mod_' . $USER->$name);
    } else {
        $copyfullname = $USER->$name;
    }

    $movetoicon = $OUTPUT->image_url('movehere', 'core');     
    $title = 'dashmovelink';
    $movetoattrs['title'] = get_string($title, 'format_culcourse', $copyfullname);
    $movetoattrs['class'] = 'dashlinkmove';

    $params = [
        'courseid' => $courseid,
        'action' => MOVE,
        'name' => $setting,
        'moveto' => $moveto,
        'sesskey' => sesskey()
    ];
    $movetourl = new moodle_url(
        '/course/format/culcourse/dashboard/dashlink_edit.php', $params
        );

    $movetourl = $movetourl->out();

    return [$movetourl, $movetoicon, $movetoattrs];
}

/**
 * Determines if the logged in user is currently moving a quicklink
 *
 * @param int $courseid The id of the course being tested
 * @return bool
 */
function ismovingquicklink($courseid) {
    global $USER;

    if (!empty($USER->quicklinkcopy)) {
        return ($USER->quicklinkcopycourse == $courseid);
    }
    return false;
}

/**
 * Determines if the logged in user is currently moving an activity link
 *
 * @param int $courseid The id of the course being tested
 * @return bool
 */
function ismovingactivitylink($courseid) {
    global $USER;

    if (!empty($USER->activitylinkcopy)) {
        return ($USER->activitylinkcopycourse == $courseid);
    }
    return false;
}

function format_culcourse_get_lti_instances($course, $type) {
    global $CFG, $USER;

    require_once($CFG->dirroot.'/mod/lti/lib.php');

    $ltis = [];
    $basicltis = get_coursemodules_in_course('lti', $course->id, 'm.typeid');

    $basicltis = get_all_instances_in_course('lti', $course, $USER->id, true);

    // lti_filter_get_types($course)

    // lti_get_type_config_by_instance($instance)

    foreach($basicltis as $basiclti) {
        if($basiclti->typeid == $type->id) {
            $ltis[] = $basiclti;
        }
    }

    return $ltis;
}