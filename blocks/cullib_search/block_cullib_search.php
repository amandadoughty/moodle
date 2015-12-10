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
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
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

        if (!empty($this->config->text)) {
            $this->content->text = '<p>' .$this->config->text . '</p>';
        } else {

            $this->content->text = '';
        }

        $strsearch  = get_string('search');

        // @TODO refactor using Moodle form classes.
        $this->content->text .= '<form name="search" action="http://city.summon.serialssolutions.com/search" method="get"';
        $this->content->text .= 'target="_blank">';
        $this->content->text .= '<input id="searchform_search" name="s.q" type="text" size="16" style="width: 70%;"';
        $this->content->text .= 'placeholder="Search for books, articles and more ..."/>';
        $this->content->text .= '<button type="submit" title="'.$strsearch.'">'.$strsearch.'</button><br />';
        $this->content->text .= '<input name="s.cmd" class="refinels" type="radio" id="all" style="margin: 0px 2px 2px 2px;"';
        $this->content->text .= 'value="" checked/>';
        $this->content->text .= '<label for = "all">Everything</label>';
        $this->content->text .= '<input name="s.cmd" class="refinels" type="radio" id="journal" style="margin: 0px 2px 2px 12px;"';
        $this->content->text .= 'value="addFacetValueFilters(ContentType,Journal Article,f|ContentType,Journal / eJournal,f)" />';
        $this->content->text .= '<label for = "journal">Journal Articles</label>';
        $this->content->text .= '<input name="s.cmd" class="refinels" type="radio" id="book" style="margin: 0px 2px 2px 12px;"';
        $this->content->text .= 'value="addFacetValueFilters(ContentType,Book / eBook)" />';
        $this->content->text .= '<label for = "book">Books/eBooks</label>';
        $this->content->text .= '<input name="s.cmd" type="hidden" value="addFacetValueFilters(ContentType,Book Review:t)" />';
        $this->content->text .= '<input name="s.cmd" type="hidden" value="addFacetValueFilters(ContentType,Newspaper Article:t)"/>';
        $this->content->text .= '</form>';

        return $this->content;
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

}
