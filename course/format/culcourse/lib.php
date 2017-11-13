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
 * Collapsed Topics Information
 *
 * A topic based format that solves the issue of the 'Scroll of Death' when a course has many topics. All topics
 * except zero have a toggle that displays that topic. One or more topics can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer by a small
 * code change. Full installation instructions, code adaptions and credits are included in the 'Readme.md' file.
 *
 * @package    course/format
 * @subpackage culcourse
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

class format_culcourse extends format_base {

    private $settings;

    /**
     * Creates a new instance of class
     *
     * Please use {@link course_get_format($courseorid)} to get an instance of the format class
     *
     * @param string $format
     * @param int $courseid
     * @return format_culcourse
     */
    protected function __construct($format, $courseid) {
        if ($courseid === 0) {
            global $COURSE;
            $courseid = $COURSE->id;  // Save lots of global $COURSE as we will never be the site course.
        }        

        parent::__construct($format, $courseid);
    }

    /**
     * Returns the format's settings and gets them if they do not exist.
     * @return type The settings as an array.
     */
    public function get_settings() {
        if (empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
        }
        return $this->settings;
    }

    /**
     * Indicates this format uses sections.
     *
     * @return bool Returns true
     */
    public function uses_sections() {
        return true;
    }

    /**
     * Gets the name for the provided section.
     *
     * @param int|stdClass $section Section object from database or just field section.section
     * @return string The section name.
     */
    public function get_section_name($section) {
        $course = $this->get_course();
        // Don't add additional text as called in creating the navigation.
        return $this->get_culcourse_section_name($course, $section, false);
    }

    /**
     * Gets the name for the provided course, section and state if need to add addional text.
     *
     * @param stdClass $course The course entry from DB
     * @param int|stdClass $section Section object from database or just field section.section
     * @param boolean $additional State to add additional text yes = true or no = false.
     * @return string The section name.
     */
    public function get_culcourse_section_name($course, $section, $additional) {
        $thesection = $this->get_section($section);
        if (is_null($thesection)) {
            $thesection = new stdClass;
            $thesection->name = '';
            if (is_object($section)) {
                $thesection->section = $section->section;
            } else {
                $thesection->section = $section;
            }
        }
        $o = '';
        $tcsettings = $this->get_settings();
        $tcsectionsettings = $this->get_format_options($thesection->section);
        $coursecontext = context_course::instance($course->id);

        // We can't add a node without any text.
        if ((string) $thesection->name !== '') {
            $o .= format_string($thesection->name, true, array('context' => $coursecontext));
            if (($thesection->section != 0) && (($tcsettings['layoutstructure'] == 2) ||
                ($tcsettings['layoutstructure'] == 3) || ($tcsettings['layoutstructure'] == 5))) {
                $o .= ' ';
                if ($additional == true) { // Break 'br' tags break backups!
                    $o .= html_writer::empty_tag('br');
                }
                if (empty($tcsectionsettings['donotshowdate'])) {
                    $o .= $this->get_section_dates($section, $course, $tcsettings);
                }
            }
        } else if ($thesection->section == 0) {
            $o = get_string('section0name', 'format_culcourse');
        } else {
            if (($tcsettings['layoutstructure'] == 1) || ($tcsettings['layoutstructure'] == 4)) {
                $o = get_string('sectionname', 'format_culcourse') . ' ' . $thesection->section;
            } else {
                $o .= $this->get_section_dates($section, $course, $tcsettings);
            }
        }

        return $o;
    }

    public function get_section_dates($section, $course, $tcsettings) {
        $dateformat = get_string('strftimedateshort');
        $o = '';
        if ($tcsettings['layoutstructure'] == 5) {
            $day = $this->format_culcourse_get_section_day($section, $course);

            $weekday = userdate($day, $dateformat);
            $o = $weekday;
        } else {
            $dates = $this->format_culcourse_get_section_dates($section, $course);

            // We subtract 24 hours for display purposes.
            $dates->end = ($dates->end - 86400);

            $weekday = userdate($dates->start, $dateformat);
            $endweekday = userdate($dates->end, $dateformat);
            $o = $weekday . ' - ' . $endweekday;
        }
        return $o;
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        global $CFG;
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            $url->set_anchor('section-'.$sectionno);
        }
        return $url;
    }   

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     * The property (array)testedbrowsers can be used as a parameter for {@link ajaxenabled()}.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
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
        $titles = array();
        $current = -1;  // MDL-33546.
        $weekformat = false;
        $tcsettings = $this->get_settings();
        if (($tcsettings['layoutstructure'] == 2) || ($tcsettings['layoutstructure'] == 3) ||
            ($tcsettings['layoutstructure'] == 5)) {
            $weekformat = true;
        }
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        if ($sections = $modinfo->get_section_info_all()) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $this->get_culcourse_section_name($course, $section, true);
                if (($weekformat == true) && ($this->is_section_current($section))) {
                    $current = $number;  // Only set if a week based course to keep the current week in the same place.
                }
            }
        }
        return array('sectiontitles' => $titles, 'current' => $current, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        global $DB;

        $blocks = $DB->get_records('block', null, '', 'name');
        $defaultblocks = get_config('format_culcourse', 'defaultblocks_culcourse');
        $defaultblocks = preg_replace('/\s+/', '', $defaultblocks);
        $defaultblocks = explode(',', $defaultblocks);

        foreach ($defaultblocks as $key => $defaultblock) {
            if (!array_key_exists($defaultblock, $blocks)) {
                unset($defaultblocks[$key]);
            }
        }

        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => $defaultblocks
        );
    }

    public function section_format_options($foreditform = false) {
        static $sectionformatoptions = false;

        if ($sectionformatoptions === false) {
            $sectionformatoptions = array(
                'donotshowdate' => array(
                    'default' => 0,
                    'type' => PARAM_INT
                )
            );
        }
        if ($foreditform && !isset($sectionformatoptions['donotshowdate']['label'])) {
            $sectionformatoptionsedit = array(
                'donotshowdate' => array(
                    'label' => new lang_string('donotshowdate', 'format_culcourse'),
                    'help' => 'donotshowdate',
                    'help_component' => 'format_culcourse',
                    'element_type' => 'checkbox'
                )
            );
            $sectionformatoptions = array_merge_recursive($sectionformatoptions, $sectionformatoptionsedit);
        }

        $tcsettings = $this->get_settings();
        if (($tcsettings['layoutstructure'] == 2) || ($tcsettings['layoutstructure'] == 3) ||
            ($tcsettings['layoutstructure'] == 5)) {
            // Weekly layout.
            return $sectionformatoptions;
        } else {
            return array();
        }
    }
    /**
     * Definitions of the additional options that this course format uses for course
     *
     * Collapsed Topics format uses the following options (until extras are migrated):
     * - coursedisplay
     * - numsections
     * - hiddensections
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        global $DB, $COURSE;

        $elements = array(
            'readinglists',  
            'timetable', 
            'graderreport', 
            'calendar', 
            'students',
            'lecturers',
            'courseofficers',
            'media'
            );

        $modfullnames = self::format_culcourse_get_modfullnames($COURSE);
        $ltitypes = self::format_culcourse_get_ltitypes($COURSE);

        if ($courseformatoptions === false) {
            $courseconfig = get_config('moodlecourse');

            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $courseconfig->numsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT,
                ),

                'layoutstructure' => array(
                    'default' => get_config('format_culcourse', 'defaultlayoutstructure'),
                    'type' => PARAM_INT,
                ),

                'showsectionsummary' => array(
                    'default' => get_config('format_culcourse', 'defaultshowsectionsummary'),
                    'type' => PARAM_INT,
                ),

                'showcoursesummary' => array(
                    'default' => get_config('format_culcourse', 'defaultshowcoursesummary'),
                    'type' => PARAM_INT,
                )
            );

            foreach ($elements as $element) {
                $courseformatoptions['show' . $element] = array(
                    'default' => get_config('format_culcourse', 'defaultshow' . $element),
                    'type' => PARAM_INT,
                );
            }

            foreach ($modfullnames as $mod => $modplural) {
                $courseformatoptions['show' . $mod] = array(
                    'default' => 2,
                    'type' => PARAM_INT,
                );
            }

            foreach ($ltitypes as $typeid => $name) {
                $courseformatoptions['showltitype' . $typeid] = array(
                    'default' => 2,
                    'type' => PARAM_INT,
                );
            }

            $courseformatoptions['selectmoduleleaders'] = array(
                'default' => null,
                'type' => PARAM_RAW,
            );
        }

        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            $coursecontext = context_course::instance($this->courseid);
            $courseconfig = get_config('moodlecourse');
            $sectionmenu = array();

            for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
                $sectionmenu[$i] = "$i";
            }

            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numbersections', 'format_culcourse'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(0 => new lang_string('hiddensectionscollapsed'),
                              1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
            );

            $courseformatoptionsedit['layoutstructure'] = array(
                'label' => new lang_string('setlayoutstructure', 'format_culcourse'),
                'help' => 'setlayoutstructure',
                'help_component' => 'format_culcourse',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(1 => new lang_string('setlayoutstructuretopic', 'format_culcourse'),             // Topic.
                          2 => new lang_string('setlayoutstructureweek', 'format_culcourse'),              // Week.
                          3 => new lang_string('setlayoutstructurelatweekfirst', 'format_culcourse'),      // Latest Week First.
                          4 => new lang_string('setlayoutstructurecurrenttopicfirst', 'format_culcourse'), // Current Topic First.
                          5 => new lang_string('setlayoutstructureday', 'format_culcourse'))               // Day.
                )
            );

            $courseformatoptionsedit['showsectionsummary'] = array(
                'label' => new lang_string('setshowsectionsummary', 'format_culcourse'),
                'help' => 'setshowsectionsummary',
                'help_component' => 'format_culcourse',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(1 => new lang_string('no'),
                          2 => new lang_string('yes'))
                )
            );

            $courseformatoptionsedit['showcoursesummary'] = array(
                'label' => new lang_string('setshowcoursesummary', 'format_culcourse'),
                'help' => 'setshowcoursesummary',
                'help_component' => 'format_culcourse',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(1 => new lang_string('no'),
                          2 => new lang_string('yes'))
                )
            );

            foreach ($elements as $element) {
                $courseformatoptionsedit['show' . $element] = array(
                    'label' => new lang_string('setshow' . $element, 'format_culcourse'),
                    'help' => 'setshow' . $element,
                    'help_component' => 'format_culcourse',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(1 => new lang_string('no'),
                              2 => new lang_string('yes'))
                    )
                );
            }

            foreach ($modfullnames as $mod => $modplural) {
                $courseformatoptionsedit['show' . $mod] = array(
                    'label' => new lang_string('setshowmodname', 'format_culcourse', $modplural),
                    'help' => 'setshowmod',
                    'help_component' => 'format_culcourse',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(1 => new lang_string('no'),
                              2 => new lang_string('yes'))
                    )
                );
            }

            foreach ($ltitypes as $typeid => $name) {
                $courseformatoptionsedit['showltitype' . $typeid] = array(
                    'label' => new lang_string('setshowmodname', 'format_culcourse', $name),
                    'help' => 'setshowmod',
                    'help_component' => 'format_culcourse',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(1 => new lang_string('no'),
                              2 => new lang_string('yes'))
                    )
                );
            }

            // Get all the lecturers.
            $lecturers = array();
            $lecturerrole = $DB->get_record('role', array('shortname'=>'lecturer'));

            if ($lecturerrole) {
                $lecturers = get_role_users($lecturerrole->id, $coursecontext);
            }

            // Create a multi select box?
            $selectbox = array();

            if (count($lecturers)) {
                foreach ($lecturers as $lecturer) {
                    $selectbox[$lecturer->id] = fullname($lecturer);
                }
            } else {
                $selectbox[0] = get_string('nolecturers', 'format_culcourse');
            }

            $courseformatoptionsedit['selectmoduleleaders'] = array(
                'label' => new lang_string('setselectmoduleleaders', 'format_culcourse'),
                'help' => 'setselectmoduleleaders',
                'help_component' => 'format_culcourse',
                'element_type' => 'select',
                'element_attributes' => array(
                    $selectbox,
                    array('multiple' => 'multiple', 'size' => 6)
                )
            );

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }

        return $courseformatoptions;
    }

    /**
     * Adds format options elements to the course/section edit form
     *
     * This function is called from {@link course_edit_form::definition_after_data()}
     *
     * @param MoodleQuickForm $mform form the elements are added to
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form
     * @return array array of references to the added form elements
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        $elements = parent::create_edit_form_elements($mform, $forsection);

        // Convert saved course_format_options value back to an array to set the value.
        if ($selectmoduleleaders = $mform->getElementValue('selectmoduleleaders')) {
            if (!is_array($selectmoduleleaders)) {
                $mform->setDefault('selectmoduleleaders', explode(',', $selectmoduleleaders ));
            } else {
                $mform->setDefault('selectmoduleleaders', $selectmoduleleaders);
            }
        }

        foreach ($elements as $key => $element) {
            if($elements[$key]->getName() == 'selectmoduleleaders') {
                $selectmoduleleadersel = $elements[$key];
                unset($elements[$key]);
            }
                    
        }

        // Increase the number of sections combo box values if the user has increased the number of sections
        // using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
        // reduced below the number of sections already set for the course on the site administration course
        // defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
        // activities / resources.
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections+1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
            }
        }

        $elements[] = $mform->addElement('header', 'selectmoduleleadershdr', get_string('setselectmoduleleadershdr', 'format_culcourse'));
        $mform->addHelpButton('selectmoduleleadershdr', 'setselectmoduleleadershdr', 'format_culcourse', '', true);
        $elements[] = $selectmoduleleadersel;
        $elements = array_values($elements);

        return $elements;
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'Collapsed Topics', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * If previous course format did not have 'numsections' option, we populate it with the
     * current number of sections.  The layout and colour defaults will come from 'course_format_options'.
     *
     * @param stdClass|array $data return value from {@link moodleform::get_data()} or array with data
     * @param stdClass $oldcourse if this function is called from {@link update_course()}
     *     this object contains information about the course before update
     * @return bool whether there were any changes to the options values
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB;

        // Convert the form array to a string to enable saving to course_format_options table.
        // Without this, an error is thrown:
        // Warning: mysqli::real_escape_string() expects parameter 1 to be string, array given
        if (isset($data->selectmoduleleaders) && is_array($data->selectmoduleleaders)) {
            $data->selectmoduleleaders = join(',', $data->selectmoduleleaders);
        }

        if ($oldcourse !== null) {
            $data = (array)$data;
            $oldcourse = (array)$oldcourse;
            $options = $this->course_format_options();
            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        // If previous format does not have the field 'numsections'
                        // and $data['numsections'] is not set,
                        // we fill it with the maximum section number from the DB
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        return $this->update_format_options($data);
    }

    /**
     * Return an instance of moodleform to edit a specified section
     *
     * Format extends editsection_form to change the default for usedefaultname.
     *
     * @param mixed $action the action attribute for the form. If empty defaults to auto detect the
     *              current url. If a moodle_url object then outputs params as hidden variables.
     * @param array $customdata the array with custom data to be passed to the form
     *     /course/editsection.php passes section_info object in 'cs' field
     *     for filling availability fields
     * @return moodleform
     */
    public function editsection_form($action, $customdata = array()) {
        global $CFG;
        require_once($CFG->dirroot. '/course/format/culcourse/editsection_form.php');
        $context = context_course::instance($this->courseid);

        if (!array_key_exists('course', $customdata)) {
            $customdata['course'] = $this->get_course();
        }

        $mform = new format_culcourse_editsection_form($action, $customdata);
        
        return $mform;
    }    

    /**
     * Whether this format allows to delete sections
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
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Is the section passed in the current section?
     *
     * @param stdClass $section The course_section entry from the DB
     * @return bool true if the section is current
     */
    public function is_section_current($section) {
        $tcsettings = $this->get_settings();
        if (($tcsettings['layoutstructure'] == 2) || ($tcsettings['layoutstructure'] == 3)) {
            if ($section->section < 1) {
                return false;
            }

            $timenow = time();
            $dates = $this->format_culcourse_get_section_dates($section, $this->get_course());

            return (($timenow >= $dates->start) && ($timenow < $dates->end));
        } else if ($tcsettings['layoutstructure'] == 5) {
            if ($section->section < 1) {
                return false;
            }

            $timenow = time();
            $day = $this->format_culcourse_get_section_day($section, $this->get_course());
            $onedayseconds = 86400;
            return (($timenow >= $day) && ($timenow < ($day + $onedayseconds)));
        } else {
            return parent::is_section_current($section);
        }
    }

    /**
     * Return the start and end date of the passed section.
     *
     * @param int|stdClass $section The course_section entry from the DB.
     * @param stdClass $course The course entry from DB.
     * @return stdClass property start for startdate, property end for enddate.
     */
    private function format_culcourse_get_section_dates($section, $course) {
        $oneweekseconds = 604800;
        /* Hack alert. We add 2 hours to avoid possible DST problems. (e.g. we go into daylight
           savings and the date changes. */
        $startdate = $course->startdate + 7200;

        $dates = new stdClass();
        if (is_object($section)) {
            $section = $section->section;
        }

        $dates->start = $startdate + ($oneweekseconds * ($section - 1));
        $dates->end = $dates->start + $oneweekseconds;

        return $dates;
    }

    /**
     * Return the date of the passed section.
     *
     * @param int|stdClass $section The course_section entry from the DB.
     * @param stdClass $course The course entry from DB.
     * @return stdClass property date.
     */
    private function format_culcourse_get_section_day($section, $course) {
        $onedayseconds = 86400;
        /* Hack alert. We add 2 hours to avoid possible DST problems. (e.g. we go into daylight
           savings and the date changes. */
        $startdate = $course->startdate + 7200;

        if (is_object($section)) {
            $section = $section->section;
        }

        $day = $startdate + ($onedayseconds * ($section - 1));

        return $day;
    }

    /**
     * TODO
     */
    static function format_culcourse_get_modfullnames($course) {
        $modinfo = get_array_of_activities($course->id);
        $plurals = get_module_types_names(true);

        $modfullnames = array();
        $archetypes   = array();

        foreach($modinfo as $cm) {
            if ($cm->mod == 'lti') {
                continue;
            }

            if (array_key_exists($cm->mod, $modfullnames)) {
                continue;
            }

            if (!array_key_exists($cm->mod, $archetypes)) {
                $archetypes[$cm->mod] = plugin_supports('mod', $cm->mod, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
            }

            if ($archetypes[$cm->mod] == MOD_ARCHETYPE_RESOURCE) {
                if (!array_key_exists('resources', $modfullnames)) {
                    $modfullnames['resources'] = get_string('resources');
                }

            } else {
                if (isset($plurals[$cm->mod])) {
                    $modfullnames[$cm->mod] = $plurals[$cm->mod];
                } else {
                    $modfullnames[$cm->mod] = ucfirst($cm->mod);
                }
            }
        }

        return $modfullnames;
    }

    static function format_culcourse_get_ltitypes($course) {
        global $DB;

        $plurals = get_module_types_names(true);

        $sql = "SELECT DISTINCT l.typeid, m.name
                FROM {course_modules} cm
                JOIN {modules} m
                ON cm.module = m.id
                JOIN {lti} l
                ON cm.instance = l.id
                WHERE cm.course = :courseid
                AND m.name = 'lti'";

        $params = ['courseid' => $course->id];

        $records = $DB->get_recordset_sql($sql, $params);
        $ltitypes = array();

        foreach($records as $record) {
            $type = lti_get_type($record->typeid);

            if ($type) {
                if (array_key_exists($record->typeid, $ltitypes)) {
                    continue;
                }

                if (!$record->typeid) {
                    $ltitypes[$record->typeid] = $plurals[$record->name];
                } else {
                    $ltitypes[$type->id] = $type->name;
                }
            }          
        }       

        return $ltitypes;
    }

}

/**
 * The string that is used to describe a section of the course.
 *
 * @return string The section description.
 */
function callback_culcourse_definition() {
    return get_string('sectionname', 'format_culcourse');
}
