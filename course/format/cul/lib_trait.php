<?php


trait culformat {
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
}   
