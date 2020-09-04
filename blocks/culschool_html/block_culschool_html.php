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
 * @copyright  1999 onwards Naomi Wilce (naomi.wilce.1@city.ac.uk)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_culschool_html extends block_base {

    public $hide = '';

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

    public function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= " {$this->hide}";
        return $attributes;
    }

    public function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) :
        format_string(get_string('newhtmlblock', 'block_culschool_html'));

        // Get content early to check if block should be hidden in editing mode.
        // Content is maintained at site level, so the block should not show up
        // if it is empty.
        // When get_content() is called by the parent class, $content will already
        // be populated so the function short circuits and returns the value set
        // here.
        $this->content = $this->get_content();

        if (!$this->content->text) {
            $this->hide = 'hide';
        }
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

        if ($this->content !== null) {
            return $this->content;
        }

        // Determine if user sees student or staff info.
        $types = block_culschool_html_get_type();
        // Get all the ancestor categories of this course.
        $cats = block_culschool_html_get_category();

        if (isset($this->config)) {
            $config = $this->config;
        } else {
            $config = get_config('block_culschool_html');
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        $filteropt->noclean = true;
        $this->content = new stdClass;
        $this->content->footer = '';
        $this->content->text = '';
        $text = '';
        // Default to FORMAT_HTML which is what will have been used before the
        // editor was properly implemented for the block.
        $format = FORMAT_HTML;

        foreach ($types as $type) {

            $this->title = isset($this->config->title) ? format_string($this->config->title) :
            format_string(get_string($type . 'blockname', 'block_culschool_html'));

            foreach ($cats as $cat) {
                $textcat = $type . $cat;
                if ( !empty(get_config('block_culschool_html', $textcat))) {
                    $text .= get_config('block_culschool_html', $textcat);

                    // Rewrite url.
                    $text = file_rewrite_pluginfile_urls($text, 'pluginfile.php', $this->context->id, 'block_culschool_html',
                        'content', null);

                }
            }
        }

        $this->content->text .= format_text($text, $format, $filteropt);

        unset($filteropt); // Memory footprint.
        return $this->content;
    }

    public function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_culschool_html');
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

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = !empty($this->config) ? $this->config : new stdClass();

        return (object) [
            'instance' => $configs,
            'plugin' => new stdClass(),
        ];
    }
}