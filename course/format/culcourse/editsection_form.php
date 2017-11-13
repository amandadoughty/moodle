<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot. '/course/editsection_form.php');

/**
 *
 * CUL Course Format editing form.
 */
class format_culcourse_editsection_form extends editsection_form {

    /**
     * Load in existing data as form defaults
     *
     * @param stdClass|array $default_values object or array of default values
     */
    function set_data($default_values) {
        if (!is_object($default_values)) {
            // we need object for file_prepare_standard_editor
            $default_values = (object)$default_values;
        }
        $editoroptions = $this->_customdata['editoroptions'];
        $default_values = file_prepare_standard_editor($default_values, 'summary', $editoroptions,
                $editoroptions['context'], 'course', 'section', $default_values->id);
        $default_values->usedefaultname = false;
        moodleform::set_data($default_values);
    }
}
