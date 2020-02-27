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
 * Basic renderer for pgrid format.
 *
 * @copyright 2020 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_cul_boost_format_pgrid_renderer extends format_pgrid_renderer {

    /**
     * Generate the html for the dashboard
     * @param stdClass $course The course entry from DB
     * @param int $displaysection The section number in the course which is being displayed
     * @return string HTML to output.
     */
    public function build_dashboard($course, $displaysection = null) {
        $o = '';
        return $o;
    }

    /**
     * Generate the html for the dashboard
     * @param stdClass $course The course entry from DB
     * @param int $displaysection The section number in the course which is being displayed
     * @return string HTML to output.
     */
    public function build_dashboard_in_header($course, $displaysection) {
        $o = '';
        $dashrenderclass = "local_culcourse_dashboard\output\dashboard";

        if (class_exists($dashrenderclass)) {
            $config = course_get_format($course)->get_format_options();
            $dashboard = new $dashrenderclass($course, $displaysection, $config);
            $templatecontext = $dashboard->export_for_template($this);
            $o .= $this->render_from_template('local_culcourse_dashboard/dashboard', $templatecontext);
        }

        return $o;
    }

}
