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
 * This file contains main class for local_culcourse_dashboard.
 *
 * @package   local_culcourse_dashboard
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB, $COURSE;

/**
 * Main class for the CUL Course course format
 *
 * @package    local_culcourse_dashboard
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_culcourse_dashboard extends format_base {

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * cul format uses the following options:
     * - baseclass
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) { 
        static $courseformatoptions = false;

        if ($courseformatoptions === false) {
            $courseformatoptions = [
                'baseclass' => [
                    'default' => get_config('local_culcourse_dashboard', 'defaultbaseclass'),
                    'type' => PARAM_INT,
                ],
                'showsectionsummary' => [
                    'default' => get_config('local_culcourse_dashboard', 'defaultshowsectionsummary'),
                    'type' => PARAM_INT,
                ]
            ];
        }

        // Splice in the dashboard options.
        $dashboard = new local_culcourse_dashboard_dashboard();
        $dashboard->set_dashboard_options($courseformatoptions);

        if ($foreditform && !isset($courseformatoptions['baseclass']['label'])) {
            $baseclasses = [
                1 => new lang_string('pluginname', 'format_topics'),
                2 => new lang_string('pluginname', 'format_weeks')
            ];

            $courseformatoptionsedit = [
                'baseclass' => [
                    'label' => new lang_string('baseclass', 'local_culcourse_dashboard'),
                    'help' => 'baseclass',
                    'help_component' => 'local_culcourse_dashboard',
                    'element_type' => 'select',
                    'element_attributes' => [$baseclasses]
                ],
                'showsectionsummary' => [
                    'label' => new lang_string('showsectionsummary', 'local_culcourse_dashboard'),
                    'help' => 'showsectionsummary',
                    'help_component' => 'local_culcourse_dashboard',
                    'element_type' => 'select',
                    'element_attributes' => [[
                        1 => new lang_string('no'),
                        2 => new lang_string('yes')
                    ]]
                ]
            ];

            // Splice in the dashboard edit options.
            $dashboard->set_dashboard_edit_options($courseformatoptionsedit);
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }        

        $args = func_get_args();
        $pcourseformatoptions = $this->call_base_function(__FUNCTION__, $args);
        $courseformatoptions = $pcourseformatoptions + $courseformatoptions;

        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form
     *
     * This function is called from {@link course_edit_form::definition_after_data()}
     *
     * Format singleactivity adds a warning when format of the course is about to be changed.
     *
     * @param MoodleQuickForm $mform form the elements are added to
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form
     * @return array array of references to the added form elements
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $PAGE;

        $args = func_get_args();
        $elements = $this->call_base_function(__FUNCTION__, [&$mform, $forsection]);
        // Weekly format unsets a key which leads to an error as the 
        // combined parent and child array have a gap in the key sequence.
        // /course/edit_form.php #373.
        // So we reindex the array.
        $elements = array_values($elements);

        if ($forsection == false) {
            global $USER;
  
            // Convert saved course_format_options value back to an array to set the value.
            if ($selectmoduleleaders = $mform->getElementValue('selectmoduleleaders')) {
                if (!is_array($selectmoduleleaders)) {
                    $mform->setDefault('selectmoduleleaders', explode(',', $selectmoduleleaders ));
                } else {
                    $mform->setDefault('selectmoduleleaders', $selectmoduleleaders);
                }
            }

            // Put module leader setting in own dropdown.
            $selectmoduleleaderhdr = $mform->addElement('header', 'selectmoduleleadershdr', get_string('setselectmoduleleadershdr', 'local_culcourse_dashboard'));
            $mform->addHelpButton('selectmoduleleadershdr', 'setselectmoduleleadershdr', 'local_culcourse_dashboard', '', true);
            array_splice($elements, -1, 0, [$selectmoduleleaderhdr]);

            // Put dashboard settings in own dropdown.
            $dashboardhdr = $mform->addElement('header', 'dashboardhdr', get_string('setdashboardhdr', 'local_culcourse_dashboard'));
            array_splice($elements, 4, 0, [$dashboardhdr]);
        }

        $PAGE->requires->js_call_amd('local_culcourse_dashboard/updatebaseclass', 'init');

        return $elements;
    }

   /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'weeks', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        $dashboard = new local_culcourse_dashboard_dashboard();
        $data = $dashboard->update_dashboard_options($data, $oldcourse);

        return $this->call_base_function(__FUNCTION__, [$data, $oldcourse]);
    }



