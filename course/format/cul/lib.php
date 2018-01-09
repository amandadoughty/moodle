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
 * This file contains main class for the course format Topic
 *
 * @since     Moodle 2.0
 * @package   format_cul
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB, $COURSE;

require_once($CFG->dirroot. '/course/format/lib.php');
require_once($CFG->dirroot. '/course/format/topics/lib.php');
require_once($CFG->dirroot. '/course/format/weeks/lib.php');



define('FORMATTOPICS', 1);
define('FORMATWEEKS', 2);

// Get record from db or default
$record = $DB->get_record('course_format_options',
                        array('courseid' => $COURSE->id,
                              'format' => 'cul',
                              'name' => 'baseclass'
                            ), 'value');

if ($record) {
    $baseclass = $record->value;
} else {
    $config = get_settings('format_cul');
    $baseclass = $config->baseclass;
    // $baseclass = 'format_topics';
}




if ($baseclass == FORMATTOPICS) {
    class dynamic_parent extends format_topics {}
} else {
    class dynamic_parent extends format_weeks {}
}

/**
 * Main class for the Topics course format
 *
 * @package    format_cul
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_cul extends dynamic_parent {

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
                    'default' => 1,
                    'type' => PARAM_INT,
                ]
            ];
        }

        if ($foreditform && !isset($courseformatoptions['baseclass']['label'])) {
            $baseclasses = [
                1 => new lang_string('pluginname', 'format_topics'),
                2 => new lang_string('pluginname', 'format_weeks')
            ];

            $courseformatoptionsedit = [
                'baseclass' => [
                    'label' => new lang_string('baseclass', 'format_cul'),
                    'help' => 'baseclass',
                    'help_component' => 'format_cul',
                    'element_type' => 'select',
                    'element_attributes' => [$baseclasses]
                ]
            ];
            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        $pcourseformatoptions = parent::course_format_options($foreditform);
        $courseformatoptions = array_merge_recursive($pcourseformatoptions, $courseformatoptions);

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
        $elements = parent::create_edit_form_elements($mform, $forsection);

        // Weekly format unsets a key which leads to an error as the 
        // combined parent and child array have a gap in the key sequence.
        // /course/edit_form.php #373.
        // So we reindex the array.
        $elements = array_values($elements);

        return $elements;
    }    

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_cul_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'cul'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
