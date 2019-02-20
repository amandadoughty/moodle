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
 * Class containing data for CUL Course Listing block.
 *
 * @package    block_culcourse_listing
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_culcourse_listing\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Class containing data for myprofile block.
 *
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursebox implements renderable, templatable {

    /**
     * @var object An object 
     */
    protected $course;

    /**
     * @var bool 
     */
    protected $isfav;

    /**
     * Constructor.
     *
     * @param id/core_course_list_element $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @param string $move html for the move icons (only used for favourites) 
     * @param bool $isfav true if course has been fvourited/starred
     */
    public function __construct($course, $isfav = false) {
        // $this->chelper = $chelper;
        // $config = $config;
        // $preferences = $preferences;
        

        if ($course instanceof stdClass) {
            $course = new \core_course_list_element($course);
        }

        $this->course = $course;
        $this->isfav = $isfav;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;

        require_once($CFG->dirroot.'/blocks/culcourse_listing/locallib.php');

        $config = get_config('block_culcourse_listing');
        $preferences = block_culcourse_listing_get_preferences();
        $favourites = block_culcourse_listing_get_favourite_courses($preferences);
        $chelper = new \block_culcourse_listing_helper();        

        if ($config->filtertype == 'date') {
            global $DB;

            $daterangeperiods = $DB->get_records('block_culcourse_listing_prds');
        } else {
            $daterangeperiods = null;
        }                

        $data = new \stdClass();
        $data->cid = $this->course->id;
        $data->coursename = $chelper->get_course_formatted_name($this->course, $config); // @TODO static?                
        $data->type = \core_course_renderer::COURSECAT_TYPE_COURSE;       
        
        $data->isfav = $this->isfav;
        $move = [];

        if ($favourites && array_key_exists($this->course->id, $favourites)) {
            $data->action = 'remove';
            $data->favclass = 'gold fa fa-star';

            if ($data->isfav) {
                $move['spacer'] = $output->image_url('spacer', 'moodle')->out();
                $move['moveupimg'] = $output->image_url('t/up', 'moodle')->out();
                $move['movedownimg'] = $output->image_url('t/down', 'moodle')->out();
                $move['moveup'] = true;
                $move['movedown'] = true;

                if (array_shift($favourites) == $this->course->id) {
                    $move['moveup'] = false;
                }

                if (array_pop($favourites) == $this->course->id) {
                    $move['movedown'] = false;
                }
            }
        } else {
            $data->action = 'add';
            $data->favclass = 'fa fa-star-o';
        }

        $data->move = $move;
        $data->sesskey = sesskey();
        $data->ismoreinfo = false;
        $data->cannenrol = false;

         // The function to be used for testing if the course is filtered or not.
        $filterfunction = 'block_culcourse_listing_set_' . $config->filtertype . '_filtered_course';
        $year = block_culcourse_listing_get_filtered_year($config, $preferences);
        $period = block_culcourse_listing_get_filtered_period($config, $preferences);

        if (!$this->isfav) {
            $filtered = $filterfunction($course, $config, $year, $period, $daterangeperiods);
            // Hide the courses that don't match the filter settings.
            if (!$filtered) {
                $data->additionalclasses = ' hide';
            }
        }

        $filterfield = $config->filterfield;
        // The function to be used for getting the year and period for this course.
        $filtermetafunction = 'block_culcourse_listing_get_filter_meta_' . $config->filtertype;

        $filter = $filtermetafunction(
            $this->course,
            $config,
            $daterangeperiods
            );

        $data->year = $filter['year'];
        $data->period = $filter['period'];

        $classes = $this->course->visible ? '' : 'dimmed';
        $classes .= is_enrolled(\context_course::instance($this->course->id)) ? ' enrolled' : '';
        $data->classes = $classes;

        // Add the info icon link if  the course has summary text, course contacts
        // or summary files.
        if ($this->course->has_summary() || $this->course->has_course_contacts() || $this->course->has_course_overviewfiles()) {
            // // Make sure JS file to expand course content is included.
            // $output->coursecat_include_js(); // @TODO
            $data->ismoreinfo = true;
        }

        // Add enrolmenticons.
        if ($enrolmenticons = enrol_get_course_info_icons($this->course)) {
            $data->cannenrol = true;
            print_r($data->enrolmenticons);

            foreach ($enrolmenticons as $enrolmenticon) {
                // {{# pix }} does not like attribute pix.
                $icon = new \stdClass(0);
                $icon->icon = $enrolmenticon->pix;
                $icon->component = $enrolmenticon->component;
                $icon->attributes = $enrolmenticon->attributes;
                $data->enrolmenticons[] = $icon;
            }

        }

        // Add course summary text, contacts and files.
        $data->info = $output->coursecat_course_summary($chelper, $this->course);        

        return $data;
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
    public static function coursecat_course_summary(\block_culcourse_listing_helper $chelper, $course) {
        global $CFG;

        if ($chelper->get_show_courses() < \core_course_renderer::COURSECAT_SHOW_COURSES_EXPANDED) {
            return '';
        }

        if ($course instanceof stdClass) {
            $course = new \core_course_list_element($course);
        }

        $data = new \stdClass();

        $data->summary = false;
        $data->contentimages = [];
        $data->contentfiles = [];
        $data->iscontacts = false;
        $data->contacts = [];

        // Add course summary text.
        if ($course->has_summary()) {
            $data->summary = $chelper->get_course_formatted_summary($course,
                    array('overflowdiv' => true, 'noclean' => true, 'para' => false));
        }

        // Add course summary files.
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                $data->contentimages[] = ['url' => $url];
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
                $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                        html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
                $data->contentfiles[] = ['image' => $image, 'url' => $url, 'filename' => $filename];
            }
        }

        // Add course contacts.
        if ($course->has_course_contacts()) {
            $data->iscontacts = true;
            foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                $data->contacts[] = ['role' => $coursecontact['rolename'], 'userid' => $userid, 'username' => $coursecontact['username'], 'cid' => SITEID];
            }
        }

        return $data;
    }
}
