 <?php


 /* This file contains main class for the course format Topic
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
require_once($CFG->dirroot. '/course/format/cul/lib_trait.php');

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

        $baseclasses = [
            1 => 'format_cultopics',
            2 => 'format_culweeks'
        ];

        // Get record from db or default
        $record = $DB->get_record('course_format_options',
                                array('courseid' => $courseid,
                                      'format' => 'cul',
                                      'name' => 'baseclass'
                                    ), 'value');

        if ($record) {
            $this->baseclassid = $record->value;
        } else {
            $config = get_config('format_cul');
            $this->baseclassid = $config->baseclass;
        }

        // $this->baseclass = get_format_or_default($baseclasses[$baseclass]);

        // parent::__construct($format, $courseid);

        $extendedclassname = $baseclasses[$this->baseclassid];

        return new $extendedclassname($format, $courseid);
    }

}

class format_culweeks extends format_weeks { 
    use culformat;

    // public function __construct($format, $courseid) { 
    //     die('boow');
    // }
}

class format_cultopics extends format_topics { 
    use culformat; 

    // public function __construct($format, $courseid) { 
    //     parent::__construct($format, $courseid);
    // }

    /**
     * Definitions of the additional options that this course format uses for course
     *
     * cul format uses the following options:
     * - baseclass
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) { die('boo!');
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
    public function create_edit_form_elements(&$mform, $forsection = false) {die('boo!');
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