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
 * CUL Course Dashboard block
 *
 * @package    block_culcourse_dashboard
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>, Tim Gagen <tim.gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/culcourse_dashboard/locallib.php');

class block_culcourse_dashboard extends block_base {

    function init() {
        $this->title = get_string('title', 'block_culcourse_dashboard');
    }

    public function specialization () {
        if(isset($this->config)) {
            $elements = array('coursesummary', 'readinglists', 'timetable', 'graderreport', 'calendar', 'photoboard');

            foreach ($elements as $element) {
                $config = 'hide_' . $element;

                if (isset($this->config->{$config}) && $this->config->{$config} == 0) {
                    $this->config->{$config} = 2;
                }
            }
        }
    }

    public function hide_header() {
        return $this->page->user_is_editing() ? false : true;
    }

    public function applicable_formats() {
        return array('all'                => false,
                     'course-view'        => true,
                     'course-view-social' => false);
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_can_be_docked() {
        return false;
    }

    public function has_config() {
        return true;
    }


    function get_content() {
        global $CFG, $COURSE, $USER;
        $course = clone($COURSE);

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text   = '';
        $this->content->footer = '';
        $renderer  = $this->page->get_renderer('block_culcourse_dashboard');

        // Course summary.
        if (empty($this->config->hide_coursesummary) || !$this->config->hide_coursesummary) {
            $this->content->text .= $renderer->summary_display($course);
        }

        // Quick Links.
        $this->content->text .= $renderer->quicklink_display($course, $this->config, $this->instance->id);

        // Activity module links.
        $this->content->text .= $renderer->activity_modules_display($course, $this->config);


        return $this->content;
    }
}
