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
 * culcourse_dashboard block renderer
 *
 * @package    block_culcourse_dashboard
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>, Tim Gagen <tim.gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * culcourse_dashboard block renderer
 *
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_culcourse_dashboard_renderer extends plugin_renderer_base {

    /**
     * block_culcourse_dashboard_renderer::summary_display()
     *
     * @param  stdClass $course
     * @return string $output string for summary area.
     */
    public function summary_display($course) {

        $output = '';
        $coursesummary = trim($course->summary);

        // If the course summary isn't 'empty', then display it.
        if (!preg_match('%\A(?:<p(?:\s+id="\w*"\s*)?>)*((?:\s*)|(?:\s*&nbsp;\s*)*)(?:</p>)*\z%sim', $coursesummary)) {
            $summarytitle = get_string('modulesummary', 'block_culcourse_dashboard');
            $header  = html_writer::tag('div', $summarytitle, array('class'=>'panel-header ui-corner-all'));
            $content = html_writer::tag('div', $coursesummary, array('class'=>'panel-content'));
            $output .= html_writer::tag('div', $header . $content, array('class'=>'dash-panel module-summary'));
        }

        return $output;
    }

    /**
     * block_culcourse_dashboard_renderer::quicklink_display()
     *
     * @param stdClass $course
     * @return string
     *
     * TODO: Create a master mapping/dispatch table to make it easy to add additional
     *       Quick Links in future. This could automatically be picked-up by the block
     *       config form. For now, it's over-engineering.
     */
    public function quicklink_display($course, $config, $blockid) {
        global $CFG, $DB;

        $content = '';
        $output  = '';
        $linkitems = array();

        $coursecontext = context_course::instance($course->id);

        // Reading list
        if (empty($config->hide_readinglists) || $config->hide_readinglists == 2) {
            $rlattrs  = array ();
            $liattrs = array();
            $urldata = block_culcourse_dashboard_get_reading_list_url_data($course);

            if (!$urldata) {
                // Not installed or not configured
                $rllnktxt = get_string('aspirelists', 'block_culcourse_dashboard');
                $rlattrs['title'] = get_string('not-installed-readinglist', 'block_culcourse_dashboard');
                $rlattrs['class'] = 'nolink';
                $rlurl = 'javascript:void(0);';
                $liattrs['class'] = 'wide';
            } else {
                $rllnktxt = get_string('aspirelists', 'block_culcourse_dashboard');

                if (OK == $urldata['status']) {
                    $listtype = $urldata['listtype'];
                    $rlurl    = $urldata['url'];

                    if ('module' == $listtype) {
                        $rlattrs['title'] = get_string('view-readinglist-module', 'block_culcourse_dashboard');
                        $rlattrs['target'] = '_blank';
                    } else if ('module-year' == $listtype) {
                        $rlattrs['title'] = get_string('view-readinglist-module-year', 'block_culcourse_dashboard');
                        $rlattrs['target'] = '_blank';
                    }
                } else if (NODATA == $urldata['status']) {
                    $rlattrs['title'] = get_string('no-readinglist', 'block_culcourse_dashboard');
                    $rlattrs['class'] = 'nolink';
                    $rlurl = 'javascript:void(0);';
                } else if (ERROR == $urldata['status']) {
                    $rlattrs['title'] = get_string('error-readinglist', 'block_culcourse_dashboard');
                    $rlattrs['class'] = 'nolink';
                    $rlurl = 'javascript:void(0);';
                }
            }

            $rlicon = html_writer::tag('i', '', array('class' => 'fa fa-bookmark'));
            $link = html_writer::link($rlurl, $rlicon . $rllnktxt, $rlattrs);
            $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
            $linkitems[] .= html_writer::tag('li', $span, $liattrs);
        }

        // Timetable link
        if (empty($config->hide_timetable) || $config->hide_timetable == 2) {

            $ttattrs = array ();
            $ttdata = block_culcourse_dashboard_get_timetable_url($course);

            if (!$ttdata) {
                // Not installed or not configured.
                $ttattrs['title'] = get_string('not-installed-timetable', 'block_culcourse_dashboard');
                $ttattrs['class'] = 'nolink';
                $ttattrs['title']  = get_string('no-timetable', 'block_culcourse_dashboard');
                $tturl = 'javascript:void(0);';
            } else {
                if (OK == $ttdata['status']) {
                    $ttattrs['title']  = get_string('view-timetable', 'block_culcourse_dashboard');
                    $ttattrs['target'] = '_blank';
                    $tturl = $ttdata['url'];
                } else if (NODATA == $ttdata['status']) {
                    $ttattrs['title']  = get_string('no-timetable', 'block_culcourse_dashboard');
                    $ttattrs['target'] = '_blank';
                    $tturl = $ttdata['url'];
                } else if (ERROR == $ttdata['status']) {
                    $ttattrs['title'] = get_string('error-timetable', 'block_culcourse_dashboard');
                    $ttattrs['class'] = 'nolink';
                    $tturl = 'javascript:void(0);';
                }
            }

            $tticon = $this->output->pix_icon('i/scheduled', '', 'moodle', array('class'=>'iconsmall'));
            $tticon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-clock-o'));
            $ttlnktx = get_string('timetable', 'block_culcourse_dashboard');
            $link = html_writer::link($tturl, $tticon . $ttlnktx, $ttattrs);
            $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Grades
        if (empty($config->hide_graderreport) || $config->hide_graderreport == 2) {
            $gattrs  = array ();
            $glnktxt = get_string('grades', 'grades');

            if (has_capability('gradereport/grader:view', $coursecontext)) { // Teacher, ...
                $glnktxt = get_string('graderreport', 'grades');
                $gatttrs['title'] = get_string('view-graderreport', 'block_culcourse_dashboard');
                $gurl = new moodle_url('/grade/report/grader/index.php', array('id' => $course->id));
            } else if (has_capability('moodle/grade:view', $coursecontext)) { // Student
                $gattrs['title'] = get_string('viewgrades', 'grades');
                $gurl = new moodle_url('/grade/report/culuser/index.php', array('id' => $course->id));
            } else  {
                $gattrs['title'] = get_string('no-view-grades', 'block_culcourse_dashboard');
                $gattrs['class'] = 'nolink';
                $gurl = 'javascript:void(0);';
            }

            $gicon = $this->output->pix_icon('i/grades', '', 'moodle', array('class'=>'iconsmall'));
            $gicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-mortar-board'));
            $link = html_writer::link($gurl, $gicon . $glnktxt, $gattrs);
            $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Calendar
        if (empty($config->hide_calendar) || $config->hide_calendar == 2) {
            $cattrs  = array ();
            $clnktxt = get_string('calendar', 'calendar');

            $cattrs['title'] = get_string('view-calendar', 'block_culcourse_dashboard');
            $curl  = new moodle_url('/calendar/view.php', array('view' => 'month', 'course' => $course->id));
            $cicon = $this->output->pix_icon('i/calendar', '', 'moodle', array('class'=>'iconsmall'));
            $cicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-calendar')); // table, calculator, list-alt
            $link = html_writer::link($curl, $cicon . $clnktxt, $cattrs);
            $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
            $linkitems[] .= html_writer::tag('li', $span);
        }

        // Photoboard
        foreach (role_get_names($coursecontext, ROLENAME_ALIAS) as $role) {
            $options[$role->id] = $role->localname;
        }

        if (empty($config->hide_photoboard) || $config->hide_photoboard == 2) {
            $studentrole = $DB->get_record('role', array('shortname'=>'student'));
            $lecturerrole = $DB->get_record('role', array('shortname'=>'lecturer'));
            $courseofficerrole = $DB->get_record('role', array('shortname'=>'courseofficer'));

            if ($studentrole){
                $phattrs  = array ();
                $alias = $options[$studentrole->id];
                $phlnktxt = $alias . 's';

                if (has_capability('block/culcourse_dashboard:viewphotoboard', $coursecontext)) {
                    $phattrs['title']  = get_string('view-student-photoboard', 'block_culcourse_dashboard', $alias);
                    $phattrs['target'] = '';
                    $phurl = block_culcourse_dashboard_get_photoboard_url($course, $studentrole->id, $blockid);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-photoboard', 'block_culcourse_dashboard', $alias);
                    $phurl = 'javascript:void(0);';
                }

                $phicon = $this->output->pix_icon('i/users', '', 'moodle', array('class'=>'iconsmall'));
                $phicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-users'));
                $link = html_writer::link($phurl, $phicon . $phlnktxt, $phattrs);
                $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
                $linkitems[] .= html_writer::tag('li', $span);
            }

            if ($lecturerrole){
                $phattrs  = array ();
                $alias = $options[$lecturerrole->id];
                $phlnktxt = $alias . 's';

                if (has_capability('block/culcourse_dashboard:viewphotoboard', $coursecontext)) {
                    $phattrs['title']  = get_string('view-lecturer-photoboard', 'block_culcourse_dashboard', $alias);
                    $phattrs['target'] = '';
                    $phurl = block_culcourse_dashboard_get_photoboard_url($course, $lecturerrole->id, $blockid);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-photoboard', 'block_culcourse_dashboard', $alias);
                    $phurl = 'javascript:void(0);';
                }

                $phicon = $this->output->pix_icon('i/users', '', 'moodle', array('class'=>'iconsmall'));
                $phicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-users'));
                $link = html_writer::link($phurl, $phicon . $phlnktxt, $phattrs);
                $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
                $linkitems[] .= html_writer::tag('li', $span);
            }

            if ($courseofficerrole){
                $phattrs  = array ();
                $alias = $options[$courseofficerrole->id];
                $phlnktxt = $alias . 's';

                if (has_capability('block/culcourse_dashboard:viewphotoboard', $coursecontext)) {
                    $phattrs['title']  = get_string('view-courseofficer-photoboard', 'block_culcourse_dashboard', $alias);
                    $phattrs['target'] = '';
                    $phurl = block_culcourse_dashboard_get_photoboard_url($course, $courseofficerrole->id, $blockid);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-photoboard', 'block_culcourse_dashboard', $alias);
                    $phurl = 'javascript:void(0);';
                }

                $phicon = $this->output->pix_icon('i/users', '', 'moodle', array('class'=>'iconsmall'));
                $phicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-users'));
                $link = html_writer::link($phurl, $phicon . $phlnktxt, $phattrs);
                $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
                $linkitems[] .= html_writer::tag('li', $span);
            }            
            // Media gallery
            if (empty($config->hide_media) || $config->hide_media == 2) {
                $kattrs  = array ();
                $klnktxt = get_string('media', 'block_culcourse_dashboard');

                $kattrs['title'] = get_string('view-media', 'block_culcourse_dashboard');
                $kurl  = new moodle_url('/local/kalturamediagallery/index.php', array('courseid' => $course->id));
                $kicon = $this->output->pix_icon('f/video-72', '', 'moodle', array('class'=>'iconsmall'));
                $kicon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-file-video-o'));
                $link = html_writer::link($kurl, $kicon . $klnktxt, $kattrs);
                $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
                $linkitems[] .= html_writer::tag('li', $span);
            }

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
        $title = get_string('quicklinks', 'block_culcourse_dashboard');
        $panelheader = html_writer::tag('div', $title, array('class'=>'panel-header ui-corner-all'));
        $navcontent  = html_writer::tag('nav', $content, array('class'=>'linkscontainer'));
        $output     .= html_writer::tag('div', $panelheader . $navcontent, array('class'=>'dash-panel clearfix'));

        return $output;
    }


    /**
     * block_culcourse_dashboard_renderer::activity_modules_display()
     *
     * @param stdClass $course
     * @return
     */
    public function activity_modules_display($course, $config) {
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

        $content .= html_writer::start_tag('ul', array('class'=>'links clearfix'));

        foreach ($modfullnames as $modname => $modfullname) {
            if (empty($config->{'hide_' . $modname}) || $config->{'hide_' . $modname} == 2) {
                $liattrs = array();

                if ($modname === 'resources') {
                    $url      = new moodle_url('/course/resources.php', array('id' => $course->id));
                    $icon     = $this->output->pix_icon('icon', '', 'mod_page', array('class'=>'iconsmall'));
                    $link     =  html_writer::link($url, $icon . $modfullname);
                    $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
                    $content  .= html_writer::tag('li', $span, $liattrs);
                } else {
                    // CMDLTWO-603: Exclude activity modules which don't have an index.php (such as Kaltura Video Assignment).
                    if (!file_exists($CFG->dirroot . "/mod/{$modname}/index.php")) {
                        continue;
                    }
                    $url      = new moodle_url('/mod/' . $modname . '/index.php', array('id' => $course->id));
                    $icon     = $this->output->pix_icon('icon', '', $modname, array('class'=>'iconsmall'));
                    $link     =  html_writer::link($url, $icon . $modfullname);
                    $span     = html_writer::tag('span', $link, array('class'=>'dash-link'));
                    $content  .= html_writer::tag('li', $span, $liattrs);
                }
            }
        }

        $content .= html_writer::end_tag('ul');

        $title = get_string('activities', 'block_culcourse_dashboard');
        $panelheader = html_writer::tag('div', $title, array('class'=>'panel-header ui-corner-all'));
        $navcontent  = html_writer::tag('nav', $content, array('class'=>'linkscontainer'));
        $output     .= html_writer::tag('div', $panelheader . $navcontent, array('class'=>'dash-panel clearfix'));

        return $output;
    }
}

