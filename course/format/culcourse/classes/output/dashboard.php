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
 * @subpackage cul
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

namespace format_culcourse\output;

use renderer_base;
use renderable;
use templatable;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/culcourse/dashboard/locallib.php');

class dashboard implements templatable, renderable {

    /**
     * @var $course - The course instance.
     */
    public $course = null;

    /**
     * @var $course - The plugin settings.
     */
    public $culconfig = null;

    /**
     * @var $course - The plugin settings.
     */
    public $userisediting = null;

    /**
     * @var $course - The plugin settings.
     */
    public $adminurl = null;

    /**
     * Constructor method, calls the parent constructor - MDL-21097
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct($course, $culconfig) {
        global $PAGE;

        $this->userisediting = $PAGE->user_is_editing();

        if ($this->userisediting) {
            $adminurl = new \moodle_url('/course/format/culcourse/dashboard/quicklink_edit_ajax.php');
            $this->adminurl = $adminurl->out();
        }

        $this->course = $course;
        $this->culconfig = $culconfig;
    }

    public function export_for_template(renderer_base $output) {
        $export = new stdClass();
        $export->quicklinksexist = false;
        $export->activitiesexist = false;
        $export->userisediting = $this->userisediting;
        $export->adminurl = $this->adminurl;
        $export->quicklinks = $this->quicklink_display($this->course);

        if(count($export->quicklinks)) {
            $export->quicklinksexist = true;
        }

        $export->activities = $this->activity_modules_display($this->course);

        if(count($export->activities)) {
            $export->activitiesexist = true;
        }

        return $export;
    }

    /**
     * 
     *
     * @param stdClass $course
     * @return string
     *
     * TODO: Create a master mapping/dispatch table to make it easy to add additional
     *       Quick Links in future. This could automatically be picked-up by the block
     *       config form. For now, it's over-engineering.
     */
    public function quicklink_display($course) {
        global $CFG, $DB, $OUTPUT;

        $linkitems = array();
        $coursecontext = \context_course::instance($course->id);

        // Reading list
        if ($this->culconfig['showreadinglists'] == 2 || $this->userisediting) {
            $lnktxt = get_string('aspirelists', 'format_culcourse');
            $attrs  = array ();
            $liattrs = array();
            $class = '';
            $editurl = '';
            $editicon = '';
            $editattrs = '';

            if ($this->userisediting) {
                list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                    $course->id, 
                    'readinglists', 
                    $this->culconfig['showreadinglists']
                    );
            }

            $urldata = format_culcourse_get_reading_list_url_data($course);

            if ($this->userisediting&& ($this->culconfig['showreadinglists'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$urldata) {
                // Not installed or not configured                
                $attrs['title'] = get_string('not-installed-readinglist', 'format_culcourse');
                $attrs['class'] = 'nolink';
                $url = 'javascript:void(0);';
                $liattrs['class'] = 'wide';
            } else {
                if (OK == $urldata['status']) {
                    $listtype = $urldata['listtype'];
                    $url = $urldata['url'];

                    if ('module' == $listtype) {
                        $attrs['title'] = get_string('view-readinglist-module', 'format_culcourse');
                        $attrs['target'] = '_blank';
                    } else if ('module-year' == $listtype) {
                        $attrs['title'] = get_string('view-readinglist-module-year', 'format_culcourse');
                        $attrs['target'] = '_blank';
                    }
                } else if (NODATA == $urldata['status']) {
                    $attrs['title'] = get_string('no-readinglist', 'format_culcourse');
                    $attrs['class'] = 'nolink';
                    $url = 'javascript:void(0);';
                } else if (ERROR == $urldata['status']) {
                    $attrs['title'] = get_string('error-readinglist', 'format_culcourse');
                    $attrs['class'] = 'nolink';
                    $url = 'javascript:void(0);';
                }
            }

            $linkitems[] = [
                'url' => $url,
                'icon' => 'fa fa-bookmark',
                'text' => $lnktxt,
                'attrs' => $attrs,
                'class' => $class,
                'liattrs' => $liattrs,
                'editurl' => $editurl, 
                'editicon' => $editicon, 
                'editattrs' => $editattrs
            ];


        }

        // Timetable link
        if ($this->culconfig['showtimetable'] == 2 || $this->userisediting) {
            $lnktxt = get_string('timetable', 'format_culcourse');
            $attrs = array ();
            $liattrs = array();
            $class = '';
            $editurl = '';
            $editicon = '';
            $editattrs = '';

            if ($this->userisediting) {
                list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                    $course->id, 
                    'showtimetable', 
                    $this->culconfig['showtimetable']
                    );
            }

            $ttdata = format_culcourse_get_timetable_url($course);

            if ($this->userisediting&& ($this->culconfig['showtimetable'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$ttdata) {
                // Not installed or not configured.
                $attrs['title'] = get_string('not-installed-timetable', 'format_culcourse');
                $attrs['class'] = 'nolink';
                $url = 'javascript:void(0);';
            } else {
                if (OK == $ttdata['status']) {
                    $attrs['title']  = get_string('view-timetable', 'format_culcourse');
                    $attrs['target'] = '_blank';
                    $url = $ttdata['url'];
                } else if (NODATA == $ttdata['status']) {
                    $attrs['title']  = get_string('no-timetable', 'format_culcourse');
                    $attrs['class'] = 'nolink';
                    $attrs['target'] = '_blank';
                    $url = $ttdata['url'];
                } else if (ERROR == $ttdata['status']) {
                    $attrs['title'] = get_string('error-timetable', 'format_culcourse');
                    $attrs['class'] = 'nolink';
                    $url = 'javascript:void(0);';
                }
            }

            $linkitems[] = [
                'url' => $url,
                'icon' => 'fa-clock-o',
                'text' => $lnktxt,
                'attrs' => $attrs,
                'class' => $class,
                'liattrs' => $liattrs,
                'editurl' => $editurl, 
                'editicon' => $editicon, 
                'editattrs' => $editattrs
            ];
        }

        // Grades
        if ($this->culconfig['showgraderreport'] == 2 || $this->userisediting) {
            $lnktxt = get_string('grades', 'grades');
            $attrs  = array ();
            $liattrs = array();
            $class = '';
            $editurl = '';
            $editicon = '';
            $editattrs = '';

            if ($this->userisediting) {
                list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                    $course->id, 
                    'showgraderreport', 
                    $this->culconfig['showgraderreport']
                    );
            }            

            if ($this->userisediting&& ($this->culconfig['showgraderreport'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (has_capability('gradereport/grader:view', $coursecontext)) { // Teacher, ...
                $lnktxt = get_string('graderreport', 'grades');
                $attrs['title'] = get_string('view-graderreport', 'format_culcourse');
                $url = new \moodle_url('/grade/report/grader/index.php', array('id' => $course->id));
            } else if (has_capability('moodle/grade:view', $coursecontext)) { // Student
                $attrs['title'] = get_string('viewgrades', 'grades');
                $url = new \moodle_url('/grade/report/culuser/index.php', array('id' => $course->id));
            } else  {
                $attrs['title'] = get_string('no-view-grades', 'format_culcourse');
                $attrs['class'] = 'nolink';
                $url = 'javascript:void(0);';
            }

            $linkitems[] = [
                'url' => $url,
                'icon' => 'fa-mortar-board',
                'text' => $lnktxt,
                'attrs' => $attrs,
                'class' => $class,
                'liattrs' => $liattrs,
                'editurl' => $editurl, 
                'editicon' => $editicon, 
                'editattrs' => $editattrs
            ];
        }

        // Calendar
        if ($this->culconfig['showcalendar'] == 2 || $this->userisediting) {
            $lnktxt = get_string('calendar', 'calendar');
            $attrs  = array ();
            $liattrs = array();
            $class = '';
            $editurl = '';
            $editicon = '';
            $editattrs = '';

            if ($this->userisediting) {
                list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                    $course->id, 
                    'showcalendar', 
                    $this->culconfig['showcalendar']
                    );
            }            

            if ($this->userisediting&& ($this->culconfig['showcalendar'] != 2)) {
                    $class = 'linkhidden';                
            }

            $attrs['title'] = get_string('view-calendar', 'format_culcourse');
            $url  = new \moodle_url('/calendar/view.php', array('view' => 'month', 'course' => $course->id));

            $linkitems[] = [
                'url' => $url,
                'icon' => 'fa-calendar',
                'text' => $lnktxt,
                'attrs' => $attrs,
                'class' => $class,
                'liattrs' => $liattrs,
                'editurl' => $editurl, 
                'editicon' => $editicon, 
                'editattrs' => $editattrs
            ];
        }

        // Photoboards
        foreach (role_get_names($coursecontext, ROLENAME_ALIAS) as $role) {
            $options[$role->id] = $role->localname;
        }
        // Student Photoboard
        if ($this->culconfig['showstudents'] == 2 || $this->userisediting) {
            $studentrole = $DB->get_record('role', array('shortname'=>'student'));

            if ($studentrole){
                $attrs  = array ();
                $liattrs = array();
                $class = '';
                $editurl = '';$editicon = '';$editattrs = '';

                if ($this->userisediting) {
                    list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                        $course->id, 
                        'showstudents', 
                        $this->culconfig['showstudents']
                        );
                }

                $alias = $options[$studentrole->id];
                $lnktxt = $alias . 's';

                if ($this->userisediting && ($this->culconfig['showstudents'] != 2)) {
                        $class = 'linkhidden';                
                }

                if (count_role_users($studentrole->id, $coursecontext, false)){
                    $attrs['title']  = get_string('view-student-photoboard', 'format_culcourse', $alias);
                    $attrs['target'] = '';
                    $url = format_culcourse_get_photoboard_url($course, $studentrole->id);
                } else {
                    $attrs['class'] = 'nolink';
                    $attrs['title']  = get_string('no-view-student-photoboard', 'format_culcourse', $alias);
                    $url = 'javascript:void(0);';
                }

                $linkitems[] = [
                    'url' => $url,
                    'icon' => 'fa-users',
                    'text' => $lnktxt,
                    'attrs' => $attrs,
                    'class' => $class,
                    'liattrs' => $liattrs,
                    'editurl' => $editurl, 
                    'editicon' => $editicon, 
                    'editattrs' => $editattrs
                ];
            }
        }

        // Lecturer Photoboard
        if ($this->culconfig['showlecturers'] == 2 || $this->userisediting) {
            $lecturerrole = $DB->get_record('role', array('shortname'=>'lecturer'));

            if ($lecturerrole){
                $attrs  = array ();
                $liattrs = array();
                $class = '';
                $editurl = '';
                $editicon = '';
                $editattrs = '';

                if ($this->userisediting) {
                    list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                        $course->id, 
                        'showlecturers', 
                        $this->culconfig['showlecturers']
                        );
                }

                $alias = $options[$lecturerrole->id];
                $lnktxt = $alias . 's';

                if ($this->userisediting && ($this->culconfig['showlecturers'] != 2)) {
                        $class = 'linkhidden';                
                }

                if (count_role_users($lecturerrole->id, $coursecontext, false)){
                    $attrs['title']  = get_string('view-lecturer-photoboard', 'format_culcourse', $alias);
                    $attrs['target'] = '';
                    $url = format_culcourse_get_photoboard_url($course, $lecturerrole->id);
                } else {
                    $attrs['class'] = 'nolink';
                    $attrs['title']  = get_string('no-view-lecturer-photoboard', 'format_culcourse', $alias);
                    $url = 'javascript:void(0);';
                }

                $linkitems[] = [
                    'url' => $url,
                    'icon' => 'fa-users',
                    'text' => $lnktxt,
                    'attrs' => $attrs,
                    'class' => $class,
                    'liattrs' => $liattrs,
                    'editurl' => $editurl, 
                    'editicon' => $editicon, 
                    'editattrs' => $editattrs
                ];
            }
        }

        // Course Officer Photoboard
        if ($this->culconfig['showcourseofficers'] == 2 || $this->userisediting) {
            $courseofficerrole = $DB->get_record('role', array('shortname'=>'courseofficer'));

            if ($courseofficerrole){
                $attrs  = array ();
                $liattrs = array();
                $class = '';
                $editurl = '';
                $editicon = '';
                $editattrs = '';

                if ($this->userisediting) {
                    list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                        $course->id, 
                        'showcourseofficers', 
                        $this->culconfig['showcourseofficers']
                        );
                }

                $alias = $options[$courseofficerrole->id];
                $lnktxt = $alias . 's';

                if ($this->userisediting && ($this->culconfig['showcourseofficers'] != 2)) {
                        $class = 'linkhidden';                
                }

                if (count_role_users($courseofficerrole->id, $coursecontext, false)){
                    $attrs['title']  = get_string('view-courseofficer-photoboard', 'format_culcourse', $alias);
                    $attrs['target'] = '';
                    $url = format_culcourse_get_photoboard_url($course, $courseofficerrole->id);
                } else {
                    $attrs['class'] = 'nolink';
                    $attrs['title']  = get_string('no-view-courseofficer-photoboard', 'format_culcourse', $alias);
                    $url = 'javascript:void(0);';
                }

                $linkitems[] = [
                    'url' => $url,
                    'icon' => 'fa-users',
                    'text' => $lnktxt,
                    'attrs' => $attrs,
                    'class' => $class,
                    'liattrs' => $liattrs,
                    'editurl' => $editurl, 
                    'editicon' => $editicon, 
                    'editattrs' => $editattrs
                ];
            }           
        }

        // Media gallery
        if ($this->culconfig['showmedia'] == 2 || $this->userisediting) {
            $lnktxt = get_string('media', 'format_culcourse');
            $attrs  = array ();
            $liattrs = array();
            $class = '';
            $editurl = '';
            $editicon = '';
            $editattrs = '';

            if ($this->userisediting) {
                list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                    $course->id, 
                    'showmedia', 
                    $this->culconfig['showmedia']
                    );
            }            

            if ($this->userisediting&& ($this->culconfig['showmedia'] != 2)) {
                    $class = 'linkhidden';                
            }

            $attrs['title'] = get_string('view-media', 'format_culcourse');
            $url  = new \moodle_url('/local/kalturamediagallery/index.php', array('courseid' => $course->id));

            $linkitems[] = [
                'url' => $url,
                'icon' => 'fa-file-video-o',
                'text' => $lnktxt,
                'attrs' => $attrs,
                'class' => $class,
                'liattrs' => $liattrs,
                'editurl' => $editurl, 
                'editicon' => $editicon, 
                'editattrs' => $editattrs
            ];
        }

        return $linkitems;
    }

    /**
     * 
     *
     * @param stdClass $course
     * @return
     */
    public function activity_modules_display($course) {
        global $CFG, $OUTPUT;

        require_once($CFG->dirroot . '/course/lib.php');

        $modinfo = get_fast_modinfo($course);

        $modfullnames = [];
        $archetypes = [];
        $activities = [];

        foreach($modinfo->cms as $cm) {

            // Exclude activities which are not visible or have no link (=label)
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }

            if (array_key_exists($cm->modname, $modfullnames)) {
                continue;
            }

            if (!array_key_exists($cm->modname, $archetypes)) {
                $archetypes[$cm->modname] = plugin_supports('mod', $cm->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
            }

            if ($archetypes[$cm->modname] == MOD_ARCHETYPE_RESOURCE) {

                if (!array_key_exists('resources', $modfullnames)) {
                    $modfullnames['resources'] = get_string('resources');
                }

            } else {
                $modfullnames[$cm->modname] = $cm->modplural;
            }
        }

        if (!count($modfullnames)) {
            return '';
        }

        \core_collator::asort($modfullnames);

        foreach ($modfullnames as $modname => $modfullname) {
            if($modname == 'lti') {
                $activities = $this->exttools_modules_display($course, $modinfo);
                continue;
            }

            if ((isset($this->culconfig['show' . $modname]) && $this->culconfig['show' . $modname] == 2)
                || $this->userisediting) 
            {
                $liattrs = array();
                $liattrs['title']  = get_string('view-mod', 'format_culcourse', strtolower($modfullname));
                $class = '';
                $editurl = '';
                $editicon = '';
                $editattrs = '';
                
                if ($this->userisediting) {
                    list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                        $course->id, 
                        'show' . $modname, 
                        $this->culconfig['show' . $modname]
                        );
                }

                if ($this->userisediting && ($this->culconfig['show' . $modname] != 2)) {
                        $class = 'linkhidden';                
                }

                if ($modname === 'resources') {
                    $url = new \moodle_url('/course/resources.php', array('id' => $course->id));
                    $icon = $OUTPUT->pix_icon('icon', '', 'mod_page', array('class' => 'iconsmall'));
                } else {
                    // CMDLTWO-603: Exclude activity modules which don't have an index.php (such as Kaltura Video Assignment).
                    if (!file_exists($CFG->dirroot . "/mod/{$modname}/index.php")) {
                        continue;
                    }

                    $url = new \moodle_url('/mod/' . $modname . '/index.php', array('id' => $course->id));
                    $icon = $OUTPUT->pix_icon('icon', '', $modname, array('class' => 'iconsmall'));
                }

                $activities[] = [
                    'url' => $url,
                    'icon' => $icon,
                    'text' => $modfullname,
                    // 'attrs' => $attrs,
                    'class' => $class,
                    'liattrs' => $liattrs,
                    'editurl' => $editurl, 
                    'editicon' => $editicon, 
                    'editattrs' => $editattrs
                ];
            }            
        }

        return $activities;
    }

    /**
     * 
     *
     * @param stdClass $course
     * @param course_modinfo $modinfo
     * @return
     */
    public function exttools_modules_display($course, $modinfo) {
        global $CFG, $DB, $OUTPUT;

        require_once($CFG->dirroot. '/mod/lti/locallib.php');

        $content = '';
        $modfullnames = array();
        $cms = $modinfo->get_instances_of('lti');

        foreach($cms as $cm) {
            // Exclude activities which are not visible or have no link (=label)
            if (!$cm->uservisible or !$cm->has_view()) {
                continue;
            }            

            $instance = $DB->get_record('lti', array('id' => $cm->instance));
            $type = lti_get_type($instance->typeid);

            if(is_https()) {
                $iconfield = 'secureicon';
            } else {
                $iconfield = 'icon';
            }

            if(!$type) {
                $type = new stdClass();
                $type->id = 0;
                $type->$iconfield = null;
            }

            $nametype = $cm->modname . 'type' . $instance->typeid;

            if (array_key_exists($nametype, $modfullnames)) {
                continue;
            }

            if($type->id) {
                $modfullnames[$nametype] = [
                    'modname' => $type->name,
                    'modfullname' => $type->name,
                    'type' => $type,
                    'icon' => $type->$iconfield
                ];
            } else {
                $modfullnames[$nametype] = [
                    'modname' => $cm->modplural,
                    'modfullname' => $cm->modplural,
                    'type' => $type,
                    'icon' => $type->$iconfield
                ];
            }            
        }

        if (!count($modfullnames)) {
            return '';
        }

        foreach ($modfullnames as $nametype => $modnames) {
            if ((isset($this->culconfig['show' . $nametype]) && $this->culconfig['show' . $nametype] == 2)
                || $this->userisediting) 
            {
                $liattrs = array();
                $liattrs['title']  = get_string('view-mod', 'format_culcourse', strtolower($modnames['modfullname']));
                $class = '';
                $editurl = '';
                $editicon = '';
                $editattrs = '';
                
                if ($this->userisediting) {
                    list($editurl, $editicon, $editattrs) = format_culcourse_get_edit_link(
                        $course->id, 
                        'show' . $nametype, 
                        $this->culconfig['show' . $nametype]
                        );
                }

                if ($this->userisediting && ($this->culconfig['show' . $nametype] != 2)) {
                        $class = 'linkhidden';                
                }

                $url = new \moodle_url('/course/format/culcourse/ltiindex.php', array('id' => $course->id, 'typeid' => $modnames['type']->id));

                if (!$modnames['type']->icon) {
                    $icon = $OUTPUT->pix_icon('icon', '', 'mod_lti', array('class' => 'iconsmall'));
                } else {
                    $icon = \html_writer::empty_tag('img', array('src' => $modnames['type']->icon, 'alt' => $modnames['type']->name, 'class' => 'iconsmall'));
                }

                $activities[] = [
                    'url' => $url,
                    'icon' => $icon,
                    'text' => $modnames['modfullname'],
                    'class' => $class,
                    'liattrs' => $liattrs,
                    'editurl' => $editurl, 
                    'editicon' => $editicon, 
                    'editattrs' => $editattrs
                ];
            }
        }

        return $activities;
    }
}
