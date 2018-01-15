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
 * @subpackage culcourse
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

namespace format_cul\output\dashboard;

defined('MOODLE_INTERNAL') || die();
// require_once($CFG->dirroot . '/course/format/renderer.php');
// require_once($CFG->dirroot . '/course/format/culcourse/lib.php');
// require_once($CFG->dirroot . '/course/format/culcourse/togglelib.php');
require_once($CFG->dirroot . '/course/format/culcourse/dashboard/locallib.php');

class renderer extends \plugin_renderer_base {

    private $courseformat = null; // Our course format object as defined in lib.php;
    private $config = null; // Admin settings for the format.
    private $tcsettings; // Settings for the format - array.
    private $userpreference; // User toggle state preference - string.
    private $defaultuserpreference; // Default user preference when none set - bool - true all open, false all closed.
    private $togglelib;
    private $isoldtogglepreference = false;
    private $userisediting = false;

    /**
     * Constructor method, calls the parent constructor - MDL-21097
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        global $PAGE;

        parent::__construct($page, $target);
        $this->config = get_config('format_cul');
        // $this->togglelib = new culcourse_togglelib;
        // $this->courseformat = course_get_format($page->course); // Needed for collapsed topics settings retrieval.

        /* Since format_cul_renderer::section_edit_controls() only displays the 'Set current section' control when editing
           mode is on we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any
           other managing capability. */
        $page->set_other_editing_capability('moodle/course:setcurrentsection');      
        $this->userisediting = $page->user_is_editing();

        if ($this->userisediting) {
            $adminurl = new moodle_url('/course/format/culcourse/quicklink_edit_ajax.php');
            $arguments = array('adminurl' => $adminurl->out());
            $page->requires->js_call_amd('format_cul/quicklinks', 'initialize', array($arguments));
        }
    }


    public function set_user_preference($preference) {
        $this->userpreference = $preference;
    }

    public function set_default_user_preference($defaultpreference) {
        $this->defaultuserpreference = $defaultpreference;
    }

 
    /**
     * format_cul_renderer::quicklink_display()
     *
     * @param stdClass $course
     * @return string
     *
     * TODO: Create a master mapping/dispatch table to make it easy to add additional
     *       Quick Links in future. This could automatically be picked-up by the block
     *       config form. For now, it's over-engineering.
     */
    public function quicklink_display($course) {
        global $CFG, $DB;

        $content = '';
        $output  = '';
        $linkitems = array();

        $coursecontext = context_course::instance($course->id);

        // Reading list
        if ($this->tcsettings['showreadinglists'] == 2 || $this->userisediting) {
            $rlattrs  = array ();
            $liattrs = array();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_cul_get_edit_link(
                    $course->id, 
                    'readinglists', 
                    $this->tcsettings['showreadinglists']
                    );
            }

            $urldata = format_cul_get_reading_list_url_data($course);

            if ($this->userisediting&& ($this->tcsettings['showreadinglists'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$urldata) {
                // Not installed or not configured
                $rllnktxt = get_string('aspirelists', 'format_cul');
                $rlattrs['title'] = get_string('not-installed-readinglist', 'format_cul');
                $rlattrs['class'] = 'nolink';
                $rlurl = 'javascript:void(0);';
                $liattrs['class'] = 'wide';
            } else {
                $rllnktxt = get_string('aspirelists', 'format_cul');

                if (OK == $urldata['status']) {
                    $listtype = $urldata['listtype'];
                    $rlurl    = $urldata['url'];

                    if ('module' == $listtype) {
                        $rlattrs['title'] = get_string('view-readinglist-module', 'format_cul');
                        $rlattrs['target'] = '_blank';
                    } else if ('module-year' == $listtype) {
                        $rlattrs['title'] = get_string('view-readinglist-module-year', 'format_cul');
                        $rlattrs['target'] = '_blank';
                    }
                } else if (NODATA == $urldata['status']) {
                    $rlattrs['title'] = get_string('no-readinglist', 'format_cul');
                    $rlattrs['class'] = 'nolink';
                    $rlurl = 'javascript:void(0);';
                } else if (ERROR == $urldata['status']) {
                    $rlattrs['title'] = get_string('error-readinglist', 'format_cul');
                    $rlattrs['class'] = 'nolink';
                    $rlurl = 'javascript:void(0);';
                }
            }

            $rlicon = html_writer::tag('i', '', array('class' => 'fa fa-bookmark'));
            $link = html_writer::link($rlurl, $rlicon . $rllnktxt, $rlattrs);
            $link .= $editlink;
            $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
            $linkitems[] .= html_writer::tag('li', $span, $liattrs);
        }

        // Echo 360
        if (($this->tcsettings['showecho'] == 2) || $this->userisediting) {
            $eattrs  = array ();
            $liattrs = array();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_cul_get_edit_link(
                    $course->id, 
                    'echo', 
                    $this->tcsettings['showecho']
                    );
            }

            $urldata = format_cul_get_echo_data($course);

            if ($this->userisediting && ($this->tcsettings['showecho'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$urldata) {
                // Not installed or not configured.
                $elnktxt = get_string('echocenter', 'format_cul');
                $eattrs['title'] = get_string('not-installed-echo', 'format_cul');
                $eattrs['class'] = 'nolink';
                $eurl = 'javascript:void(0);';
            } else {
                $elnktxt = get_string('echocenter', 'format_cul');

                if (OK == $urldata['status']) {
                    $eattrs['title'] = get_string('view-echo-module', 'format_cul');
                    $eattrs['target'] = '_blank';
                    $eurl = $urldata['url'];
                } else if (NODATA == $urldata['status']) {
                    $eattrs['title'] = get_string('no-echo', 'format_cul');
                    $eattrs['class'] = 'nolink';
                    $eurl = 'javascript:void(0);';
                } else if (ERROR == $urldata['status']) {
                    $eattrs['title'] = get_string('error-echo', 'format_cul');
                    $eattrs['class'] = 'nolink';
                    $eurl = 'javascript:void(0);';
                }
            }

            $eicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-video-camera'));
            $link = html_writer::link($eurl, $eicon . $elnktxt, $eattrs);
            $link .= $editlink;
            $span = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
            $linkitems[] .= html_writer::tag('li', $span, $liattrs);
        }

        // Timetable link
        if ($this->tcsettings['showtimetable'] == 2 || $this->userisediting) {
            $ttattrs = array ();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_cul_get_edit_link(
                    $course->id, 
                    'timetable', 
                    $this->tcsettings['showtimetable']
                    );
            }

            $ttdata = format_cul_get_timetable_url($course);

            if ($this->userisediting&& ($this->tcsettings['showtimetable'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$ttdata) {
                // Not installed or not configured.
                $ttattrs['title'] = get_string('not-installed-timetable', 'format_cul');
                $ttattrs['class'] = 'nolink';
                $tturl = 'javascript:void(0);';
            } else {
                if (OK == $ttdata['status']) {
                    $ttattrs['title']  = get_string('view-timetable', 'format_cul');
                    $ttattrs['target'] = '_blank';
                    $tturl = $ttdata['url'];
                } else if (NODATA == $ttdata['status']) {
                    $ttattrs['title']  = get_string('no-timetable', 'format_cul');
                    $ttattrs['class'] = 'nolink';
                    $ttattrs['target'] = '_blank';
                    $tturl = $ttdata['url'];
                } else if (ERROR == $ttdata['status']) {
                    $ttattrs['title'] = get_string('error-timetable', 'format_cul');
                    $ttattrs['class'] = 'nolink';
                    $tturl = 'javascript:void(0);';
                }
            }

            $tticon = $this->output->pix_icon('i/scheduled', '', 'moodle', array('class'=>'iconsmall'));
            $tticon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-clock-o'));
            $ttlnktx = get_string('timetable', 'format_cul');
            $link = html_writer::link($tturl, $tticon . $ttlnktx, $ttattrs);
            $link .= $editlink;
            $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Grades
        if ($this->tcsettings['showgraderreport'] == 2 || $this->userisediting) {
            $gattrs  = array ();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_cul_get_edit_link(
                    $course->id, 
                    'graderreport', 
                    $this->tcsettings['showgraderreport']
                    );
            }

            $glnktxt = get_string('grades', 'grades');

            if ($this->userisediting&& ($this->tcsettings['showgraderreport'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (has_capability('gradereport/grader:view', $coursecontext)) { // Teacher, ...
                $glnktxt = get_string('graderreport', 'grades');
                $gattrs['title'] = get_string('view-graderreport', 'format_cul');
                $gurl = new moodle_url('/grade/report/grader/index.php', array('id' => $course->id));
            } else if (has_capability('moodle/grade:view', $coursecontext)) { // Student
                $gattrs['title'] = get_string('viewgrades', 'grades');
                $gurl = new moodle_url('/grade/report/culuser/index.php', array('id' => $course->id));
            } else  {
                $gattrs['title'] = get_string('no-view-grades', 'format_cul');
                $gattrs['class'] = 'nolink';
                $gurl = 'javascript:void(0);';
            }

            $gicon = $this->output->pix_icon('i/grades', '', 'moodle', array('class'=>'iconsmall'));
            $gicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-mortar-board'));
            $link = html_writer::link($gurl, $gicon . $glnktxt, $gattrs);
            $link .= $editlink;
            $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Calendar
        if ($this->tcsettings['showcalendar'] == 2 || $this->userisediting) {
            $cattrs  = array ();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_cul_get_edit_link(
                    $course->id, 
                    'calendar', 
                    $this->tcsettings['showcalendar']
                    );
            }

            $clnktxt = get_string('calendar', 'calendar');

            if ($this->userisediting&& ($this->tcsettings['showcalendar'] != 2)) {
                    $class = 'linkhidden';                
            }

            $cattrs['title'] = get_string('view-calendar', 'format_cul');
            $curl  = new moodle_url('/calendar/view.php', array('view' => 'month', 'course' => $course->id));
            $cicon = $this->output->pix_icon('i/calendar', '', 'moodle', array('class'=>'iconsmall'));
            $cicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-calendar')); // table, calculator, list-alt
            $link = html_writer::link($curl, $cicon . $clnktxt, $cattrs);
            $link .= $editlink;
            $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Photoboards
        foreach (role_get_names($coursecontext, ROLENAME_ALIAS) as $role) {
            $options[$role->id] = $role->localname;
        }
        // Student Photoboard
        if ($this->tcsettings['showstudents'] == 2 || $this->userisediting) {
            $studentrole = $DB->get_record('role', array('shortname'=>'student'));

            if ($studentrole){
                $phattrs  = array ();
                $class = '';
                $editlink = '';

                if ($this->userisediting) {
                    $editlink = format_cul_get_edit_link(
                        $course->id, 
                        'students', 
                        $this->tcsettings['showstudents']
                        );
                }

                $alias = $options[$studentrole->id];
                $phlnktxt = $alias . 's';

                if ($this->userisediting && ($this->tcsettings['showstudents'] != 2)) {
                        $class = 'linkhidden';                
                }

                if (count_role_users($studentrole->id, $coursecontext, false)){
                    $phattrs['title']  = get_string('view-student-photoboard', 'format_cul', $alias);
                    $phattrs['target'] = '';
                    $phurl = format_cul_get_photoboard_url($course, $studentrole->id);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-student-photoboard', 'format_cul', $alias);
                    $phurl = 'javascript:void(0);';
                }

                $phicon = $this->output->pix_icon('i/users', '', 'moodle', array('class'=>'iconsmall'));
                $phicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-users'));
                $link = html_writer::link($phurl, $phicon . $phlnktxt, $phattrs);
                $link .= $editlink;
                $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
                $linkitems[] .= html_writer::tag('li', $span);
            }
        }

        // Lecturer Photoboard
        if ($this->tcsettings['showlecturers'] == 2 || $this->userisediting) {
            $lecturerrole = $DB->get_record('role', array('shortname'=>'lecturer'));

            if ($lecturerrole){
                $phattrs  = array ();
                $class = '';
                $editlink = '';

                if ($this->userisediting) {
                    $editlink = format_cul_get_edit_link(
                        $course->id, 
                        'lecturers', 
                        $this->tcsettings['showlecturers']
                        );
                }

                $alias = $options[$lecturerrole->id];
                $phlnktxt = $alias . 's';

                if ($this->userisediting && ($this->tcsettings['showlecturers'] != 2)) {
                        $class = 'linkhidden';                
                }

                if (count_role_users($lecturerrole->id, $coursecontext, false)){
                    $phattrs['title']  = get_string('view-lecturer-photoboard', 'format_cul', $alias);
                    $phattrs['target'] = '';
                    $phurl = format_cul_get_photoboard_url($course, $lecturerrole->id);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-lecturer-photoboard', 'format_cul', $alias);
                    $phurl = 'javascript:void(0);';
                }

                $phicon = $this->output->pix_icon('i/users', '', 'moodle', array('class'=>'iconsmall'));
                $phicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-users'));
                $link = html_writer::link($phurl, $phicon . $phlnktxt, $phattrs);
                $link .= $editlink;
                $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
                $linkitems[] .= html_writer::tag('li', $span);
            }
        }

        // Course Officer Photoboard
        if ($this->tcsettings['showcourseofficers'] == 2 || $this->userisediting) {
            $courseofficerrole = $DB->get_record('role', array('shortname'=>'courseofficer'));

            if ($courseofficerrole){
                $phattrs  = array ();
                $class = '';
                $editlink = '';

                if ($this->userisediting) {
                    $editlink = format_cul_get_edit_link(
                        $course->id, 
                        'courseofficers', 
                        $this->tcsettings['showcourseofficers']
                        );
                }

                $alias = $options[$courseofficerrole->id];
                $phlnktxt = $alias . 's';

                if ($this->userisediting && ($this->tcsettings['showcourseofficers'] != 2)) {
                        $class = 'linkhidden';                
                }

                if (count_role_users($courseofficerrole->id, $coursecontext, false)){
                    $phattrs['title']  = get_string('view-courseofficer-photoboard', 'format_cul', $alias);
                    $phattrs['target'] = '';
                    $phurl = format_cul_get_photoboard_url($course, $courseofficerrole->id);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-courseofficer-photoboard', 'format_cul', $alias);
                    $phurl = 'javascript:void(0);';
                }

                $phicon = $this->output->pix_icon('i/users', '', 'moodle', array('class'=>'iconsmall'));
                $phicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-users'));
                $link = html_writer::link($phurl, $phicon . $phlnktxt, $phattrs);
                $link .= $editlink;
                $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
                $linkitems[] .= html_writer::tag('li', $span);
            }           
        }

        // Media gallery
        if ($this->tcsettings['showmedia'] == 2 || $this->userisediting) {
            $kattrs  = array ();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_cul_get_edit_link(
                    $course->id, 
                    'media', 
                    $this->tcsettings['showmedia']
                    );
            }

            $klnktxt = get_string('media', 'format_cul');

            if ($this->userisediting&& ($this->tcsettings['showmedia'] != 2)) {
                    $class = 'linkhidden';                
            }

            $kattrs['title'] = get_string('view-media', 'format_cul');
            $kurl  = new moodle_url('/local/kalturamediagallery/index.php', array('courseid' => $course->id));
            $kicon = $this->output->pix_icon('f/video-72', '', 'moodle', array('class'=>'iconsmall'));
            $kicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-file-video-o'));
            $link = html_writer::link($kurl, $kicon . $klnktxt, $kattrs);
            $link .= $editlink;
            $span     = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Build the dash-panel if we have any links.
        if (!count($linkitems)) {
            return '';
        }

        $content .= html_writer::start_tag('ul', array('class'=>'links clearfix'));

        foreach ($linkitems as $linkitem) {
            $content .= $linkitem;
        }

        $content .= html_writer::end_tag('ul');

        // Wrap it all up in a dash-panel.
        $title = get_string('quicklinks', 'format_cul');
        $panelheader = html_writer::tag('div', $title, array('class'=>'panel-header ui-corner-all'));
        $navcontent = html_writer::tag('nav', $content, array('class'=>'linkscontainer'));
        $output .= html_writer::tag('div', $panelheader . $navcontent, array('class'=>'dash-panel clearfix'));

        return $output;
    }

    /**
     * format_cul_renderer::activity_modules_display()
     *
     * @param stdClass $course
     * @return
     */
    public function activity_modules_display($course) {
        global $CFG;

        $content = '';
        $output  = '';        

        require_once($CFG->dirroot . '/course/lib.php');

        $modinfo = get_fast_modinfo($course);

        $modfullnames = array();
        $archetypes   = array();

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

        core_collator::asort($modfullnames);

        $content .= html_writer::start_tag('ul', array('class' => 'links clearfix'));

        foreach ($modfullnames as $modname => $modfullname) {
            if($modname == 'lti') {
                $content .= $this->exttools_modules_display($course, $modinfo);
                continue;
            }

            if ((isset($this->tcsettings['show' . $modname]) && $this->tcsettings['show' . $modname] == 2)
                || $this->userisediting) 
            {
                $liattrs = array();
                $liattrs['title']  = get_string('view-mod', 'format_cul', strtolower($modfullname));
                $class = '';
                $editlink  = '';
                
                if ($this->userisediting) {
                    $editlink = format_cul_get_edit_link(
                        $course->id, 
                        $modname, 
                        $this->tcsettings['show' . $modname]
                        );
                }

                if ($this->userisediting && ($this->tcsettings['show' . $modname] != 2)) {
                        $class = 'linkhidden';                
                }

                if ($modname === 'resources') {
                    $url = new moodle_url('/course/resources.php', array('id' => $course->id));
                    $icon = $this->output->pix_icon('icon', '', 'mod_page', array('class' => 'iconsmall'));
                    $link =  html_writer::link($url, $icon . $modfullname);
                    $link .= $editlink;
                    $span = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
                    $content .= html_writer::tag('li', $span, $liattrs);
                } else {
                    // CMDLTWO-603: Exclude activity modules which don't have an index.php (such as Kaltura Video Assignment).
                    if (!file_exists($CFG->dirroot . "/mod/{$modname}/index.php")) {
                        continue;
                    }

                    $url = new moodle_url('/mod/' . $modname . '/index.php', array('id' => $course->id));
                    $icon = $this->output->pix_icon('icon', '', $modname, array('class' => 'iconsmall'));
                    $link =  html_writer::link($url, $icon . $modfullname);
                    $link .= $editlink;
                    $span = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
                    $content .= html_writer::tag('li', $span, $liattrs);
                }
            }
        }

        $content .= html_writer::end_tag('ul');

        $title = get_string('activities', 'format_cul');
        $panelheader = html_writer::tag('div', $title, array('class'=>'panel-header ui-corner-all'));
        $navcontent  = html_writer::tag('nav', $content, array('class'=>'linkscontainer'));
        $output .= html_writer::tag('div', $panelheader . $navcontent, array('class'=>'dash-panel clearfix'));

        return $output;
    }

    /**
     * format_cul_renderer::exttools_modules_display()
     *
     * @param stdClass $course
     * @param course_modinfo $modinfo
     * @return
     */
    public function exttools_modules_display($course, $modinfo) {
        global $CFG, $DB;

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
                    'modfullname' => $type->name . ' ' . $cm->modplural,
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
            if ((isset($this->tcsettings['show' . $nametype]) && $this->tcsettings['show' . $nametype] == 2)
                || $this->userisediting) 
            {
                $liattrs = array();
                $liattrs['title']  = get_string('view-mod', 'format_cul', strtolower($modnames['modfullname']));
                $class = '';
                $editlink  = '';
                
                if ($this->userisediting) {
                    $editlink = format_cul_get_edit_link(
                        $course->id, 
                        $nametype, 
                        $this->tcsettings['show' . $nametype]
                        );
                }

                if ($this->userisediting && ($this->tcsettings['show' . $nametype] != 2)) {
                        $class = 'linkhidden';                
                }

                $url = new moodle_url('/course/format/culcourse/ltiindex.php', array('id' => $course->id, 'typeid' => $modnames['type']->id));

                if (!$modnames['type']->icon) {
                    $icon = $this->output->pix_icon('icon', '', 'mod_lti', array('class' => 'icon'));
                } else {
                    $icon = html_writer::empty_tag('img', array('src' => $modnames['type']->icon, 'alt' => $modnames['type']->name, 'class' => 'icon'));
                }
                
                $link =  html_writer::link($url, $icon . $modnames['modname']);
                $link .= $editlink;
                $span = html_writer::tag('span', $link, array('class' => 'dash-link ' . $class));
                $content .= html_writer::tag('li', $span, $liattrs);
            }
        }

        return $content;
    }
}
