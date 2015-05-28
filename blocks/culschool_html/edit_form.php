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
 * Form for editing HTML block instances.
 *
 * @package   block_culschool_html
 * @copyright  1999 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing HTML block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/blocks/culschool_html/lib.php');

class block_culschool_html_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;
        $depts = block_culschool_html_get_dept();
        $types = block_culschool_html_get_type();
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_culschool_html'));
        $mform->setType('config_title', PARAM_TEXT);

        foreach ($types as $type) {
            foreach ($depts as $dept) {
                $name = $type . $dept;
                $textname = 'text' . $name;
                $configname = 'config_' . $textname;
                $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->block->context);
                $mform->addElement('editor', $configname, get_string('configcontent' . $name, 'block_culschool_html'), null, $editoroptions);
                //$mform->addRule($configname, null, 'required', null, 'client');
                $mform->setType($configname, PARAM_RAW); // XSS is prevented when printing the block contents and serving files
            }
        }

        if (!empty($CFG->block_culschool_html_allowcssclasses)) {
            $mform->addElement('text', 'config_classes', get_string('configclasses', 'block_culschool_html'));
            $mform->setType('config_classes', PARAM_TEXT);
            $mform->addHelpButton('config_classes', 'configclasses', 'block_culschool_html');
        }
    }

    function set_data($defaults) {
        $depts = block_culschool_html_get_dept();
        $types = block_culschool_html_get_type();

        foreach ($types as $type) {
            foreach ($depts as $dept) {
                $name = $type . $dept;
                $textname = 'text' . $name;
                $formatname = 'format' . $name;
                $configname = 'config_' . $textname;

                if (!empty($this->block->config) && is_object($this->block->config)) {
                    ${$textname} = $this->block->config->{$textname};
                    $draftid_editor = file_get_submitted_draft_itemid($configname);

                    if (empty(${$textname})) {
                        $currenttext = '';
                    } else {
                        $currenttext = ${$textname} ;
                    }

                    $defaults->{$configname}['text'] = file_prepare_draft_area($draftid_editor, $this->block->context->id, 'block_culschool_html', 'content', 0, array('subdirs'=>true), $currenttext);
                    $defaults->{$configname}['itemid'] = $draftid_editor;
                    $defaults->{$configname}['format'] = $this->block->config->{$formatname};
                } else {
                    ${$textname}  = '';
                }
                // have to delete text here, otherwise parent::set_data will empty content
                // of editor
                unset($this->block->config->{$textname});
            }
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        parent::set_data($defaults);
        // restore $text
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }

        if (isset($title)) {
            // Reset the preserved title
            $this->block->config->title = $title;
        }

        foreach ($types as $type) {
            foreach ($depts as $dept) {
                $name = $type . $dept;
                $textname = 'text' . $name;
                $this->block->config->{$textname} = ${$textname};
            }
        }
    }
}
