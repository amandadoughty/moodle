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
 * block_culcourse_dashboard_edit_form
 *
 */
class block_culcourse_dashboard_edit_form extends block_edit_form {

    protected function specific_definition($mform) {

        global $COURSE, $DB;

        // Section header title according to language file.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('html', '<fieldset><legend>' .
                            get_string('modulesummary', 'block_culcourse_dashboard') .
                            '</legend>');

        // Checkbox: Hide Module Summary.
        $default = get_config('block_culcourse_dashboard', 'defaultshowcoursesummary');
        $choices = array(
            1 => new lang_string('no'),
            2 => new lang_string('yes')
        );

        $mform->addElement(
            'select',
            'config_hide_coursesummary',
            get_string('setshowcoursesummary', 'block_culcourse_dashboard'),
            $choices
            );

        $mform->setDefault('config_hide_coursesummary', $default);
        $mform->setType('config_hide_coursesummary', PARAM_MULTILANG);
        $mform->addHelpButton('config_hide_coursesummary', 'setshowcoursesummary', 'block_culcourse_dashboard');
        $mform->addElement('html', '</fieldset>');

        // Checkbox group: Hide Quick Links. //TODO: Create a master mapping for these, for flexibility.
        $elements = array('readinglists', 'timetable', 'graderreport', 'calendar', 'photoboard', 'media');
        $grouplabel = 'group1';

        $mform->addElement('html', '<fieldset><legend>' .
                            get_string('quicklinks', 'block_culcourse_dashboard') .
                            '</legend>');

        foreach ($elements as $element) {
            $default = get_config('block_culcourse_dashboard', 'defaultshow' . $element);
            $choices = array(
                1 => new lang_string('no'),
                2 => new lang_string('yes')
            );

            $mform->addElement(
                'select',
                'config_hide_' . $element,
                get_string('setshow' . $element, 'block_culcourse_dashboard'),
                $choices,
                array('group'=>$grouplabel)
                );

            $mform->setDefault('config_hide_' . $element, $default);
            $mform->setType('config_hide_' . $element, PARAM_MULTILANG);
            $mform->addHelpButton('config_hide_' . $element, 'setshow' . $element, 'block_culcourse_dashboard');
        }

        $mform->addElement('html', '</fieldset>');

        // Checkbox: Hide Activities links.
        $mform->addElement('html', '<fieldset><legend>' .
                            get_string('activities', 'block_culcourse_dashboard') .
                            '</legend>');

        $modfullnames = self::block_culcourse_dashboard_get_modfullnames($COURSE);

        foreach ($modfullnames as $mod => $modplural) {
            $default = 2;
            $choices = array(
                1 => new lang_string('no'),
                2 => new lang_string('yes')
            );

            $mform->addElement(
                'select',
                'config_hide_' . $mod,
                get_string('setshowmodname', 'block_culcourse_dashboard', $modplural),
                $choices,
                array('group'=>$grouplabel)
                );

            $mform->setDefault('config_hide_' . $mod, $default);
            $mform->setType('config_hide_' . $mod, PARAM_MULTILANG);
            $mform->addHelpButton('config_hide_' . $mod, 'setshowmod', 'block_culcourse_dashboard');
        }

        $mform->addElement('html', '</fieldset>');

        $mform->addElement('html', '<fieldset><legend>' .
                            get_string('moduleleaders', 'block_culcourse_dashboard') .
                            '</legend>');

        $coursecontext = context_course::instance($COURSE->id);

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
            $selectbox[0] = get_string('nolecturers', 'block_culcourse_dashboard');
        }

        $mform->addElement(
            'select',
            'config_selectmoduleleaders',
            get_string('setselectmoduleleaders', 'block_culcourse_dashboard'),
            $selectbox,
            array('multiple' => 'multiple', 'size' => 6)
            );

        $mform->setDefault('config_selectmoduleleaders', null);
        $mform->setType('config_selectmoduleleaders', PARAM_MULTILANG);
        $mform->addHelpButton('config_selectmoduleleaders', 'setselectmoduleleaders', 'block_culcourse_dashboard');

        $mform->addElement('html', '</fieldset>');
    }


    /**
     * TODO
     */
    static function block_culcourse_dashboard_get_modfullnames($course) {
        $modinfo = get_array_of_activities($course->id);
        $plurals = get_module_types_names(true);

        $modfullnames = array();
        $archetypes   = array();

        foreach($modinfo as $cm) {
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









}
