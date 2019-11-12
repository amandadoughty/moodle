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
 * Renderer for outputting the format_culcourse.
 *
 * @package   format_culcourse
 * @copyright 2018 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/course/format/culcourse/renderer.php");

use format_culcourse\output\dashboard;

/**
 * Basic renderer for topics format.
 *
 * @copyright 2012 Dan Poltawski
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_cul_boost_format_culcourse_renderer extends format_culcourse_renderer {

	/** @var array format Settings for the format
	*/
	private $culconfig;

	/**
	 * Constructor method, calls the parent constructor
	 *
	 * @param moodle_page $this->page
	 * @param string $target one of rendering target constants
	 */
	public function __construct(moodle_page $page, $target) {
	    global $COURSE;

	    parent::__construct($page, $target);

	    $this->culconfig = course_get_format($COURSE)->get_format_options();

	    // @TODO
	    // Since format_culcourse_renderer::section_edit_controls() only displays the 'Set current section' control when editing mode is on
	    // we need to be sure that the link 'Turn editing mode on' is available for a user who does not have any other managing capability.
	    $this->page->set_other_editing_capability('moodle/course:setcurrentsection');
	}

	/**
	 * Generate the starting container html for a list of sections
	 * @return string HTML to output.
	 */
	public function dashboard_section() {
	    global $COURSE;

	    $o = '';
	    $dashboard = new dashboard($COURSE, $this->culconfig);
	    $templatecontext = $dashboard->export_for_template($this);
	    $o .= $this->render_from_template('format_culcourse/dashboard', $templatecontext);

	    return $o;
	}

	/**
	 * Generate the starting container html for a list of sections
	 * @return string HTML to output.
	 */
	public function start_section_list() {
	    global $COURSE;

	    $o = '';
	    $o .=  html_writer::start_tag('ul', ['class' => 'culcourse']);

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
        $course = course_get_format($course)->get_course();
        $context = context_course::instance($course->id);
        // Title with completion help icon.
        $completioninfo = new completion_info($course);

        echo $completioninfo->display_help_icon();
        echo $this->output->heading($this->page_title(), 2, 'accesshide');
        // Copy activity clipboard..
        echo $this->course_activity_clipboard($course, 0);
        // Now the list of sections..
        echo $this->start_section_list();

        $numsections = course_get_format($course)->get_last_section_number();       

        foreach ($modinfo->get_section_info_all() as $section => $thissection) {
            if ($section == 0) {
                // 0-section is displayed a little different then the others
                if ($thissection->summary or !empty($modinfo->sections[0]) or $this->page->user_is_editing()) {
                    echo $this->section_header($thissection, $course, false, 0);
                    
                    echo html_writer::start_tag('div', ['class'=>'topsection-wrap d-flex flex-wrap align-items-stretch']);
	                   
                    $title = get_section_name($course, $section);
                    $sectionsummary = $this->output->heading($title, 3, 'section-title');
                    $sectionsummary .= $this->section_summary_container($thissection);                        
                    $class = '';

                    $sectioncm = $this->courserenderer->course_section_cm_list($course, $thissection, 0);

                    echo html_writer::tag('div', $sectionsummary, ['class'=>'course-summary col p-3 bg-light']);
                    echo html_writer::tag('div', $sectioncm, ['class'=>'col-12 '.$class.' px-3 pb-3 bg-light']);

                    echo html_writer::end_tag('div');

                    echo $this->courserenderer->course_section_add_cm_control($course, 0, 0); 

                    echo $this->injected_section_footer($course, $section, $context, $thissection, false);
                }
                continue;
            }

            if (($this->page->user_is_editing() || $course->coursedisplay == COURSE_DISPLAY_SINGLEPAGE) 
                && $numsections > 1 && $section == 1)
            {                
                // Collapse/Expand all.
                echo $this->toggle_all();
            }

            if ($section > $numsections) {
                // activities inside this section are 'orphaned', this section will be printed as 'stealth' below.
                continue;
            }
            // Show the section if the user is permitted to access it, OR if it's not available.
            // but there is some available info text which explains the reason & should display.
            $showsection = $thissection->uservisible ||
                    ($thissection->visible && !$thissection->available &&
                    !empty($thissection->availableinfo));

            if (!$showsection) {
                // If the hiddensections option is set to 'show hidden sections in collapsed
                // form', then display the hidden section message - UNLESS the section is
                // hidden by the availability system, which is set to hide the reason.
                if (!$course->hiddensections && $thissection->available) {
                    echo $this->section_hidden($section, $course->id);
                }

                continue;
            }

            if (!$this->page->user_is_editing() && $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                // Display section summary only.
                echo $this->section_summary($thissection, $course, null);
            } else {
                echo $this->section_header($thissection, $course, false, 0);

                if ($thissection->uservisible) {
                    echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                    echo $this->courserenderer->course_section_add_cm_control($course, $section, 0);
                }        
                
                // We don't insert a section at the end. We allow user to append multiple sections instead.
                // if ($numsections != $section) {
                    echo $this->injected_section_footer($course, $section, $context, $thissection, false);
                // }
            }
        }        

        if ($this->page->user_is_editing() and has_capability('moodle/course:update', $context)) {
            // Print stealth sections if present.
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                if ($section <= $numsections or empty($modinfo->sections[$section])) {
                    // this is not stealth section or it is empty
                    continue;
                }

                echo $this->stealth_section_header($section);
                echo $this->courserenderer->course_section_cm_list($course, $thissection, 0);
                echo $this->stealth_section_footer();
            }

            echo $this->end_section_list();
	    $this->change_number_sections($course, 0);                      
        } else {
            echo $this->end_section_list();
        }        
    }

        /**
     * Generate the display of the header part of a section before
     * course modules are included
     *
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a single-section page
     * @param int $sectionreturn The section to return to after an action
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn=null) {
        $o = '';
        $currenttext = '';
        $sectionstyle = '';

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if (course_get_format($course)->is_section_current($section)) {
                $sectionstyle = ' current';
            }
        }

        $title = get_section_name($course, $section);

        $o.= html_writer::start_tag(
            'li', 
            [
                'id' => 'section-'.$section->section,
                'class' => 'section main clearfix'.$sectionstyle, 
                'role'=>'region',
                'aria-label'=> get_section_name($course, $section)
            ]
        );

        // $o .= html_writer::link(
        //     "#endofsection-{$section->section}",
        //     get_string('skipsection', 'format_culcourse', $title),
        //     ['class' => 'sr-only sr-only-focusable']
        // );

        // Create a span that contains the section title to be used to create the keyboard section move menu.
        $o .= html_writer::tag(
            'span', 
            get_section_name($course, $section), 
            ['class' => 'hidden sectionname']
        );

        $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
        $o.= html_writer::tag(
            'div', 
            $leftcontent, 
            ['class' => 'left side']
        );

        $rightcontent = $this->section_right_content($section, $course, $onsectionpage);
        $o.= html_writer::tag(
            'div', 
            $rightcontent, 
            ['class' => 'right side']
        );
        $o.= html_writer::start_tag('div', ['class' => 'content']);

        // When not on a section page, we display the section titles except the general section if null.
        $hasnamenotsecpg = (!$onsectionpage && ($section->section != 0 || !is_null($section->name)));

        // When on a section page, we only display the general section title, if title is not the default one.
        $hasnamesecpg = ($onsectionpage && ($section->section == 0 && !is_null($section->name)));
        $classes = ' accesshide';

        if ($hasnamenotsecpg || $hasnamesecpg) {
            $classes = '';
        }

        $ariapressed = 'true';

        if ($section->section != 0) {
            // user_preference_allow_ajax_update('format_culcourse_expanded' . $section->id, PARAM_INT);
            // $userpref = 'format_culcourse_expanded' . $section->id;
            $attributes = [
                'class' => 'sectionhead toggle',
                'id' => 'toggle-' . $section->id,
                'data-toggle' => 'collapse',
                'data-target' => '.course-content #togglesection-' . $section->id,
                'role' => 'button', 
                'aria-pressed' => $ariapressed
            ];

            if ($onsectionpage) {
                $attributes['class'] = 'toggle';
                $attributes['data-toggle'] = '';
                $attributes['data-target'] = '';
            }

            $o .= html_writer::start_tag(
                'div',
                $attributes
            );

            $sectionname = html_writer::tag('span', $this->section_title($section, $course));
            $o .= $this->output->heading($sectionname, 3, 'sectionname' . $classes);
            $o .= $this->section_availability($section);

            
            if ($this->culconfig['showsectionsummary'] == 2) {
                $o .= $this->section_summary_container($section, $onsectionpage);
            }

            if(!$onsectionpage) {
                $o .= $this->section_activity_summary($section, $course, null);
            }

            $o .= html_writer::end_tag('div'); // .sectionhead.

            $attributes = [
                'class' => 'sectionbody togglesection collapse show',
                'id' => 'togglesection-' . $section->id,
                'data-preference-key' => $section->id
            ];

            if ($onsectionpage) {
                $attributes['class'] = 'sectionbody';
            }

            $o .= html_writer::start_tag(
                'div',
                $attributes
            );

            if ($this->culconfig['showsectionsummary'] == 1) {
                $o .= $this->section_summary_container($section, $onsectionpage);
            }

        }  else {
            $o .= html_writer::tag('div', '', ['class' => 'summary']); //@TODO
        }

        return $o;
    }

    /**
     * Generate the display of the footer part of a section
     *
     * @param stdClass $course The course entry from DB
     * @param int $section The section order number
     * @param stdClass $section The course_section entry from DB
     * @param stdClass $context 
     * @param bool $onsectionpage true if being printed on a section page

     * @return string HTML to output.
     */
    protected function injected_section_footer($course, $section, $context, $thissection, $onsectionpage) {
        $o = '';
        $ariapressed = 'true';

        if ($section != 0) {
            $o .= html_writer::end_tag('div'); // .sectionbody.

            $attributes = [
                'class' => 'sectionclose',
                'id' => 'footertoggle-' . $thissection->id,
                'href' => '',
                'data-toggle' => 'collapse',
                'data-target' => '.course-content #togglesection-' . $thissection->id,
                'role' => 'button', 
                'aria-pressed' => $ariapressed
            ];

            if ($onsectionpage) {
                $attributes['class'] = 'toggle';
                $attributes['data-toggle'] = '';
                $attributes['data-target'] = '';
            }

            $o .= html_writer::start_tag(
                'a',
                $attributes
            );

            $o .= get_string('closesection', 'format_culcourse');
            $o .= html_writer::end_tag('a');
        }

        $o .= html_writer::end_tag('div'); // .content.

        if ($this->page->user_is_editing() and has_capability('moodle/course:update', $context)) {                    
            $o .= $this->insert_section($course, $section + 1);
        }

        // $o .= html_writer::tag('span', '', ['id' => "endofsection-{$thissection->section}"]);
        $o .= html_writer::end_tag('li');

        return $o;
    }

}
