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

require_once($CFG->dirroot . '/course/renderer.php');

/**
 * The block culcourse listing renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('block_culcourse_listing');
 */
class block_culcourse_listing_renderer extends plugin_renderer_base {

    protected $config;
    protected $preferences;

    /**
     * Returns HTML to print tree with course categories and courses
     *
     * @return string
     */
    public function culcourse_listing(block_culcourse_listing_helper $chelper) {
        global $CFG;

        return $this->coursecat_tree($chelper, core_course_category::get(0));
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
                core_course_category::has_capability_on_any('moodle/course:view')
            )
        ) {
            $all = get_string('all', 'block_culcourse_listing');
            $filters = array();
            $years = array();
            $periods = array();
            $attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
            // Get the category content. Calling this function also sets the list of filtered
            // years and periods.
            $categorycontent = $this->coursecat_subcategory_children($chelper, $coursecat, 0);

            if (empty($categorycontent)) {
                return '';
            }

            $content .= html_writer::start_tag('div', $attributes); // Start .course_category_tree div.
            $content .= html_writer::start_tag('div', array(
            'id' => 'allcoursesheader'));

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
            $header = get_string('allcourses', 'block_culcourse_listing');
            $and = get_string('and', 'block_culcourse_listing');
            $filterstring = join($and, $filters);
            $divalert = get_string('divalert', 'block_culcourse_listing', $filterstring);
            // $divalert .= '<i class="fa fa-hand-o-right"></i>';
            $content .= html_writer::tag('h2', $header, array('class' => 'allcourses'));
            $content .= html_writer::tag('h2', $divalert, array('class' => 'divalert'));
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

            $content .= html_writer::end_tag('div'); // End #allcourses.
            $content .= html_writer::tag('div', $categorycontent);
            $content .= html_writer::end_tag('div'); // End .course_category_tree.
        }

        return $content;
    }

    /**
     * Returns HTML to display the subcategories and courses in the given category
     *
     * This method is re-used by AJAX to expand content of not loaded category
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of the category in the current tree
     * @return string
     */
    protected function coursecat_subcategory_children(block_culcourse_listing_helper $chelper, $coursecat, $depth) {
        $content = '';
        // Sets whether or not user can view courses in this category without
        // participation ie with a category or site role assignment.
        $viewcourses = $this->has_view_courses_capability($coursecat->get_context());
                $content = '';
        // Get the courses.
        $courses = array();
        $options = array();
        $options['sort'] = array('shortname' => 1, 'visible' => -1);
        $courses = $coursecat->get_courses($options);
        $content .= $this->coursecat_courses($chelper, $courses, $viewcourses, $coursecat->get_courses_count());
        // Get the subcategories.
        $content .= $this->coursecat_subcategories($chelper, $coursecat, $depth);

        return $content;
    }

    /**
     * Renders the list of subcategories in a category
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param coursecat $coursecat
     * @param int $depth depth of the category in the current tree
     * @return string
     */
    protected function coursecat_subcategories(block_culcourse_listing_helper $chelper, $coursecat, $depth) {
        global $CFG;

        $subcategories = array();
        $subcategories = $coursecat->get_children();
        $totalcount = $coursecat->get_children_count();

        // Checks if there are any subcategories to display.
        if (!$totalcount) {
            // Note that we call get_child_categories_count() AFTER get_child_categories() to avoid extra DB requests.
            // Categories count is cached during children categories retrieval.
            return '';
        }

        $content = html_writer::start_tag('div', array('class' => 'subcategories'));

        foreach ($subcategories as $subcategory) {
            $content .= $this->coursecat_subcategory($chelper, $subcategory, $depth);
        }

        $content .= html_writer::end_tag('div');

        return $content;
    }

    /**
     * Returns HTML to display a course category as a part of a tree
     *
     * This is an internal function, to display a particular category and all its contents.
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param core_course_category $coursecat
     * @param int $depth depth of this category in the current tree
     * @return string
     */
    protected function coursecat_subcategory(block_culcourse_listing_helper $chelper, $coursecat, $depth) {
        $content = '';
        // Sets whether or not user can view courses in this category without
        // participation ie with a category or site role assignment.
        $view = $this->has_view_courses_capability($coursecat->get_context());
                $content = '';
        // Sets whether or not user can view courses in child categories of this
        // category without participation ie with a category or site role assignment.
        $viewchild = $this->has_view_courses_capability_on_child($coursecat);

        // Check if category is visible to the user.
        if ($coursecat->is_uservisible() &&
            (
                $coursecat->id == 0 ||
                array_key_exists($coursecat->id, $chelper->get_my_categories()) ||
                $view ||
                $viewchild
            )
        ) {
            $classes = array('culcategory');
            if (empty($coursecat->visible)) {
                $classes[] = 'dimmed_category';
            }

            // Generating all of the content for a category causes very slow loading times
            // so this is done using ajax instead. Those with course level enrolments do get
            // the expanded content displayed.
            if ($view) {
                // Content is not loaded but the container is loaded with indicative class
                // attributes.
                $categorycontent = '';
                $classes[] = 'manage notloaded';

                if ($coursecat->get_children_count() ||
                    (
                        $chelper->get_show_courses() >= core_course_renderer::COURSECAT_SHOW_COURSES_COLLAPSED &&
                        $coursecat->get_courses_count())
                    ) {
                    $classes[] = 'with_children';
                    $classes[] = 'collapsed';
                }
                // Make sure JS file to expand category content is included.
                $this->coursecat_include_js();
            } else {
                // Load category content.
                if ($viewchild) {
                    $classes[] = 'manage';
                }
                // Tell block that there ae expanded categories.
                $chelper->set_has_expanded_categories(true);
                $categorycontent = $this->coursecat_subcategory_children($chelper, $coursecat, $depth);
                $classes[] = 'loaded';
                if (!empty($categorycontent)) {
                    $classes[] = 'with_children';
                }
            }

            $filter = $chelper->get_filtered_category_ids();

            // Hide the categories that are filtered out.
            if (!$view && isset($filter[$coursecat->id]) && !$filter[$coursecat->id]) {
                $classes[] = 'hide';
            }

            $content = html_writer::start_tag('div', array(
                'class' => join(' ', $classes),
                'data-categoryid' => $coursecat->id,
                'data-depth' => $depth,
                'data-showcourses' => $chelper->get_show_courses(),
                'data-type' => core_course_renderer::COURSECAT_TYPE_CATEGORY,
            ));

            // Category name.
            $categoryname = $coursecat->get_formatted_name();
            $viewcategoryurl = new moodle_url(' /course/index.php', array('categoryid' => $coursecat->id));
            $categorylink = html_writer::link(
                $viewcategoryurl,
                $categoryname,
                array('class' => 'overviewlink', 'title' => $categoryname)
                );
            $content .= html_writer::start_tag('div', array('class' => 'info'));
            $content .= html_writer::tag(($depth > 1) ? 'h4' : 'h3', $categorylink, array('class' => 'categoryname'));

            // Add category editing link for those with capability.
            if ($view) {
                $editcategoryurl = new moodle_url(' /course/index.php', array('categoryid' => $coursecat->id, 'categoryedit' => 1));
                $icon = html_writer::tag('i', '', array('class' => 'red fa fa-pencil'));
                $title = get_string('editcategory', 'block_culcourse_listing');
                $content .= html_writer::link($editcategoryurl, $icon, array('class' => 'overviewlink', 'title' => $title));
            }

            $content .= html_writer::end_tag('div'); // End .info.

            // Add category content.
            $content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));
            $content .= html_writer::end_tag('div'); // End .category.
        }

        return $content;
    }

    /**
     * Renders the list of courses
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param array $courses the list of courses to display
     * @param int|null $totalcount total number of courses (affects display mode if it is AUTO or pagination if applicable),
     *     defaulted to count($courses)
     * @return string
     */
    protected function coursecat_courses(block_culcourse_listing_helper $chelper, $courses, $viewcourses, $totalcount = null) {
        global $CFG;

        // Check if there are any courses to display.
        if ($totalcount === null) {
            $totalcount = count($chelper->get_my_courses());
        }

        if (!$totalcount) {
            // Courses count is cached during courses retrieval.
            return '';
        }

        // The function to be used for setting the arrays of filtered years and
        // periods.
        $filterlistfunction = 'block_culcourse_listing_get_filter_list_' . $this->config->filtertype;
        $years = array();
        $periods = array();
        $attributes = $chelper->get_and_erase_attributes('courses');
        $content = html_writer::start_tag('div', $attributes);
        $coursecount = 0;

        foreach ($courses as $course) {
            // Check if users has course level enrollment.
            $enrolled = array_key_exists($course->id, $chelper->get_my_courses());

            if ($viewcourses || $enrolled) {
                // Add course year and period to array for use in the filter form dropdown lists.
                // (For those with category role assignments, the dropdown list will be updated
                // with every ajax call generated by expanding a category.)
                // $years and $periods are passed by reference.
                $filterlistfunction($course, $this->config, $years, $periods, $chelper->get_daterange_periods());
                $coursecount ++;
                $classes = ($coursecount % 2) ? 'odd' : 'even';

                if ($coursecount == 1) {
                    $classes .= ' first';
                }

                if ($coursecount >= count($courses)) {
                    $classes .= ' last';
                }

                $content .= $this->coursecat_course($chelper, $course, $classes);
            }
        }

        $filteryears = $chelper->get_filter_years();
        $filterperiods = $chelper->get_filter_periods();
        $filteryears = $filteryears + $years;
        $filterperiods = $filterperiods + $periods;
        $chelper->set_filter_years($filteryears);
        $chelper->set_filter_periods($filterperiods);
        $content .= html_writer::end_tag('div'); // End .courses.

        return $content;
    }

    /**
     * Displays one course in the list of courses.
     *
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @param string $move html for the move icons (only used for favourites)
     * @return string
     */
    protected function coursecat_course(block_culcourse_listing_helper $chelper, $course, $additionalclasses = '', $isfav = false) {
        global $CFG;

        // Check if course exists.
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        if (!$course instanceof core_course_list_element) {
            return '';
        }

        // // The function to be used for testing if the course is filtered or not.
        // $filterfunction = 'block_culcourse_listing_set_' . $this->config->filtertype . '_filtered_course';
        // $year = block_culcourse_listing_get_filtered_year($this->config, $this->preferences);
        // $period = block_culcourse_listing_get_filtered_period($this->config, $this->preferences);

        // if (!$isfav) {
        //     $filtered = $filterfunction($course, $this->config, $year, $period, $chelper->get_daterange_periods());
        //     // Hide the courses that don't match the filter settings.
        //     if (!$filtered) {
        //         $additionalclasses .= ' hide';
        //     }
        // }

        // $filterfield = $this->config->filterfield;
        // // The function to be used for getting the year and period for this course.
        // $filtermetafunction = 'block_culcourse_listing_get_filter_meta_' . $this->config->filtertype;

        // $filter = $filtermetafunction(
        //     $course,
        //     $this->config,
        //     $chelper->get_daterange_periods()
        //     );

        // $content = '';
        // $classes = trim('culcoursebox clearfix panel panel-default '. $additionalclasses);
        // $classes .= ' collapsed';
        // $content .= html_writer::start_tag('div', array(
        //     'class' => $classes,
        //     'data-courseid' => $course->id,
        //     'data-type' => core_course_renderer::COURSECAT_TYPE_COURSE,
        //     'data-year' => $filter['year'],
        //     'data-period' => $filter['period']
        // ));

        // $classes = $course->visible ? '' : 'dimmed';
        // $classes .= is_enrolled(context_course::instance($course->id)) ? ' enrolled' : '';
        // $classes .= ' info panel-heading';
        // $content .= html_writer::start_tag('div', array('class' => $classes));
        // $content .= html_writer::start_tag('div', array('class' => 'coursename_wrapper'));

        // // Add move icons if renderering a course in the favourites list.
        // $content .= $move;

        // // Add course name.
        // $coursename = $chelper->get_course_formatted_name($course, $this->config);
        // $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
        //                                     $coursename, array('title' => $course->shortname));
        // $content .= html_writer::tag('div', $coursenamelink, array('class' => 'coursename'));
        // $content .= html_writer::end_tag('div');
        // $content .= html_writer::start_tag('div', array('class' => 'moreinfo'));

        // // Add the info icon link if  the course has summary text, course contacts
        // // or summary files.
        // if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()) {
        //     $url = new moodle_url('/course/info.php', array('id' => $course->id));
        //     $image = html_writer::empty_tag('img', array('src' => $this->output->image_url('i/info'),
        //         'alt' => get_string('summary')));
        //     $content .= html_writer::link($url, $image, array('title' => get_string('summary')));
        //     // Make sure JS file to expand course content is included.
        //     $this->coursecat_include_js();
        // }

        // $content .= html_writer::end_tag('div');

        // // Add favourite link.
        // $favourites = $chelper->get_favourites();

        // if ($favourites && array_key_exists($course->id, $favourites)) {
        //     $action = 'remove';
        //     $favclass = 'gold fa fa-star';
        // } else {
        //     $action = 'add';
        //     $favclass = 'fa fa-star-o';
        // }

        // $favouriteurl = new moodle_url($CFG->wwwroot. '/blocks/culcourse_listing/favourite_post.php',
        //         array('action' => $action, 'cid' => $course->id, 'sesskey' => sesskey()));
        // $favouriteicon = html_writer::tag('i', '', array('class' => $favclass));
        // $content .= html_writer::link(
        //     $favouriteurl,
        //     $favouriteicon, array(
        //         'class' => ' favouritelink favouritelink_' . $course->id,
        //         'title' => get_string("favourite$action", 'block_culcourse_listing')
        //         )
        //     );

        // // Add enrolmenticons.
        // if ($icons = enrol_get_course_info_icons($course)) {
        //     $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
        //     foreach ($icons as $pixicon) {
        //         $content .= $this->render($pixicon);
        //     }
        //     $content .= html_writer::end_tag('div');
        // }

        // $content .= html_writer::end_tag('div'); // End .panel-heading.
        // $content .= html_writer::start_tag('div', array('class' => 'content panel-body'));

        // // Add course summary text, contacts and files.
        // $content .= $this->coursecat_course_summary($chelper, $course);
        // $content .= html_writer::end_tag('div'); // End .panel-body.
        // $content .= html_writer::end_tag('div'); // End .panel.

        // return $content;



        // Use renderable @TODO
        // $renderable = new \block_myprofile\output\coursebox($this->config);
        // $renderer = $this->page->get_renderer('block_myprofile');
        // $content = $renderer->render($renderable);

        $coursebox = new \block_culcourse_listing\output\coursebox($chelper, $this->config, $this->preferences, $course, $additionalclasses, $isfav);
        $content = $this->render_from_template('block_culcourse_listing/coursebox', $coursebox->export_for_template($this));

        return $content;
    }

    /**
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_course() and may be re-used in AJAX
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param stdClass|core_course_list_element $course
     * @return string
     */
    public function coursecat_course_summary(block_culcourse_listing_helper $chelper, $course) {
        global $CFG;

        if ($chelper->get_show_courses() < core_course_renderer::COURSECAT_SHOW_COURSES_EXPANDED) {
            return '';
        }

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $content = '';

        // Add course summary text.
        if ($course->has_summary()) {
            $content .= html_writer::start_tag('div', array('class' => 'summary'));
            $content .= $chelper->get_course_formatted_summary($course,
                    array('overflowdiv' => true, 'noclean' => true, 'para' => false));
            $content .= html_writer::end_tag('div'); // End .summary.
        }

        // Add course summary files.
        $contentimages = $contentfiles = '';

        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                $contentimages .= html_writer::tag('div',
                        html_writer::empty_tag('img', array('src' => $url)),
                        array('class' => 'courseimage'));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
                $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                        html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
                $contentfiles .= html_writer::tag('span',
                        html_writer::link($url, $filename),
                        array('class' => 'coursefile fp-filename-icon'));
            }
        }

        $content .= $contentimages. $contentfiles;

        // Add course contacts.
        if ($course->has_course_contacts()) {
            $content .= html_writer::start_tag('ul', array('class' => 'teachers'));
            foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                $name = $coursecontact['rolename'].': '.
                        html_writer::link(new moodle_url('/user/view.php',
                                array('id' => $userid, 'course' => SITEID)),
                            $coursecontact['username']);
                $content .= html_writer::tag('li', $name);
            }
            $content .= html_writer::end_tag('ul'); // End .teachers.
        }

        return $content;
    }

    /**
     * Make sure that javascript file for AJAX expanding of courses and categories
     * content is included.
     */
    protected function coursecat_include_js() {
        static $jsloaded = false;

        if (!$jsloaded) {
            // We must only load this module once.
            $this->page->requires->yui_module(
                'moodle-block_culcourse_listing-category',
                'M.blocks_culcourse_listing.init_category',
                array(array('config' => $this->config))
                );
            $jsloaded = true;
        }
    }

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
        $id = html_writer::random_id('favourites');
        // Start content generation.
        $content = html_writer::start_tag('div', array('id' => $id, 'class' => 'favourites'));

        // Header.
        $content .= html_writer::start_tag('div', array('id' => 'favouritesheader'));
        $content .= $this->output->heading(
            get_string('favourites', 'block_culcourse_listing'),
            2,
            'culcourse_listing'
            );
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
     * Container html for list of favourites
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param array $courses list of favourite courses in sorted order
     * @param string $prefix identifies the list
     * @return string $content
     */
    public function favourites(block_culcourse_listing_helper $chelper, $courses, $prefix = 'course_') {
        global $USER, $CFG;
        $nofavourites = '';

        if (!$courses) {
            $nofavourites = get_string('nofavourites', 'block_culcourse_listing');
        }

        $content = '';
        $content .= html_writer::start_tag('div', array('class' => $prefix . 'list'));

        // Add no favourites message.
        $content .= html_writer::tag('span', $nofavourites);

        // Add courses with drag and drop hml prepended.
        if ($courses) {
            $content .= $this->drag_drop($chelper, $courses, $prefix);
        }

        $content .= html_writer::end_tag('div');
        return $content;
    }

    /**
     * Drag and drop html for favourite courses @TODO template
     *
     * @param block_culcourse_listing_helper $chelper various display options
     * @param array $courses list of favourite courses in sorted order
     * @param string $prefix identifies the list
     * @return string $content
     */
    public function drag_drop(block_culcourse_listing_helper $chelper, $courses, $prefix='course_') {
        $content = '';
        $courseordernumber = 0;
        $maxcourses = count($courses);
        // Intialize string/icon etc.
        $url = null;
        $moveicon = null;
        $moveup[] = null;
        $movedown[] = null;
        $url = new moodle_url('/blocks/culcourse_listing/move_post.php', array('sesskey' => sesskey()));
        $moveup['str'] = get_string('moveup');
        $moveup['icon'] = $this->image_url('t/up');
        $movedown['str'] = get_string('movedown');
        $movedown['icon'] = $this->image_url('t/down');

        foreach ($courses as $course) {
            $caption = '';

            if (!is_null($url)) {
                // Add course id to move link.
                $url->param('source', $course->id);
                $caption .= html_writer::start_tag('div', array('class' => 'moveicons'));
                // Add an arrow to move course up.
                if ($courseordernumber > 0) {
                    $url->param('move', -1);
                    $caption .= html_writer::link($url,
                    html_writer::empty_tag('img', array('src' => $moveup['icon'],
                        'class' => 'up', 'alt' => $moveup['str'])),
                        array('title' => $moveup['str'], 'class' => 'moveup'));
                } else {
                    // Add a spacer to keep keep down arrow icons at right position.
                    $caption .= html_writer::empty_tag('img', array('src' => $this->image_url('spacer'),
                        'class' => 'movedownspacer'));
                }
                // Add an arrow to move course down.
                if ($courseordernumber <= $maxcourses - 2) {
                    $url->param('move', 1);
                    $caption .= html_writer::link($url, html_writer::empty_tag('img',
                        array('src' => $movedown['icon'], 'class' => 'down', 'alt' => $movedown['str'])),
                        array('title' => $movedown['str'], 'class' => 'movedown'));
                } else {
                    // Add a spacer to keep keep up arrow icons at right position.
                    $caption .= html_writer::empty_tag('img', array('src' => $this->image_url('spacer'),
                        'class' => 'moveupspacer'));
                }
                $caption .= html_writer::end_tag('div');
            }

            // Add the course html.
            $content .= $this->coursecat_course($chelper, $course, '', true);
            $courseordernumber++;
        }

        return $content;
    }

    /**
     * Serves requests to /course/category_ajax.php
     *
     * In this renderer implementation it may expand the category content or
     * course content.
     *
     * @return string
     * @throws coding_exception
     */
    public function coursecat_ajax($chelper) {
        global $DB, $CFG;

        $type = required_param('type', PARAM_INT);
        // The json endcoded stringified select options from filter form.
        $years = optional_param('years', '', PARAM_TEXT);
        $periods = optional_param('periods', '', PARAM_TEXT);

        $content = '';
        $filterform = '';
        $favourites = block_culcourse_listing_get_favourite_courses($this->preferences);

        if ($type === core_course_renderer::COURSECAT_TYPE_CATEGORY) {
            // This is a request for a category list of some kind.
            $categoryid = required_param('categoryid', PARAM_INT);
            $showcourses = required_param('showcourses', PARAM_INT);
            $depth = required_param('depth', PARAM_INT);
            $category = coursecat::get($categoryid);
            $chelper->set_show_courses($showcourses);
            $chelper->set_favourites($favourites);

            // Get content.
            $content = $this->coursecat_subcategory_children($chelper, $category, $depth);

            // Get settings.
            $userpref = 'culcourse_listing_filter_year';
            $selectedyear = $this->preferences[$userpref];
            $userpref = 'culcourse_listing_filter_period';
            $selectedperiod = $this->preferences[$userpref];

            // Get filter form.
            $filteryears = array();
            $filterperiods = array();

            if ($this->config->filterbyyear) {
                $filteryears = $chelper->get_filter_years();
                $years = (array) json_decode($years);
                $filteryears = array_unique($years + $filteryears);

                if ($filteryears) {
                    asort($filteryears);
                }
            }

            if ($this->config->filterbyperiod) {
                $filterperiods = $chelper->get_filter_periods();
                $periods = (array) json_decode($periods);
                $filterperiods = array_unique($periods + $filterperiods);

                if ($filterperiods) {
                    asort($filterperiods);
                }
            }

            if ($this->config->filterbyyear || $this->config->filterbyperiod) {
                $all = get_string('all', 'block_culcourse_listing');
                $filteryears = array($all => $all) + $filteryears;
                $filterperiods = array($all => $all) + $filterperiods;
                $chelper->set_filter_years($filteryears);
                $chelper->set_filter_periods($filterperiods);
                $filterform = block_culcourse_listing_filter_form(
                    $this->config,
                    $filteryears,
                    $filterperiods,
                    $selectedyear,
                    $selectedperiod
                    );
            }

            // Return HTML for the category and the updated filter form.
            return array('content' => $content, 'filterform' => $filterform);

        } else if ($type === core_course_renderer::COURSECAT_TYPE_COURSE) {
            // This is a request for the course information.
            $courseid = required_param('courseid', PARAM_INT);

            try {
                $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
            } catch (exception $e) {
                throw new coding_exception('Invalid course id');
            }

            $chelper = new block_culcourse_listing_helper();
            $chelper->set_show_courses(core_course_renderer::COURSECAT_SHOW_COURSES_EXPANDED);
            $chelper->set_favourites($favourites);

            // $coursebox = new \block_culcourse_listing\output\coursebox($chelper, $this->config, $this->preferences, $course, $additionalclasses, $isfav);
            $content = $this->coursecat_course_summary($chelper, $course);

            // Return HTML for the course summary.
            return array('content' => $content);
        } else {
            throw new coding_exception('Invalid request type');
        }
    }

    /***** Utility functions *****/

    /**
     * Sets the block admin settings.
     *
     * @param array $preferences
     */
    public function set_config($config) {
        $this->config = $config;
    }

    /**
     * Sets the user preferences for the state of the filters.
     * @param array $preferences
     */
    public function set_preferences($preferences) {
        $this->preferences = $preferences;
    }

    /**
     * Returns the user preferences for the state of the filters.
     *
     * @return array
     */
    public function get_preferences() {
        return $this->preferences;
    }

    /**
     * Returns true if the user is able to view courses without participation
     * in any of the children of this category.
     * @return bool
     */
    public function has_view_courses_capability_on_child($coursecat) {
        global $CFG, $DB;

        $context = $coursecat->get_context();
        // Check all child categories (not only direct children).
        require_once($CFG->libdir . '/accesslib.php');
        $childcategories = $DB->get_records_sql(
                ' SELECT * '.
                ' FROM {context} ctx '.
                ' JOIN {course_categories} c ON c.id = ctx.instanceid'.
                ' WHERE ctx.path like ? AND ctx.contextlevel = ?',
                    array($context->path. '/%', CONTEXT_COURSECAT)
            );
        foreach ($childcategories as $childcat) {
            $childcontext = context_coursecat::instance($childcat->id);
            if (has_capability('moodle/course:view', $childcontext)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if the user is able to view courses without participation
     * in this category.
     * @return bool
     */
    public function has_view_courses_capability($context) {
        return has_capability('moodle/course:view', $context);
    }

}

/**
 * Class storing display options and functions to help display course category
 * and/or courses lists
 *
 * @package    block_culcourse_listing
 * @copyright  2014 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_culcourse_listing_helper {
    /** @var string [none, collapsed, expanded] how (if) display courses list */
    protected $showcourses = 10; /* core_course_renderer::COURSECAT_SHOW_COURSES_COLLAPSED */
    /** @var array options to display courses list */
    protected $coursesdisplayoptions = array();
    /** @var array additional HTML attributes */
    protected $attributes = array();
    /** @var array of stdClass objects courses with enrollments or role assignments */
    protected $mycourses;
    /** @var array  */
    protected $mycategories;
    /** @var array of stdClass objects favourite courses */
    protected $favourites;
    /** @var array   */
    protected $filteredcategoryids;
    /** @var array  */
    protected $filteryears = array();
    /** @var array  */
    protected $filterperiods = array();
    /** @var array  */
    protected $terms = array();
    /** @var array  */
    protected $daterangeperiods = array();
    /** @var bool if the category has expanded categories  */
    protected $expandedcategories = false;

    /**
     * Sets how (if) to show the courses - none, collapsed, expanded, etc.
     *
     * @param int $showcourses SHOW_COURSES_NONE, SHOW_COURSES_COLLAPSED, SHOW_COURSES_EXPANDED, etc.
     * @return block_culcourse_listing_helper
     */
    public function set_show_courses($showcourses) {
        $this->showcourses = $showcourses;
        // Automatically set the options to preload summary and course contacts.
        $this->coursesdisplayoptions['summary'] = $showcourses >= core_course_renderer::COURSECAT_SHOW_COURSES_AUTO;
        $this->coursesdisplayoptions['coursecontacts'] = $showcourses >= core_course_renderer::COURSECAT_SHOW_COURSES_EXPANDED;
        return $this;
    }

    /**
     * Returns how (if) to show the courses - none, collapsed, expanded, etc.
     *
     * @return int - COURSECAT_SHOW_COURSES_NONE, COURSECAT_SHOW_COURSES_COLLAPSED, COURSECAT_SHOW_COURSES_EXPANDED, etc.
     */
    public function get_show_courses() {
        return $this->showcourses;
    }

    /**
     * Sets additional general options to pass between renderer functions, usually HTML attributes
     *
     * @param array $attributes
     * @return block_culcourse_listing_helper
     */
    public function set_attributes($attributes) {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Return all attributes and erases them so they are not applied again
     *
     * @param string $classname adds additional class name to the beginning of $attributes['class']
     * @return array
     */
    public function get_and_erase_attributes($classname) {
        $attributes = $this->attributes;
        $this->attributes = array();
        if (empty($attributes['class'])) {
            $attributes['class'] = '';
        }
        $attributes['class'] = $classname . ' '. $attributes['class'];
        return $attributes;
    }

    /**
     * Sets the array of the courses the user is enrolled in.
     *
     * @param array $mycourses
     * @return array
     */
    public function set_my_courses($mycourses) {
        $this->mycourses = $mycourses;
        return $this;
    }

    /**
     * Returns the array of the courses the user is enrolled in.
     *
     * @return array of stdClass courses with course id as key.
     */
    public function get_my_courses() {
        return $this->mycourses;
    }

    /**
     * Sets the array of categories containing courses the user is enrolled in.
     *
     * @param array $mycategories
     * @return array
     */
    public function set_my_categories($mycategories) {
        $this->mycategories = $mycategories;
        return $this;
    }

    /**
     * Returns the array of categories containing courses the user is enrolled in.
     *
     * @return array of core_course_category categories with category id as key.
     */
    public function get_my_categories() {
        return $this->mycategories;
    }

    /**
     * Sets the array of courses the user has added to their favourites.
     *
     * @param array $favourites
     * @return array
     */
    public function set_favourites($favourites) {
        $this->favourites = $favourites;
        return $this;
    }

    /**
     * Returns the array of courses the user has added to their favourites.
     *
     * @return array
     */
    public function get_favourites() {
        return $this->favourites;
    }

    /**
     * Sets the array of states for category filtering.
     *
     * @param array $filteredcategoryids
     * @return array of core_course_list_element favourites with course id as key.
     */
    public function set_filtered_category_ids($filteredcategoryids) {
        $this->filteredcategoryids = $filteredcategoryids;
        return $this;
    }

    /**
     * Returns the array of states for category filtering.
     *
     * @return array of category ids as keys with a value of on/off (0/1)
     */
    public function get_filtered_category_ids() {
        return $this->filteredcategoryids;
    }

    /**
     * Sets the list of years in the filter form select list.
     *
     * @param array $filteryears
     * @return array
     */
    public function set_filter_years($filteryears) {
        $this->filteryears = $filteryears;
        return $this;
    }

    /**
     * Returns the list of years in the filter form select list.
     *
     * @return array
     */
    public function get_filter_years() {
        return $this->filteryears;
    }

    /**
     * Sets the list of periods in the filter form select list.
     *
     * @param array $filterperiods
     * @return array
     */
    public function set_filter_periods($filterperiods) {
        $this->filterperiods = $filterperiods;
        return $this;
    }

    /**
     * Returns the list of periods in the filter form select list.
     *
     * @return array
     */
    public function get_filter_periods() {
        return $this->filterperiods;
    }

    /**
     * Sets the list of periods in the block_culcourse_listing_prds table.
     *
     * @param array $filterperiods
     * @return array
     */
    public function set_daterange_periods($daterangeperiods) {
        $this->daterangeperiods = $daterangeperiods;
        return $this;
    }

    /**
     * Returns the list of periods in the block_culcourse_listing_prds table.
     *
     * @return array
     */
    public function get_daterange_periods() {
        return $this->daterangeperiods;
    }    

    /**
     * Sets flag indicating whether category has expanded subcategories.
     *
     * @param bool $expandedcategories
     */
    public function set_has_expanded_categories($expandedcategories) {
        $this->expandedcategories = $expandedcategories;
        return $this;
    }

    /**
     * Returns lag indicating whether category has expanded subcategories
     *
     * @return bool
     */
    public function get_has_expanded_categories() {
        return $this->expandedcategories;
    }

    /**
     * Returns given course's summary with proper embedded files urls and formatted
     *
     * @param core_course_list_element $course
     * @param array|stdClass $options additional formatting options
     * @return string
     */
    public function get_course_formatted_summary($course, $options = array()) {
        global $CFG;
        require_once($CFG->libdir. '/filelib.php');
        if (!$course->has_summary()) {
            return '';
        }
        $options = (array)$options;
        $context = context_course::instance($course->id);
        $summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', null);
        $summary = format_text($summary, $course->summaryformat, $options, $course->id);
        if (!empty($this->searchcriteria['search'])) {
            $summary = highlight($this->searchcriteria['search'], $summary);
        }
        return $summary;
    }

    /**
     * Returns course name as it is configured to appear in courses lists formatted to
     * course context
     *
     * @param core_course_list_element $course
     * @param array|stdClass $options additional formatting options
     * @return string
     */
    public function get_course_formatted_name($course, $config, $options = array()) {
        $options = (array)$options;

        if (!isset($options['context'])) {
            $options['context'] = context_course::instance($course->id);
        }

        $displayname = $config->displayname;
        $name = !empty($course->$displayname) ? $course->$displayname : get_course_display_name_for_list($course);
        $name = format_string($name, true, $options);

        return $name;
    }
}
