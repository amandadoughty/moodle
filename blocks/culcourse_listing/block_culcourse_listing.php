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
 * CUL Course listing block
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot.'/blocks/culcourse_listing/locallib.php');
require_once($CFG->dirroot.'/blocks/culcourse_listing/renderer.php');

/**
 * CUL Course listing block
 *
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_culcourse_listing extends block_base {
    /**
     * Block initialization
     */
    public function init() {
        $this->title   = get_string('courseoverview', 'block_culcourse_listing');
    }

    /**
     * Return contents of culcourse_listing block
     *
     * @return stdClass contents of block
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        $content = array();
        $attributes = array();

        $config = get_config('block_culcourse_listing');
        // Get all of the user preferences for this block. NB returns default values if no
        // preferences are set.
        $preferences = block_culcourse_listing_get_preferences();
        // Get the courses that the user is  enrolled on.
        $enrolledcourses = enrol_get_my_courses('id, shortname, fullname, idnumber,
            category, summary', 'fullname ASC, visible DESC');
        // For users with course enrollments, we want to filter out empty categories, so we need
        // the filtered courses in order to identify the filtered categories.
        // Anyone who has a category role assignment will still see the categories even if they
        // are empty.
        $filteredcourseids = block_culcourse_listing_get_filtered_course_ids($enrolledcourses, $config, $preferences);
        $favourites = block_culcourse_listing_get_favourite_courses($preferences);
        // Get categories with enrolled courses.
        list($mycategories, $filteredcategoryids) = block_culcourse_listing_get_categories($enrolledcourses, $filteredcourseids);
        $attributes = block_culcourse_listing_get_filter_state($config, $preferences);

        $chelper = new block_culcourse_listing_helper();
        $chelper->set_my_courses($enrolledcourses);
        $chelper->set_my_categories($mycategories);
        $chelper->set_favourites($favourites);
        $chelper->set_filtered_category_ids($filteredcategoryids);
        $chelper->set_attributes($attributes);

        $renderer = $this->page->get_renderer('block_culcourse_listing');
        $renderer->set_config($config);
        $renderer->set_preferences($preferences);

        if (empty($enrolledcourses) && !coursecat::has_capability_on_any('moodle/course:view')) {
            $this->content->text .= get_string('nocourses', 'my');
        } else {
            // Render the favourites area.
            $this->content->text .= $renderer->favourite_area($chelper);
            // Render the courses area.
            $this->content->text .= $renderer->culcourse_listing($chelper);

            // YUI strings.
            $this->page->requires->string_for_js('clearfavouritescheck', 'block_culcourse_listing');
            $this->page->requires->string_for_js('reorderfavouritescheck', 'block_culcourse_listing');
            $this->page->requires->string_for_js('move', 'block_culcourse_listing');
            $this->page->requires->string_for_js('favouriteadd', 'block_culcourse_listing');
            $this->page->requires->string_for_js('favouriteremove', 'block_culcourse_listing');
            $this->page->requires->string_for_js('nofavourites', 'block_culcourse_listing');
            $this->page->requires->string_for_js('divalert', 'block_culcourse_listing');
            $this->page->requires->string_for_js('and', 'block_culcourse_listing');
            $this->page->requires->string_for_js('all', 'block_culcourse_listing');

            // YUI modules.
            $this->page->requires->yui_module(
                'moodle-block_culcourse_listing-course_list',
                'M.blocks_culcourse_listing.init_course_list',
                array(array('config' => $config))
            );

            $this->page->requires->yui_module(
                'moodle-block_culcourse_listing-favourite_list',
                'M.blocks_culcourse_listing.init_favourite_list'
            );
        }

        return $this->content;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => false, 'site-index' => true, 'my-index' => true);
    }

    /**
     * Sets block header to be hidden or visible
     *
     * @return bool if true then header will be visible.
     */
    public function hide_header() {
        return true;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }
}
