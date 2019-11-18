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
 * culcourse_listing block renderer
 *
 * @package    block_culcourse_listing
 * @copyright  2013 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . "/blocks/culcourse_listing/renderer.php");

/**
 * The block culcourse listing renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('block_culcourse_listing');
 */
class theme_cul_boost_block_culcourse_listing_renderer extends block_culcourse_listing_renderer {

	/**
	 * Creates html for favourite area
	 *
	 * @param block_culcourse_listing_helper $chelper various display options
	 * @return string $content string for favourite area.
	 */
	public function favourite_area(block_culcourse_listing_helper $chelper) {
	    global $USER;
	    $class = '';
	    $courses = $chelper->get_favourites();

	    if ($courses) {
	        $class = 'show';
	    } else {
	        $class = 'hide';
	    }

	    // Generate an id and the required JS call to make this a nice widget.
	    $id = 'favourites';
	    // Start content generation.
	    $content = html_writer::start_tag('div', array('id' => $id, 'class' => 'favourites tab-pane', 'role'=>'tabpanel'));

	    // Header.
	    $content .= html_writer::start_tag('div', array('id' => 'favouritesheader', 'class'=>'mb-3'));
	    $heading = $this->output->heading(
	        get_string('myfavourites', 'theme_cul_boost'),
	        2,
	        'culcourse_listing m-0'
	        );
	    $heading = html_writer::link(
            '#', 
            $heading, 
            [
                'class'=>'nav-link favourites-link',
                'data-toggle'=>'tab',
                'data-target' => '#' . $id,
                'role'=>'tab',
                'aria-expanded'=>'false'
            ]
        );
	    $content .= html_writer::tag('li', $heading, ['class'=>'nav-item']);

	    $content .= html_writer::start_tag('div', array('id' => 'favouritesbuttons'));

	    // Clear favourites button.
	    $button = get_string('clearfavourites', 'block_culcourse_listing');
	    $clearurl = new moodle_url('/blocks/culcourse_listing/clearfavourites_post.php');
	    $content .= html_writer::start_tag(
	        'div',
	        array('id' => 'clearfavourites', 'class' => $class)
	        );
	    $content .= $this->output->single_button($clearurl, $button);
	    $content .= html_writer::end_tag('div');

	    // Reorder favourites button.
	    $button = get_string('reorderfavourites', 'block_culcourse_listing');
	    $reorderurl = new moodle_url('/blocks/culcourse_listing/reorderfavourites_post.php');
	    $content .= html_writer::start_tag(
	        'div',
	        array('id' => 'reorderfavourites', 'class' => $class)
	        );
	    $content .= $this->output->single_button($reorderurl, $button);
	    $content .= html_writer::end_tag('div');
	    $content .= html_writer::end_tag('div'); // End #favouritesbuttons.
	    $content .= html_writer::end_tag('div'); // End #favouritesheader.
	    $content .= html_writer::start_tag('div', array('class' => 'fav'));

	    // List of favourite courses.
	    $content .= $this->favourites($chelper, $courses, 'favourite_');
	    $content .= html_writer::end_tag('div');
	    $content .= html_writer::end_tag('div');

	    return $content;
	}

    /**
     * Returns HTML to display a tree of subcategories and courses in the given category
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param core_course_category $coursecat top category (this category's name and description will NOT be added to the tree)
     * @return string
     */
    protected function coursecat_tree(block_culcourse_listing_helper $chelper, $coursecat) {
        // Start content generation.
        $content = '';

        // If the category is visible to the user and is either, the site category or a
        // category that the user has course enrolments in, or a category that the user
        // can view without participation (ie a category the user has a role assignement in).
        // Then get the content.
        if ($coursecat->is_uservisible() &&
            (
                $coursecat->id == 0 ||
                array_key_exists($coursecat->id, $chelper->get_my_categories()) ||
                coursecat::has_capability_on_any('moodle/course:view')
            )
        ) {
            $all = get_string('all', 'block_culcourse_listing');
            $filters = array();
            $years = array();
            $periods = array();
            $id = 'course_category_tree';
            $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
            // Get the category content. Calling this function also sets the list of filtered
            // years and periods.
            $categorycontent = $this->coursecat_subcategory_children($chelper, $coursecat, 0);

            if (empty($categorycontent)) {
                return '';
            }

            $content .= html_writer::start_tag('div', ['id'=>$id, 'class'=>'tab-pane active', 'role'=>'tabpanel']); // Start .course_category_tree div.
            $content .= html_writer::start_tag('div', $attributes);
            $content .= html_writer::start_tag('div', ['id' => 'allcoursesheader']);

            // If filter by year is enabled in the admin settings then build the array for the year
            // filter.
            if ($this->config->filterbyyear) {
                $userpref = 'culcourse_listing_filter_year';
                $selectedyear = $this->preferences[$userpref];

                if ($selectedyear != $all) {
                    $filters[] = $selectedyear;
                    // Adds the selected year to the existing array (NB the year and period arrays have been
                    // filled by calling coursecat_subcategory_children() but the user may have set a preference
                    // that no longer exists so we need to add that).
                    $years = array($selectedyear => $selectedyear);
                }

                $filteryears = $chelper->get_filter_years();
                // Remove duplicates.
                $filteryears = array_unique($years + $filteryears);
                // Sort.
                if ($filteryears) {
                    asort($filteryears);
                }
                // Append 'ALL' to the start of the array.
                $filteryears = array($all => $all) + $filteryears;
                $chelper->set_filter_years($filteryears);
            } else {
                $selectedyear = null;
                $filteryears = array();
            }

            // If filter by period is enabled in the admin settings then build the array for the period
            // filter.
            if ($this->config->filterbyperiod) {
                $userpref = 'culcourse_listing_filter_period';
                $selectedperiod = $this->preferences[$userpref];

                if ($selectedperiod != $all) {
                    $filters[] = get_string($selectedperiod, 'block_culcourse_listing');
                    // Adds the selected period to the existing array (NB the year and period arrays have been
                    // filled by calling coursecat_subcategory_children() but the user may have set a preference
                    // that no longer exists so we need to add that).
                    $periods = array($selectedperiod => get_string($selectedperiod, 'block_culcourse_listing'));
                }

                $filterperiods = $chelper->get_filter_periods();
                // Remove duplicates.
                $filterperiods = array_unique($periods + $filterperiods);
                // Sort.
                if ($filterperiods) {
                    asort($filterperiods);
                }
                // Append 'ALL' to the start of the array.
                $filterperiods = array($all => $all) + $filterperiods;
                $chelper->set_filter_periods($filterperiods);
            } else {
                $selectedperiod = null;
                $filterperiods = array();
            }

            // Get two alternative strings for the heading. One is the heading when no filters are applied and
            // the other is the heading used to declare the filter applied. These are shown/hidden when the
            // filter is on/off.
            $header = get_string('mymodules', 'theme_cul_boost');
            $and = get_string('and', 'block_culcourse_listing');
            $filterstring = join($and, $filters);
            $divalert = get_string('divalert', 'block_culcourse_listing', $filterstring);
            $content .= html_writer::start_tag('div', array(
            'id' => 'allcoursesactions'));
            // $divalert .= '<i class="fa fa-hand-o-right"></i>';
            
            $heading = html_writer::tag('h2', $header, array('class' => 'allcourses m-0'));
            $heading = html_writer::link(
                '#',
                $heading,
                [
                    'class'=>'nav-link allcourses-link active',
                    'data-toggle'=>'tab',
                    'data-target' => '#' . $id,
                    'role'=>'tab',
                    'aria-expanded' => true
                ]
            );
            $content .= html_writer::tag('li', $heading, ['class'=>'nav-item']);

            $content .= html_writer::tag('div', $divalert, array('class' => 'divalert text-center text-white mb-3'));
            $hasexpandedcats = $chelper->get_has_expanded_categories();

            // If filter by year or filter by period are enabled in the admin settings
            // then print the filter form.
            if ($this->config->filterbyyear || $this->config->filterbyperiod) {
                $form = block_culcourse_listing_filter_form(
                    $this->config,
                    $filteryears,
                    $filterperiods,
                    $selectedyear,
                    $selectedperiod
                    );                
                $content .= html_writer::tag('div', $form, array('class' => 'filter'));
            }

            // If there are children of the top category then add the collapse/expand link.
            if ($coursecat->get_children_count()) {
                $classes = array(
                    'culcollapseexpand'
                );

                if ($hasexpandedcats) {
                    $classes[] = 'culcollapse-all';
                    $string = 'collapseall';
                } else {
                    $classes[] = 'hide';
                    $string = 'expandall';
                }

                $content .= html_writer::start_tag('div', array('class' => 'collapsible-actions'));
                $content .= html_writer::link('#', get_string($string),
                        array('class' => implode(' ', $classes)));
                $content .= html_writer::end_tag('div');
                $this->page->requires->strings_for_js(array('collapseall', 'expandall'), 'moodle');
            }

            $content .= html_writer::end_tag('div'); // End #allcoursesactions.
            $content .= html_writer::end_tag('div'); // End #allcourses.
            $content .= html_writer::tag('div', $categorycontent);
            $content .= html_writer::end_tag('div');
            $content .= html_writer::end_tag('div'); // End .course_category_tree.
        }

        return $content;
    }

}