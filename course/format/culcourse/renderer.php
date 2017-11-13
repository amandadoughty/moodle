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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/course/format/renderer.php');
require_once($CFG->dirroot . '/course/format/culcourse/lib.php');
require_once($CFG->dirroot . '/course/format/culcourse/togglelib.php');
require_once($CFG->dirroot . '/course/format/culcourse/locallib.php');

class format_culcourse_renderer extends format_section_renderer_base {

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
        $this->config = get_config('format_culcourse');
        $this->togglelib = new culcourse_togglelib;
        $this->courseformat = course_get_format($page->course); // Needed for collapsed topics settings retrieval.

        /* Since format_culcourse_renderer::section_edit_controls() only displays the 'Set current section' control when editing
           mode is on we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any
           other managing capability. */
        $page->set_other_editing_capability('moodle/course:setcurrentsection');      
        $this->userisediting = $page->user_is_editing();

        if ($this->userisediting) {
            $adminurl = new moodle_url('/course/format/culcourse/quicklink_edit_ajax.php');
            $arguments = array('adminurl' => $adminurl->out());
            $page->requires->js_call_amd('format_culcourse/quicklinks', 'initialize', array($arguments));
        }
    }

    /**
     * Generate the starting container html for a list of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        global $COURSE;

        $o = '';

        // Course summary.
        if ($this->tcsettings['showcoursesummary'] == 2) {
            $o .= $this->summary_display($COURSE);
        }

        // Quick Links.
        $o .= $this->quicklink_display($COURSE);

        // Activity module links.
        $o .= $this->activity_modules_display($COURSE);

        $o .= html_writer::start_tag('ul', array('class' => 'culcourse'));

        return $o;
    }

    /**
     * Generate the starting container html for a list of sections when showing a toggle.
     * @return string HTML to output.
     */
    protected function start_toggle_section_list() {
        $classes = 'culcourse topics';
        $classes .= ' cullayout';

        $attributes = array('class' => $classes);
        //$attributes['style'] = $style;
        return html_writer::start_tag('ul', $attributes);
    }

    /**
     * Generate the closing container html for a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('sectionname', 'format_culcourse');
    }


    /**
     * Generate the edit control items of a section
     *
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return array of edit control items
     */
    protected function section_edit_control_items($course, $section, $onsectionpage = false) {
        global $PAGE;

        if (!$PAGE->user_is_editing()) {
            return array();
        }

        $controls = array();
        $culcontrols = array();
        $icons = array(
            'i/settings' => 'fa fa-gear',
            'i/marked' => 'fa fa-lightbulb-o',
            'i/marker' => 'fa fa-lightbulb-o',
            'i/hide' => 'fa fa-eye',
            'i/show' => 'fa fa-eye-slash',
            'i/delete' => 'fa fa-remove',
            'i/up' => 'fa fa-arrow-up',
            'i/down' => 'fa fa-arrow-down'
            );

        $coursecontext = context_course::instance($course->id);

        if (($this->tcsettings['layoutstructure'] == 1) || ($this->tcsettings['layoutstructure'] == 4)) {
            // Copied from topics format.
            if ($onsectionpage) {
                $url = course_get_url($course, $section->section);
            } else {
                $url = course_get_url($course);
            }
            $url->param('sesskey', sesskey());

            $isstealth = $section->section > $course->numsections;
            $controls = array();
            if (!$isstealth && $section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
                if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                    $url->param('marker', 0);
                    $markedthistopic = get_string('markedthistopic');
                    $highlightoff = get_string('highlightoff');
                    $controls['highlight'] = array('url' => $url, "icon" => 'i/marked',
                                                   'name' => $highlightoff,
                                                   'pixattr' => array('class' => '', 'alt' => $markedthistopic),
                                                   'attr' => array('class' => 'editing_highlight', 'title' => $markedthistopic));
                } else {
                    $url->param('marker', $section->section);
                    $markthistopic = get_string('markthistopic');
                    $highlight = get_string('highlight');
                    $controls['highlight'] = array('url' => $url, "icon" => 'i/marker',
                                                   'name' => $highlight,
                                                   'pixattr' => array('class' => '', 'alt' => $markthistopic),
                                                   'attr' => array('class' => 'editing_highlight', 'title' => $markthistopic));
                }
            }
        }

        $parentcontrols = parent::section_edit_control_items($course, $section, $onsectionpage);

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $items = array();
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $items[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $items = array_merge($items, $controls);
                }
            }
        } else {
            $items = array_merge($controls, $parentcontrols);
        }

        foreach ($items as $key => $item) {
            $url = empty($item['url']) ? '' : $item['url'];
            $icon = empty($item['icon']) ? '' : $item['icon'];
            $name = empty($item['name']) ? '' : $item['name'];
            $attr = empty($item['attr']) ? '' : $item['attr'];
            $class = empty($item['pixattr']['class']) ? '' : $item['pixattr']['class'];
            $alt = empty($item['pixattr']['alt']) ? '' : $item['pixattr']['alt'];

            $url = new moodle_url($url);
            $image = html_writer::empty_tag(
                    'img',
                    array(
                        'src' => $this->output->pix_url($icon),
                        'class' => "icon editing_showhide" . $class,
                        'alt' => $alt
                    )
                );

            $icon = html_writer::tag('i', $image, array('class' => $icons[$icon]));

            $culcontrols[] = html_writer::link(
                    $url,
                    $icon,
                    $attr
                );
        }

        return $culcontrols;
    }

    /**
     * Generate a summary of a section for display on the 'course index page'
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_summary($section, $course, $mods) {
        $classattr = 'section main section-summary clearfix';
        $linkclasses = '';

        // If section is hidden then display grey section link.
        if (!$section->visible) {
            $classattr .= ' hidden';
            $linkclasses .= ' dimmed_text';
        } else if ($this->courseformat->is_section_current($section)) {
            $classattr .= ' current';
        }

        $o = '';
        $title = $this->courseformat->get_culcourse_section_name($course, $section, false);
        $liattributes = array(
            'id' => 'section-'.$section->section,
            'class' => $classattr,
            'role' => 'region',
            'aria-label' => $title
        );


        $o .= html_writer::start_tag('li', $liattributes);

        $o .= html_writer::tag('div', '', array('class' => 'left side'));
        $o .= html_writer::tag('div', '', array('class' => 'right side'));
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        if ($section->uservisible) {
            $title = html_writer::tag('a', $title,
                    array('href' => course_get_url($course, $section->section), 'class' => $linkclasses));
        }
        $o .= $this->output->heading($title, 3, 'section-title');

        $o .= html_writer::start_tag('div', array('class' => 'summarytext'));
        $o .= $this->format_summary_text($section);
        $o .= html_writer::end_tag('div');
        $o .= $this->section_activity_summary($section, $course, null);

        $context = context_course::instance($course->id);
        $o .= $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));

        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');

        return $o;
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null) {
        $o = '';

        $sectionstyle = '';
        $rightcurrent = '';
        $sectionheight = '';
        $context = context_course::instance($course->id);

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            } else if ($this->courseformat->is_section_current($section)) {
                $section->toggle = true; // Open current section regardless of toggle state.
                $sectionstyle = ' current';
                $rightcurrent = ' left';
            }
        }

        if ($this->tcsettings['showsectionsummary'] == 2) {
            $sectionheight = ' showsectionsummary';
        }

        $liattributes = array(
            'id' => 'section-' . $section->section,
            'class' => 'section main clearfix' . $sectionstyle . $sectionheight,
            'role' => 'region',
            'aria-label' => $this->courseformat->get_culcourse_section_name($course, $section, false)
        );

        $o .= html_writer::start_tag('li', $liattributes);

        if ($this->userisediting) {
            $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
            $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
            $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
            $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        }
        $o .= html_writer::start_tag('div', array('class' => 'content'));

        if (($onsectionpage == false) && ($section->section != 0)) {
            $o .= html_writer::start_tag('div',
                    array('class' => 'sectionhead toggle toggle-arrow',
                    'id' => 'toggle-' . $section->section));

            if ((!($section->toggle === null)) && ($section->toggle == true)) {
                $toggleclass = 'toggle_open';
                $sectionclass = ' sectionopen';
                $summaryclass = ' summary_open';
            } else {
                $toggleclass = 'toggle_closed';
                $sectionclass = '';
                $summaryclass = ' summary_closed';
            }
            $toggleclass .= ' the_toggle ';
            $toggleurl = new moodle_url('/course/view.php', array('id' => $course->id));
            $o .= html_writer::start_tag('a', array('class' => $toggleclass, 'href' => $toggleurl));

            if (empty($this->tcsettings)) {
                $this->tcsettings = $this->courseformat->get_settings();
            }

            $title = $this->courseformat->get_culcourse_section_name($course, $section, true);

            if ($this->userisediting) {
                $o .= $this->output->heading($title, 3, 'section-title');
            } else {
                $o .= html_writer::tag('h3', $title); // Moodle H3's look bad on mobile / tablet with CT so use plain.
            }

            $o .= html_writer::end_tag('a');

            if ($this->tcsettings['showsectionsummary'] == 2) {
                $o .= $this->section_summary_container($section, $summaryclass);
            }

            $o .= $this->section_activity_summary($section, $course, null);

            $o .= html_writer::end_tag('div'); // End .sectionhead.

            $o .= html_writer::start_tag('div', array('class' => 'sectionbody toggledsection'.$sectionclass,
                                                      'id' => 'toggledsection-' . $section->section));

            $o .= $this->section_availability_message($section, has_capability('moodle/course:viewhiddensections', $context));

            if ($this->tcsettings['showsectionsummary'] == 1) {
                $o .= $this->section_summary_container($section, $summaryclass);
            }

        } else {
            // When on a section page, we only display the general section title, if title is not the default one.
            $hasnamesecpg = ($section->section == 0 && (string) $section->name !== '');

            if ($this->userisediting) {
                // $o .= $this->output->heading(get_string('summarycalltoaction', 'format_culcourse'), 3, 'section-title');
                $o .= html_writer::tag('div', get_string('summarycalltoaction', 'format_culcourse'), array('class' => 'alert alert-info'));
            }

            if ($hasnamesecpg) {
                $o .= $this->output->heading($this->section_title($section, $course), 3, 'section-title');
            }

            // if ($hasnamesecpg) {
            //     $o .= $this->output->heading($this->section_title($section, $course), 3, 'section-title');
            // } else if ($this->userisediting) {
            //     $o .= $this->output->heading(get_string('summarycalltoaction', 'format_culcourse'), 3, 'section-title');
            // }

            $o .= html_writer::start_tag('div', array('class' => 'summary'));
            $o .= $this->format_summary_text($section);
            $o .= html_writer::end_tag('div');

            $o .= $this->section_availability_message($section, has_capability('moodle/course:viewhiddensections', $context));
        }


        return $o;
    }

    protected function section_summary_container($section, $summaryclass) {
        $summarytext = $this->format_summary_text($section);
        if ($summarytext) {
            $showsectionsummary = ($this->tcsettings['showsectionsummary'] == 2);
            $classextra = $showsectionsummary? ' summaryalwaysshown' : '';
            $o = html_writer::start_tag('div', array('class' => 'summary'.$classextra . $summaryclass));
            $o .= $this->format_summary_text($section);

            if ($showsectionsummary){
                $o .= $this->truncate_summary_text($section);
            }

            $o .= html_writer::end_tag('div');
        } else {
            $o = '';
        }
        return $o;
    }

    /**
     * Generate the display of the footer part of a section
     *
     * @return string HTML to output.
     */
    protected function section_footer() {
        // $o = '';
        // if (($section->section != 0)) {
        //     $o = html_writer::end_tag('div'); // End .sectionbody.
        // }
        $o = html_writer::end_tag('div'); // End .content.
        $o .= html_writer::end_tag('li');

        return $o;
    }

    /**
     * Generate the html for a hidden section
     *
     * @param stdClass $section The section in the course which is being displayed.
     * @param int|stdClass $courseorid The course to get the section name for (object or just course id)
     * @return string HTML to output.
     */
    protected function section_hidden($section, $courseorid = null) {
        $o = '';
        $course = $this->courseformat->get_course();
        $liattributes = array(
            'id' => 'section-' . $section->section,
            'class' => 'section main clearfix hidden',
            'role' => 'region',
            'aria-label' => $this->courseformat->get_culcourse_section_name($course, $section, false)
        );

        $o .= html_writer::start_tag('li', $liattributes);
        if ($this->userisediting) {
            $leftcontent = $this->section_left_content($section, $course, false);
            $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));

            $rightcontent = $this->section_right_content($section, $course, false);
            $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
        }

        $o .= html_writer::start_tag('div', array('class' => 'content sectionhidden'));

        $title = get_string('notavailable');
        if ($this->userisediting) {
            $o .= $this->output->heading($title, 3, 'section-title');
        } else {
            $o .= html_writer::tag('h3', $title); // Moodle H3's look bad on mobile / tablet with CT so use plain.
        }
        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');
        return $o;
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        $modinfo = get_fast_modinfo($course);
        $course = $this->courseformat->get_course();
        if (empty($this->tcsettings)) {
            $this->tcsettings = $this->courseformat->get_settings();
        }

        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);
        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');

        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);

        // Now the list of sections..
        echo $this->start_section_list();

        $sections = $modinfo->get_section_info_all();
        // General section if non-empty.
        $thissection = $sections[0];
        unset($sections[0]);
        if ($thissection->summary or !empty($modinfo->sections[0]) or $this->userisediting) {
            echo $this->section_header($thissection, $course, false, 0);
            echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
            echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0, 0);
            echo $this->section_footer();
        }

        // Change number of sections.
        if ($this->userisediting and has_capability('moodle/course:update', $context)) {
            // echo $this->section_changenumsections($course);            
        }

        if ($course->numsections > 0) {
            if ($course->numsections > 1) {
                // Collapsed Topics all toggles.
                echo $this->toggle_all();
            }
            $currentsectionfirst = false;
            if ($this->tcsettings['layoutstructure'] == 4) {
                $currentsectionfirst = true;
            }

            if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                $section = 1;
            } else {
                $timenow = time();
                $weekofseconds = 604800;
                $course->enddate = $course->startdate + ($weekofseconds * $course->numsections);
                $section = $course->numsections;
                $weekdate = $course->enddate;      // This should be 0:00 Monday of that week.
                $weekdate -= 7200;                 // Subtract two hours to avoid possible DST problems.
            }

            $numsections = $course->numsections; // Because we want to manipulate this for column breakpoints.
            if (($this->tcsettings['layoutstructure'] == 3) && ($this->userisediting == false)) {
                $loopsection = 1;
                $numsections = 0;
                while ($loopsection <= $course->numsections) {
                    $nextweekdate = $weekdate - ($weekofseconds);
                    if ((($thissection->uservisible ||
                            ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo)))
                            && ($nextweekdate <= $timenow)) == true) {
                        $numsections++; // Section not shown so do not count in columns calculation.
                    }
                    $weekdate = $nextweekdate;
                    $section--;
                    $loopsection++;
                }
                // Reset.
                $section = $course->numsections;
                $weekdate = $course->enddate;      // This should be 0:00 Monday of that week.
                $weekdate -= 7200;                 // Subtract two hours to avoid possible DST problems.
            }

            echo $this->end_section_list();
            echo $this->start_toggle_section_list();

            $loopsection = 1;
            $canbreak = false; // Once the first section is shown we can decide if we break on another column.
            $columncount = 1;
            $columnbreakpoint = 0;
            $shownsectioncount = 0;

            if ($this->userpreference != null) {
                $this->isoldtogglepreference = $this->togglelib->is_old_preference($this->userpreference);
                if ($this->isoldtogglepreference == true) {
                    $ts1 = base_convert(substr($this->userpreference, 0, 6), 36, 2);
                    $ts2 = base_convert(substr($this->userpreference, 6, 12), 36, 2);
                    $thesparezeros = "00000000000000000000000000";
                    if (strlen($ts1) < 26) {
                        // Need to PAD.
                        $ts1 = substr($thesparezeros, 0, (26 - strlen($ts1))) . $ts1;
                    }
                    if (strlen($ts2) < 27) {
                        // Need to PAD.
                        $ts2 = substr($thesparezeros, 0, (27 - strlen($ts2))) . $ts2;
                    }
                    $tb = $ts1 . $ts2;
                } else {
                    // Check we have enough digits for the number of toggles in case this has increased.
                    $numdigits = $this->togglelib->get_required_digits($course->numsections);
                    if ($numdigits > strlen($this->userpreference)) {
                        if ($this->defaultuserpreference == 0) {
                            $dchar = $this->togglelib->get_min_digit();
                        } else {
                            $dchar = $this->togglelib->get_max_digit();
                        }
                        for ($i = strlen($this->userpreference); $i < $numdigits; $i++) {
                            $this->userpreference .= $dchar;
                        }
                    }
                    $this->togglelib->set_toggles($this->userpreference);
                }
            } else {
                $numdigits = $this->togglelib->get_required_digits($course->numsections);
                if ($this->defaultuserpreference == 0) {
                    $dchar = $this->togglelib->get_min_digit();
                } else {
                    $dchar = $this->togglelib->get_max_digit();
                }
                $this->userpreference = '';
                for ($i = 0; $i < $numdigits; $i++) {
                    $this->userpreference .= $dchar;
                }
                $this->togglelib->set_toggles($this->userpreference);
            }

            while ($loopsection <= $course->numsections) {
                if (($this->tcsettings['layoutstructure'] == 3) && ($this->userisediting == false)) {
                    $nextweekdate = $weekdate - ($weekofseconds);
                }
                $thissection = $modinfo->get_section_info($section);

                /* Show the section if the user is permitted to access it, OR if it's not available
                   but there is some available info text which explains the reason & should display. */
                if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                    $showsection = $thissection->uservisible ||
                            ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo));
                } else {
                    $showsection = ($thissection->uservisible ||
                            ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo)))
                            && ($nextweekdate <= $timenow);
                }
                if (($currentsectionfirst == true) && ($showsection == true)) {
                    // Show  the section if we were meant to and it is the current section:....
                    $showsection = ($course->marker == $section);
                } else if (($this->tcsettings['layoutstructure'] == 4) && ($course->marker == $section)) {
                    $showsection = false; // Do not reshow current section.
                }
                if (!$showsection) {
                    // Hidden section message is overridden by 'unavailable' control.
                    if ($this->tcsettings['layoutstructure'] != 4) {
                        if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                            if (!$course->hiddensections && $thissection->available) {
                                $shownsectioncount++;
                                echo $this->section_hidden($thissection);
                            }
                        }
                    }
                } else {
                    $shownsectioncount++;

                    if ($this->isoldtogglepreference == true) {
                        $togglestate = substr($tb, $section, 1);
                        if ($togglestate == '1') {
                            $thissection->toggle = true;
                        } else {
                            $thissection->toggle = false;
                        }
                    } else {
                        $thissection->toggle = $this->togglelib->get_toggle_state($thissection->section);
                    }

                    echo $this->section_header($thissection, $course, false, 0);

                    if ($thissection->uservisible) {
                        echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                        echo $this->courserenderer->course_section_add_cm_control($course, $thissection->section, 0);
                    }

                    if (($thissection->section != 0)) {
                        echo html_writer::end_tag('div'); // End .sectionbody.
                    }

                    echo $this->section_footer();
                }

                if ($currentsectionfirst == false) {
                    /* Only need to do this on the iteration when $currentsectionfirst is not true as this iteration will always
                       happen.  Otherwise you get duplicate entries in course_sections in the DB. */
                    unset($sections[$section]);
                }

                if (($this->tcsettings['layoutstructure'] != 3) || ($this->userisediting)) {
                    $section++;
                } else {
                    $section--;
                    if (($this->tcsettings['layoutstructure'] == 3) && ($this->userisediting == false)) {
                        $weekdate = $nextweekdate;
                    }
                }

                $loopsection++;

                if (($currentsectionfirst == true) && ($loopsection > $course->numsections)) {
                    // Now show the rest.
                    $currentsectionfirst = false;
                    $loopsection = 1;
                    $section = 1;
                }

                if ($section > $course->numsections) {
                    // Activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                    break;
                }
            }
        }

        if ($this->userisediting and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $course->numsections or empty($modinfo->sections[$section])) {
                    // This is not stealth section or it is empty.
                    continue;
                }
                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection->section, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();
            echo $this->section_changenumsections($course);            
        } else {
            echo $this->end_section_list();
        }
    }

    /**
     * Displays the toggle all functionality.
     * @return string HTML to output.
     */
    protected function toggle_all() {
        $o = html_writer::start_tag('li', array('class' => 'tcsection main clearfix', 'id' => 'toggle-all'));

        if ($this->userisediting) {
            $o .= html_writer::tag('div', $this->output->spacer(), array('class' => 'left side'));
            $o .= html_writer::tag('div', $this->output->spacer(), array('class' => 'right side'));
        }

        $o .= html_writer::start_tag('div', array('class' => 'content'));
        $iconsetclass = ' toggle-arrow';
        $o .= html_writer::start_tag('div', array('class' => 'sectionbody'.$iconsetclass));
        $o .= html_writer::start_tag('h4', null);
        $o .= html_writer::tag('a', get_string('culcourseopened', 'format_culcourse'),
                               array('class' => 'on ', 'href' => '#', 'id' => 'toggles-all-opened'));
        $o .= html_writer::tag('a', get_string('culcourseclosed', 'format_culcourse'),
                               array('class' => 'off ', 'href' => '#', 'id' => 'toggles-all-closed'));
        $o .= html_writer::end_tag('h4');
        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('div');
        $o .= html_writer::end_tag('li');

        return $o;
    }

    public function set_user_preference($preference) {
        $this->userpreference = $preference;
    }

    public function set_default_user_preference($defaultpreference) {
        $this->defaultuserpreference = $defaultpreference;
    }

    /**
     * format_culcourse_renderer::summary_display()
     *
     * @param  stdClass $course
     * @return string $output string for summary area.
     */
    public function summary_display($course) {

        $output = '';
        $coursesummary = trim($course->summary);

        // If the course summary isn't 'empty', then display it.
        if (!preg_match('%\A(?:<p(?:\s+id="\w*"\s*)?>)*((?:\s*)|(?:\s*&nbsp;\s*)*)(?:</p>)*\z%sim', $coursesummary)) {
            $summarytitle = get_string('modulesummary', 'format_culcourse');
            $header  = html_writer::tag('div', $summarytitle, array('class' => 'panel-header ui-corner-all'));
            $content = html_writer::tag('div', $coursesummary, array('class' => 'panel-content'));
            $output .= html_writer::tag('div', $header . $content, array('class' => 'dash-panel module-summary'));
        }

        return $output;
    }

    /**
     * format_culcourse_renderer::quicklink_display()
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
                $editlink = format_culcourse_get_edit_link(
                    $course->id, 
                    'readinglists', 
                    $this->tcsettings['showreadinglists']
                    );
            }

            $urldata = format_culcourse_get_reading_list_url_data($course);

            if ($this->userisediting&& ($this->tcsettings['showreadinglists'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$urldata) {
                // Not installed or not configured
                $rllnktxt = get_string('aspirelists', 'format_culcourse');
                $rlattrs['title'] = get_string('not-installed-readinglist', 'format_culcourse');
                $rlattrs['class'] = 'nolink';
                $rlurl = 'javascript:void(0);';
                $liattrs['class'] = 'wide';
            } else {
                $rllnktxt = get_string('aspirelists', 'format_culcourse');

                if (OK == $urldata['status']) {
                    $listtype = $urldata['listtype'];
                    $rlurl    = $urldata['url'];

                    if ('module' == $listtype) {
                        $rlattrs['title'] = get_string('view-readinglist-module', 'format_culcourse');
                        $rlattrs['target'] = '_blank';
                    } else if ('module-year' == $listtype) {
                        $rlattrs['title'] = get_string('view-readinglist-module-year', 'format_culcourse');
                        $rlattrs['target'] = '_blank';
                    }
                } else if (NODATA == $urldata['status']) {
                    $rlattrs['title'] = get_string('no-readinglist', 'format_culcourse');
                    $rlattrs['class'] = 'nolink';
                    $rlurl = 'javascript:void(0);';
                } else if (ERROR == $urldata['status']) {
                    $rlattrs['title'] = get_string('error-readinglist', 'format_culcourse');
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

        // Timetable link
        if ($this->tcsettings['showtimetable'] == 2 || $this->userisediting) {
            $ttattrs = array ();
            $class = '';
            $editlink = '';

            if ($this->userisediting) {
                $editlink = format_culcourse_get_edit_link(
                    $course->id, 
                    'timetable', 
                    $this->tcsettings['showtimetable']
                    );
            }

            $ttdata = format_culcourse_get_timetable_url($course);

            if ($this->userisediting&& ($this->tcsettings['showtimetable'] != 2)) {
                    $class = 'linkhidden';                
            }

            if (!$ttdata) {
                // Not installed or not configured.
                $ttattrs['title'] = get_string('not-installed-timetable', 'format_culcourse');
                $ttattrs['class'] = 'nolink';
                $tturl = 'javascript:void(0);';
            } else {
                if (OK == $ttdata['status']) {
                    $ttattrs['title']  = get_string('view-timetable', 'format_culcourse');
                    $ttattrs['target'] = '_blank';
                    $tturl = $ttdata['url'];
                } else if (NODATA == $ttdata['status']) {
                    $ttattrs['title']  = get_string('no-timetable', 'format_culcourse');
                    $ttattrs['class'] = 'nolink';
                    $ttattrs['target'] = '_blank';
                    $tturl = $ttdata['url'];
                } else if (ERROR == $ttdata['status']) {
                    $ttattrs['title'] = get_string('error-timetable', 'format_culcourse');
                    $ttattrs['class'] = 'nolink';
                    $tturl = 'javascript:void(0);';
                }
            }

            $tticon = $this->output->pix_icon('i/scheduled', '', 'moodle', array('class'=>'iconsmall'));
            $tticon = $icon = html_writer::tag('i', '', array('class' => 'fa fa-clock-o'));
            $ttlnktx = get_string('timetable', 'format_culcourse');
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
                $editlink = format_culcourse_get_edit_link(
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
                $gattrs['title'] = get_string('view-graderreport', 'format_culcourse');
                $gurl = new moodle_url('/grade/report/grader/index.php', array('id' => $course->id));
            } else if (has_capability('moodle/grade:view', $coursecontext)) { // Student
                $gattrs['title'] = get_string('viewgrades', 'grades');
                $gurl = new moodle_url('/grade/report/culuser/index.php', array('id' => $course->id));
            } else  {
                $gattrs['title'] = get_string('no-view-grades', 'format_culcourse');
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
                $editlink = format_culcourse_get_edit_link(
                    $course->id, 
                    'calendar', 
                    $this->tcsettings['showcalendar']
                    );
            }

            $clnktxt = get_string('calendar', 'calendar');

            if ($this->userisediting&& ($this->tcsettings['showcalendar'] != 2)) {
                    $class = 'linkhidden';                
            }

            $cattrs['title'] = get_string('view-calendar', 'format_culcourse');
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
                    $editlink = format_culcourse_get_edit_link(
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
                    $phattrs['title']  = get_string('view-student-photoboard', 'format_culcourse', $alias);
                    $phattrs['target'] = '';
                    $phurl = format_culcourse_get_photoboard_url($course, $studentrole->id);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-student-photoboard', 'format_culcourse', $alias);
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
                    $editlink = format_culcourse_get_edit_link(
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
                    $phattrs['title']  = get_string('view-lecturer-photoboard', 'format_culcourse', $alias);
                    $phattrs['target'] = '';
                    $phurl = format_culcourse_get_photoboard_url($course, $lecturerrole->id);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-lecturer-photoboard', 'format_culcourse', $alias);
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
                    $editlink = format_culcourse_get_edit_link(
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
                    $phattrs['title']  = get_string('view-courseofficer-photoboard', 'format_culcourse', $alias);
                    $phattrs['target'] = '';
                    $phurl = format_culcourse_get_photoboard_url($course, $courseofficerrole->id);
                } else {
                    $phattrs['class'] = 'nolink';
                    $phattrs['title']  = get_string('no-view-courseofficer-photoboard', 'format_culcourse', $alias);
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
                $editlink = format_culcourse_get_edit_link(
                    $course->id, 
                    'media', 
                    $this->tcsettings['showmedia']
                    );
            }

            $klnktxt = get_string('media', 'format_culcourse');

            if ($this->userisediting&& ($this->tcsettings['showmedia'] != 2)) {
                    $class = 'linkhidden';                
            }

            $kattrs['title'] = get_string('view-media', 'format_culcourse');
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
        $title = get_string('quicklinks', 'format_culcourse');
        $panelheader = html_writer::tag('div', $title, array('class'=>'panel-header ui-corner-all'));
        $navcontent = html_writer::tag('nav', $content, array('class'=>'linkscontainer'));
        $output .= html_writer::tag('div', $panelheader . $navcontent, array('class'=>'dash-panel clearfix'));

        return $output;
    }


    /**
     * format_culcourse_renderer::activity_modules_display()
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
                $liattrs['title']  = get_string('view-mod', 'format_culcourse', strtolower($modfullname));
                $class = '';
                $editlink  = '';
                
                if ($this->userisediting) {
                    $editlink = format_culcourse_get_edit_link(
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

        $title = get_string('activities', 'format_culcourse');
        $panelheader = html_writer::tag('div', $title, array('class'=>'panel-header ui-corner-all'));
        $navcontent  = html_writer::tag('nav', $content, array('class'=>'linkscontainer'));
        $output .= html_writer::tag('div', $panelheader . $navcontent, array('class'=>'dash-panel clearfix'));

        return $output;
    }

    /**
     * format_culcourse_renderer::exttools_modules_display()
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
                $liattrs['title']  = get_string('view-mod', 'format_culcourse', strtolower($modnames['modfullname']));
                $class = '';
                $editlink  = '';
                
                if ($this->userisediting) {
                    $editlink = format_culcourse_get_edit_link(
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


    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_right_content($section, $course, $onsectionpage) {
        $o = $this->output->spacer();
        $controls = $this->section_edit_control_items($course, $section, $onsectionpage);

        if (!empty($controls)) {
            $o = implode('', $controls);
        }

        return $o;
    }

    /**
     * Generate the content to displayed on the left part of a section
     * before course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function section_left_content($section, $course, $onsectionpage) {
        $o = $this->output->spacer();

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (course_get_format($course)->is_section_current($section)) {
                $o = get_accesshide(get_string('currentsection', 'format_'.$course->format));
            }
        }

        return $o;
    }

    /**
     * Generate html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function format_summary_text($section) {
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);
        $options = new stdClass();
        $options->noclean = false;
        $options->overflowdiv = true;
        return format_text($summarytext, $section->summaryformat, $options);
    }

    /**
     * generate truncated html for a section summary text
     *
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function truncate_summary_text($section) {
        $context = context_course::instance($section->course);
        $summarytext = file_rewrite_pluginfile_urls($section->summary, 'pluginfile.php',
            $context->id, 'course', 'section', $section->id);

        if (strlen($summarytext) > 250) {
            // $summarytext = substr($summarytext, 0, 249) . '...';
        }

        $options = new stdClass();
        $options->noclean = false;
        $options->overflowdiv = false;
        $summarytext = format_text($summarytext, $section->summaryformat, $options);
        $summarytext = html_writer::tag('div', $summarytext);
        $summarytext = html_writer::tag('div', $summarytext, array('class' => 'truncate'));
        return $summarytext;
    }

    /**
     * Generate a summary of the activites in a section
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course the course record from DB
     * @param array    $mods (argument not used)
     * @return string HTML to output.
     */
    protected function section_activity_summary($section, $course, $mods) {
        $modinfo = get_fast_modinfo($course);

        if (empty($modinfo->sections[$section->section])) {
            return '';
        }

        // Generate array with count of activities in this section:
        $sectionmods = array();
        $resource = get_string('resources');
        $activity = get_string('activities');
        $total = 0;
        $complete = 0;
        $cancomplete = isloggedin() && !isguestuser();
        $completioninfo = new completion_info($course);

        foreach ($modinfo->sections[$section->section] as $cmid) {
            $thismod = $modinfo->cms[$cmid];

            if ($thismod->modname == 'label' || $thismod->modname == 'bootstrapelements') {
                // Labels are special (not interesting for students)!
                continue;
            }

            if ($thismod->uservisible) {
                if (plugin_supports('mod', $thismod->modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER) == MOD_ARCHETYPE_RESOURCE) {
                    $type = $resource;
                } else {
                    $type = $activity;
                }

                if (isset($sectionmods[$type]['name'])) {
                    $sectionmods[$type]['count']++;
                } else {
                    $sectionmods[$type]['name'] = $type;
                    $sectionmods[$type]['count'] = 1;
                }

                if ($cancomplete && $completioninfo->is_enabled($thismod) != COMPLETION_TRACKING_NONE) {
                    $total++;
                    $completiondata = $completioninfo->get_data($thismod, true);

                    if ($completiondata->completionstate == COMPLETION_COMPLETE ||
                            $completiondata->completionstate == COMPLETION_COMPLETE_PASS) {
                        $complete++;
                    }
                }
            }
        }

        asort($sectionmods);

        if (empty($sectionmods)) {
            // No sections
            return '';
        }

        // Output section activities summary:
        $o = '';
        $o.= html_writer::start_tag('div', array('class' => 'section-summary-activities mdl-right'));
        foreach ($sectionmods as $mod) {
            $o.= html_writer::start_tag('span', array('class' => 'activity-count'));
            $o.= $mod['name'].': '.$mod['count'];
            $o.= html_writer::end_tag('span');
        }
        $o.= html_writer::end_tag('div');

        // Output section completion data
        if ($total > 0) {
            $a = new stdClass;
            $a->complete = $complete;
            $a->total = $total;

            $o.= html_writer::start_tag('div', array('class' => 'section-summary-progress mdl-right'));
            $o.= html_writer::tag('span', get_string('progresstotal', 'completion', $a), array('class' => 'activity-count'));
            $o.= html_writer::end_tag('div');
        }

        return $o;
    }

    protected function section_changenumsections($course) {
        $o = html_writer::start_tag('div', array('id' => 'changenumsections', 'class' => ''));

        // Increase number of sections.
        $straddsection = get_string('addsection', 'format_culcourse');
        $url = new moodle_url('/course/changenumsections.php',
                        array('courseid' => $course->id,
                            'increase' => true,
                            'sesskey' => sesskey()
                            )
                        );
        $addsectionbutton = new single_button($url, $straddsection, 'get');
        $addsectionbutton->class = 'sectionbutton btn-city';
        $o .= $this->output->render($addsectionbutton);

        if ($course->numsections > 0) {
            // Reduce number of sections sections.
            $strremovesection = get_string('removesection', 'format_culcourse');
            $url = new moodle_url('/course/changenumsections.php',
                            array('courseid' => $course->id,
                                'increase' => false,
                                'sesskey' => sesskey()
                                )
                            );
            $removesectionbutton = new single_button($url, $strremovesection, 'get');
            $removesectionbutton->class = 'sectionbutton btn-city';
            $o .= $this->output->render($removesectionbutton);
        }

        $o .= html_writer::end_tag('div'); // End #changenumsections.

        return $o;
    }
}
