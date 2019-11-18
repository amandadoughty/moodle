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
 * Library Search block
 *
 * @package   block_cullib_search
 * @copyright 2019 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_cullib_search extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_cullib_search');
    }

    public function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->footer = '';
        $this->content->text = '';

        $actionurl = get_config('block_cullib_search', 'actionurl');
        $output = $this->page->get_renderer('block_cullib_search');
        $searchform = new \block_cullib_search\output\search_form($actionurl);
        $this->content->text = $output->render($searchform);

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }
}
