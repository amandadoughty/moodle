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
 * @subpackage culcollapsed
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
// require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

class format_culcollapsed_dashboard {

    private $elements;
    private $modfullnames;
    private $ltitypes;

    public function __construct() {
        global $COURSE;

        $this->elements = [
            'readinglists', 
            'timetable', 
            'graderreport', 
            'calendar', 
            'students',
            'lecturers',
            'courseofficers',
            'media'
            ];

        $this->modfullnames = self::format_culcollapsed_get_modfullnames($COURSE);
        $this->ltitypes = self::format_culcollapsed_get_ltitypes($COURSE);
    }

    public function set_dashboard_options(&$courseformatoptions) {
        global $DB, $COURSE;

        $dashboardoptions = [];

        foreach ($this->elements as $element) {
            $dashboardoptions['show' . $element] = array(
                'default' => get_config('format_culcollapsed', 'defaultshow' . $element),
                'type' => PARAM_INT,
            );
        }

        foreach ($this->modfullnames as $mod => $modplural) {
            $dashboardoptions['show' . $mod] = array(
                'default' => 2,
                'type' => PARAM_INT,
            );
        }

        foreach ($this->ltitypes as $typeid => $name) {
            $dashboardoptions['showltitype' . $typeid] = array(
                'default' => 2,
                'type' => PARAM_INT,
            );
        }

        $dashboardoptions['selectmoduleleaders'] = array(
            'default' => null,
            'type' => PARAM_RAW,
        );

        # Insert at offset 4.
        $offset = 4;
        $courseformatoptions = array_slice($courseformatoptions, 0, $offset, true) +
            $dashboardoptions +
            array_slice($courseformatoptions, $offset, NULL, true);

    }

    public function set_dashboard_edit_options(&$courseformatoptionsedit) {
        global $DB, $COURSE;

        $dashboardoptionsedit = [];
        $coursecontext = context_course::instance($COURSE->id);
        
        foreach ($this->elements as $element) {
            $courseformatoptionsedit['show' . $element] = array(
                'label' => new lang_string('setshow' . $element, 'format_culcollapsed'),
                'help' => 'setshow' . $element,
                'help_component' => 'format_culcollapsed',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(1 => new lang_string('no'),
                          2 => new lang_string('yes'))
                )
            );
        }

        foreach ($this->modfullnames as $mod => $modplural) {
            $courseformatoptionsedit['show' . $mod] = array(
                'label' => new lang_string('setshowmodname', 'format_culcollapsed', $modplural),
                'help' => 'setshowmod',
                'help_component' => 'format_culcollapsed',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(1 => new lang_string('no'),
                          2 => new lang_string('yes'))
                )
            );
        }

        foreach ($this->ltitypes as $typeid => $name) {
            $courseformatoptionsedit['showltitype' . $typeid] = array(
                'label' => new lang_string('setshowmodname', 'format_culcollapsed', $name),
                'help' => 'setshowmod',
                'help_component' => 'format_culcollapsed',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(1 => new lang_string('no'),
                          2 => new lang_string('yes'))
                )
            );
        }

        // Get all the lecturers.
        $lecturers = array();
        $lecturerrole = $DB->get_record('role', ['shortname'=>'lecturer']);

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
            $selectbox[0] = get_string('nolecturers', 'format_culcollapsed');
        }

        $dashboardoptionsedit['selectmoduleleaders'] = [
            'label' => new lang_string('setselectmoduleleaders', 'format_culcollapsed'),
            'help' => 'setselectmoduleleaders',
            'help_component' => 'format_culcollapsed',
            'element_type' => 'select',
            'element_attributes' => [
                $selectbox,
                ['multiple' => 'multiple', 'size' => 6]
            ]
        ];

        // array_splice($courseformatoptionsedit, 4, 0, $dashboardoptionsedit);

        # Insert at offset 4.
        $offset = 4;
        $courseformatoptionsedit = array_slice($courseformatoptionsedit, 0, $offset, true) +
            $dashboardoptionsedit +
            array_slice($courseformatoptionsedit, $offset, NULL, true);

    }

    /**
     * TODO
     */
    static function format_culcollapsed_get_modfullnames($course) {
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

    static function format_culcollapsed_get_ltitypes($course) {
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