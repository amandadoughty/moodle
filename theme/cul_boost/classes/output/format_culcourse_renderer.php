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

}