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
// require_once($CFG->dirroot. '/course/format/cul/lib_trait.php');

define('FORMATTOPICS', 1);
define('FORMATWEEKS', 2);

// // Get record from db or default
// $record = $DB->get_record('course_format_options',
//                         array('courseid' => $COURSE->id,
//                               'format' => 'cul',
//                               'name' => 'baseclass'
//                             ), 'value');

// if ($record) {
//     $baseclass = $record->value;
// } else {
//     $config = get_config('format_cul');
//     $baseclass = $config->baseclass;
// }




// if ($baseclass == FORMATTOPICS) {
//     class dynamic_parent extends format_topics {}
// } else {
//     class dynamic_parent extends format_weeks {}
// }

/**
 * Main class for the Topics course format
 *
 * @package    format_cul
 * @copyright  2012 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_cul extends format_base {



    /** @var string baseformat used for this course. Please note that it can be different from
     * course.format field if course referes to non-existing or disabled format */
    protected $baseclass;

    /**
     * Creates a new instance of class
     *
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     * @return format_base
     */
    protected function __construct($format, $courseid) {        
        // define('FORMATTOPICS', 1);
        // define('FORMATWEEKS', 2);

        global $DB;

        // $baseclasses = [
        //     1 => 'format_cultopics',
        //     2 => 'format_culweeks'
        // ];

        // Get record from db or default
        $record = $DB->get_record('course_format_options',
                                array('courseid' => $courseid,
                                      'format' => 'cul',
                                      'name' => 'baseclass'
                                    ), 'value');

        if ($record) {
            $this->baseclass = $record->value;
        } else {
            $config = get_config('format_cul');
            $this->baseclass = $config->baseclass;
        }

        // $this->baseclass = get_format_or_default($baseclasses[$baseclass]);

        // parent::__construct($format, $courseid);

        // $extendedclassname = $baseclasses[$baseclassid];

        // return new $extendedclassname($format, $courseid);
    }

// }

// class format_culweeks extends format_weeks { 
//     use culformat;
// }

// class format_cultopics extends format_topics { 
//     use culformat;    



    /**
     * Returns the display name of the given section that the course prefers.
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     * @return Display name that the course format prefers, e.g. "Topic 2"
     */
    public function get_section_name($section) {
        if (is_object($section)) {
            $sectionnum = $section->section;
        } else {
            $sectionnum = $section;
        }

        //@TODO

        if (get_string_manager()->string_exists('sectionname', 'format_' . $this->format)) {
            return get_string('sectionname', 'format_' . $this->format) . ' ' . $sectionnum;
        }

        // Return an empty string if there's no available section name string for the given format.
        return '';
    }

    /**
     * Returns the default section using format_base's implementation of get_section_name.
     *
     * @param int|stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name based on the given course format.
     */
    public function get_default_section_name($section) {

        // @TODO
        return self::get_section_name($section);
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        // no support by default
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = false;
        return $ajaxsupport;
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {


        // @TODO
        return null;
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * Please note that course view page /course/view.php?id=COURSEID is hardcoded in many
     * places in core and contributed modules. If course format wants to change the location
     * of the view script, it is not enough to change just this function. Do not forget
     * to add proper redirection.
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if null the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;

        // @TODO
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        if (array_key_exists('sr', $options)) {
            $sectionno = $options['sr'];
        } else if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if (empty($CFG->linkcoursesections) && !empty($options['navigation']) && $sectionno !== null) {
            // by default assume that sections are never displayed on separate pages
            return null;
        }
        if ($this->uses_sections() && $sectionno !== null) {
            $url->set_anchor('section-'.$sectionno);
        }
        return $url;
    }

    /**
     * Loads all of the course sections into the navigation
     *
     * This method is called from {@link global_navigation::load_course_sections()}
     *
     * By default the method {@link global_navigation::load_generic_course_sections()} is called
     *
     * When overwriting please note that navigationlib relies on using the correct values for
     * arguments $type and $key in {@link navigation_node::add()}
     *
     * Example of code creating a section node:
     * $sectionnode = $node->add($sectionname, $url, navigation_node::TYPE_SECTION, null, $section->id);
     * $sectionnode->nodetype = navigation_node::NODETYPE_BRANCH;
     *
     * Example of code creating an activity node:
     * $activitynode = $sectionnode->add($activityname, $action, navigation_node::TYPE_ACTIVITY, null, $activity->id, $icon);
     * if (global_navigation::module_extends_navigation($activity->modname)) {
     *     $activitynode->nodetype = navigation_node::NODETYPE_BRANCH;
     * } else {
     *     $activitynode->nodetype = navigation_node::NODETYPE_LEAF;
     * }
     *
     * Also note that if $navigation->includesectionnum is not null, the section with this relative
     * number needs is expected to be loaded
     *
     * @param global_navigation $navigation
     * @param navigation_node $node The course node within the navigation
     */
    public function extend_course_navigation($navigation, navigation_node $node) {

        // @TODO
        if ($course = $this->get_course()) {
            $navigation->load_generic_course_sections($course, $node);
        }
        return array();
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @see blocks_add_default_course_blocks()
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {

        // @TODO
        global $CFG;
        if (isset($CFG->defaultblocks)) {
            return blocks_parse_default_blocks_list($CFG->defaultblocks);
        }
        $blocknames = array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array()
        );
        return $blocknames;
    }

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
        $elements = parent::create_edit_form_elements($mform, $forsection);

        // Weekly format unsets a key which leads to an error as the 
        // combined parent and child array have a gap in the key sequence.
        // /course/edit_form.php #373.
        // So we reindex the array.
        $elements = array_values($elements);

        return $elements;
    }    

    /**
     * Returns true if the specified section is current
     *
     * By default we analyze $course->marker
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function is_section_current($section) {
        if (is_object($section)) {
            $sectionnum = $section->section;
        } else {
            $sectionnum = $section;
        }

        // @TODO
        return ($sectionnum && ($course = $this->get_course()) && $course->marker == $sectionnum);
    }

    /**
     * Whether this format allows to delete sections
     *
     * If format supports deleting sections it is also recommended to define language string
     * 'deletesection' inside the format.
     *
     * Do not call this function directly, instead use {@link course_can_delete_section()}
     *
     * @param int|stdClass|section_info $section
     * @return bool
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Prepares the templateable object to display section name
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
                                                         $editable = null, $edithint = null, $editlabel = null) {

        // @TODO
        global $USER, $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        if ($editable === null) {
            $editable = !empty($USER->editing) && has_capability('moodle/course:update',
                    context_course::instance($section->course));
        }

        $displayvalue = $title = get_section_name($section->course, $section);
        if ($linkifneeded) {
            // Display link under the section name if the course format setting is to display one section per page.
            $url = course_get_url($section->course, $section->section, array('navigation' => true));
            if ($url) {
                $displayvalue = html_writer::link($url, $title);
            }
            $itemtype = 'sectionname';
        } else {
            // If $linkifneeded==false, we never display the link (this is used when rendering the section header).
            // Itemtype 'sectionnamenl' (nl=no link) will tell the callback that link should not be rendered -
            // there is no other way callback can know where we display the section name.
            $itemtype = 'sectionnamenl';
        }
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname');
        }
        if (empty($editlabel)) {
            $editlabel = new lang_string('newsectionname', '', $title);
        }

        return new \core\output\inplace_editable('format_' . $this->format, $itemtype, $section->id, $editable,
            $displayvalue, $section->name, $edithint, $editlabel);
    }

    /**
     * Returns the default end date value based on the start date.
     *
     * This is the default implementation for course formats, it is based on
     * moodlecourse/courseduration setting. Course formats like format_weeks for
     * example can overwrite this method and return a value based on their internal options.
     *
     * @param moodleform $mform
     * @param array $fieldnames The form - field names mapping.
     * @return int
     */
    public function get_default_course_enddate($mform, $fieldnames = array()) {


        // @TODO
        if (empty($fieldnames)) {
            $fieldnames = array('startdate' => 'startdate');
        }

        $startdate = $this->get_form_start_date($mform, $fieldnames);
        $courseduration = intval(get_config('moodlecourse', 'courseduration'));
        if (!$courseduration) {
            // Default, it should be already set during upgrade though.
            $courseduration = YEARSECS;
        }

        return $startdate + $courseduration;
    }

    /**
     * Indicates whether the course format supports the creation of the Announcements forum.
     *
     * For course format plugin developers, please override this to return true if you want the Announcements forum
     * to be created upon course creation.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0.
        return !$section->section || $section->visible;
    }

    /**
     * Callback used in WS core_course_edit_section when teacher performs an AJAX action on a section (show/hide)
     *
     * Access to the course is already validated in the WS but the callback has to make sure
     * that particular action is allowed by checking capabilities
     *
     * Course formats should register
     *
     * @param stdClass|section_info $section
     * @param string $action
     * @param int $sr
     * @return null|array|stdClass any data for the Javascript post-processor (must be json-encodeable)
     */
    public function section_action($section, $action, $sr) {

        // @TODO
        global $PAGE;
        if (!$this->uses_sections() || !$section->section) {
            // No section actions are allowed if course format does not support sections.
            // No actions are allowed on the 0-section by default (overwrite in course format if needed).
            throw new moodle_exception('sectionactionnotsupported', 'core', null, s($action));
        }

        $course = $this->get_course();
        $coursecontext = context_course::instance($course->id);
        switch($action) {
            case 'hide':
            case 'show':
                require_capability('moodle/course:sectionvisibility', $coursecontext);
                $visible = ($action === 'hide') ? 0 : 1;
                course_update_section($course, $section, array('visible' => $visible));
                break;
            default:
                throw new moodle_exception('sectionactionnotsupported', 'core', null, s($action));
        }

        $modules = [];

        $modinfo = get_fast_modinfo($course);
        $coursesections = $modinfo->sections;
        if (array_key_exists($section->section, $coursesections)) {
            $courserenderer = $PAGE->get_renderer('core', 'course');
            $completioninfo = new completion_info($course);
            foreach ($coursesections[$section->section] as $cmid) {
                $cm = $modinfo->get_cm($cmid);
                $modules[] = $courserenderer->course_section_cm_list_item($course, $completioninfo, $cm, $sr);
            }
        }

        return ['modules' => $modules];
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
