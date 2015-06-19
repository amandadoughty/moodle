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

class block_culschool_html extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_culschool_html');
    }

    public function has_config() {
        return true;
    }

    public function applicable_formats() {
        return array('all' => false,
                    'course-view' => true);
    }

    public function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) :
        format_string(get_string('newhtmlblock', 'block_culschool_html'));
    }

    public function instance_allow_multiple() {
        return false;
    }


    public function instance_can_be_hidden() {
        return false;
    }

    public function get_content() {
        global $CFG, $COURSE;
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/blocks/culschool_html/lib.php');

        // Determine if user sees student or staff info.
        $types = block_culschool_html_get_type();
        // Get all the ancestor categories of this course.
        $cats = block_culschool_html_get_category();

        if (isset($this->config)) {
            $config = $this->config;
        } else {
            $config = get_config('block_culschool_html');
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;

        // @TODO Remove the if statement and just leave $filteropt->noclean = true;
        // We always want the content cleaned up. Just in case an admin copies and pastes
        // something bad by mistake.
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '';
        $text = '';
        // Default to FORMAT_HTML which is what will have been used before the
        // editor was properly implemented for the block.
        $format = FORMAT_HTML;

        // @TODO Remove commented code
        // foreach ($types as $type) {
        //     foreach ($depts as $dept) {
        //         $name = $type . $dept;
        //         $textname = 'text' . $name;
        //         $configname = 'config_' . $textname;

        //         if (isset($this->config->{$textname})) {
        //             // rewrite url
        //             $this->config->{$textname} = file_rewrite_pluginfile_urls($this->config->{$textname},
        //                 'pluginfile.php', $this->context->id, 'block_culschool_html', 'content', NULL);

        //             // Check to see if the format has been properly set on the config
        //             if (isset($this->config->format)) {
        //                 $format = $this->config->format;
        //             }

        //             //$this->content->text .= format_text($this->config->{$textname}, $format, $filteropt);
        //             $text .= $this->config->{$textname};
        //         }
        //     }
        // }
        foreach ($types as $type) {

            $this->title = isset($this->config->title) ? format_string($this->config->title) :
            format_string(get_string($type . 'blockname', 'block_culschool_html'));

            foreach ($cats as $cat) {
                $textcat = $type . $cat;
                if ( !empty(get_config('block_culschool_html', $textcat))) {
                    $text .= get_config('block_culschool_html', $textcat);

                // @TODO Uncomment these lines. We do need to rewrite the file urls when displaying
                // files.
                // // rewrite url
                // $text = file_rewrite_pluginfile_urls($text,
                //     'pluginfile.php', $this->context->id, 'block_culschool_html', 'content', NULL);

                }
            }
        }

        $this->content->text .= format_text($text, $format, $filteropt);

        unset($filteropt); // Memory footprint.
        return $this->content;
    }


    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/blocks/culschool_html/lib.php');

        // Get all of the categories.
        $categories = $DB->get_records('course_categories', array ('visible' => 1), 'id, name');
        $types = block_culschool_html_get_type();
        $config = clone($data);

        foreach ($types as $type) {
            foreach ($categories as $category) {
                $name = $type . $category;
                $textname = 'text' . $name;
                // @TODO Remove line below. We are always using FORMAT_HTML
                $formatname = 'format' . $name;
                // Move embedded files into a proper filearea and adjust HTML links to match.
                $config->{$textname} = file_save_draft_area_files($data->{$textname}['itemid'], $this->context->id,
                    'block_culschool_html', 'content', 0, array('subdirs' => true), $data->{$textname}['text']);
                // @TODO Remove line below. We are always using FORMAT_HTML
                $config->{$formatname} = $data->{$textname}['format'];
            }
        }

        parent::instance_config_save($config, $nolongerused);
    }

    public function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_culschool_html');
        return true;
    }

    // @TODO Remove this function. We will require that the content is cleaned regardless.
    public function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there
                // That is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }


    // @TODO Remove this function. We are not using the config setting
    /*
     * Add custom html attributes to aid with theming and styling
     *
     * @return array
     */
    public function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        if (!empty($CFG->block_culschool_html_allowcssclasses)) {
            if (!empty($this->config->classes)) {
                $attributes['class'] .= ' '.$this->config->classes;
            }
        }

        return $attributes;
    }
}
