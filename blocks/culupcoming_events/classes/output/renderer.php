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
 * culupcoming_events block renderer
 *
 * @package    block_culupcoming_events
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_culupcoming_events\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;
use renderable;
use stdClass;

/**
 * culupcoming_events block renderer
 *
 * @package    block_culupcoming_events
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Return the main content for the block culupcoming_events.
     *
     * @param main $main The main renderable
     * @return string HTML string
     */
    public function render_main(main $main) {
        return $this->render_from_template('block_culupcoming_events/main', $main->export_for_template($this));
    }

    /**
     * Returns HTML to display the specified course's avatar.
     *
     * Course avatar may be obtained in two ways:
     * <pre>
     * // Option 1: (shortcut for simple cases, preferred way)
     * // $course has come from the DB and has fields id
     * $OUTPUT->course_picture($course, array('popup'=>true));
     *
     * // Option 2:
     * $coursepic = new course($course);
     * // Set properties of $coursepic
     * $coursepic->popup = true;
     * $OUTPUT->render($coursepic);
     * </pre>
     *
     * @param stdClass $course Object with at least fields id, picture, imagealt, firstname, lastname
     *     If any of these are missing, the database is queried. Avoid this
     *     if at all possible, particularly for reports. It is very bad for performance.
     * @param array $options associative array with course picture options, used only if not a course_picture object,
     *     options are:
     *     - size=35 (size of image)
     *     - link=true (make image clickable - the link leads to course)
     *     - popup=false (open in popup)
     *     - alttext=true (add image alt attribute)
     *     - class = image class attribute (default 'coursepicture')
     * @return string HTML fragment
     */
    public function course_picture(stdClass $course, array $options = null) {
        $coursepicture = new course_picture($course);
        foreach ((array)$options as $key => $value) {
            if (array_key_exists($key, $coursepicture)) {
                $coursepicture->$key = $value;
            }
        }
        return $this->render($coursepicture);
    }

    /**
     * Internal implementation of course image rendering.
     *
     * @param course_picture $coursepicture
     * @return string
     */
    protected function render_course_picture(course_picture $coursepicture) {
        global $CFG, $DB;

        $course = $coursepicture->course;
        $coursedisplayname = $course->shortname;

        if ($coursepicture->alttext) {
            $alt = get_string('pictureof', '', $coursedisplayname);
        } else {
            $alt = '';
        }

        if (empty($coursepicture->size)) {
            $size = 35;
        } else if ($coursepicture->size === true or $coursepicture->size == 1) {
            $size = 100;
        } else {
            $size = $coursepicture->size;
        }

        $class = $coursepicture->class;
        $src = $coursepicture->get_url($this->page, $this);
        $attributes = array('src' => $src, 'alt' => $alt, 'title' => $alt, 'class' => $class);

        // Get the image html output first.
        $output = \html_writer::empty_tag('img', $attributes);;

        // Then wrap it in link if needed.
        if (!$coursepicture->link) {
            return $output;
        }

        $url = new \moodle_url('/course/view.php', array('id' => $course->id));
        $attributes = array('href' => $url);

        if ($coursepicture->popup) {
            $id = \html_writer::random_id('coursepicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);
        }

        return \html_writer::tag('a', $output, $attributes);
    }
}
