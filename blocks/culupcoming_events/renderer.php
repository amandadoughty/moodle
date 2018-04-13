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
 * Renderer for CUL Upcoming Events block
 *
 * @package    block
 * @subpackage culupcoming_events
 * @copyright  2013 Tim Gage <Tim.Gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die;

require_once('renderers/course_picture.php');

/**
 * block_culupcoming_events_renderer
 *
 * @package   block
 * @copyright 2013 Tim Gagen <Tim.Gagen.1@city.ac.uk>
 */
class block_culupcoming_events_renderer extends plugin_renderer_base {


    public function render_culupcoming_events($events, $prev, $next) {

        // $dashboard = new dashboard($COURSE, $this->culconfig);
        // $templatecontext = $dashboard->export_for_template($this);
        // $o .= $this->render_from_template('format_culcourse/dashboard', $templatecontext);


        $context = new stdClass();
        $context->events = $events;
        $context->reloadurl = $this->page->url;

        $context->pagination = $renderer->culupcoming_events_pagination($prev, $next);

        return $this->render_from_template('block_culupcoming_events/culupcoming_events', $context);
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param int $prev the previous page number
     * @param int $next the next page number
     * @return string $output html
     */
    public function culupcoming_events_pagination($prev = false, $next = false) {
        $pagination = new stdClass();

        if ($prev) {
            $pagination->prev = new stdClass();
            $pagination->prev->url = new moodle_url($this->page->url, array('block_culupcoming_events_page' => $prev));
            $pagination->prev->text = get_string('sooner', 'block_culupcoming_events');
        }

        if ($prev && $next) {
            $pagination->sep = '&nbsp;|&nbsp;';
        } else {
            $pagination->sep = '';
        }

        if ($next) {
            $pagination->next = new stdClass();
            $pagination->nexturl = new moodle_url($this->page->url, array('block_culupcoming_events_page' => $next));
            $pagination->nexttext = get_string('later', 'block_culupcoming_events');

        }

        return $pagination;
    }

        /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param int $prev the previous page number
     * @param int $next the next page number
     * @return string $output html
     */
    public function culupcoming_events_paginationold($prev = false, $next = false) {
        $output = '';

        if ($prev || $next) {
            $output .= html_writer::start_tag('div', array('class' => 'pages'));

            if ($prev) {
                $prevurl = new moodle_url($this->page->url, array('block_culupcoming_events_page' => $prev));
                // $prevurl = new moodle_url($this->page->url, array('block_culupcoming_events_lastid' => $prev));
                $prevtext = get_string('sooner', 'block_culupcoming_events');
                $output .= html_writer::link($prevurl, $prevtext);
            }

            if ($prev && $next) {
                $output .= '&nbsp;|&nbsp;';
            }

            if ($next) {
                $nexturl = new moodle_url($this->page->url, array('block_culupcoming_events_page' => $next));
                // $nexturl = new moodle_url($this->page->url, array('block_culupcoming_events_lastid' => $next));
                $nexttext = get_string('later', 'block_culupcoming_events');
                $output .= html_writer::link($nexturl, $nexttext);
            }

            $output .= html_writer::end_tag('div'); // Closing div: .pages.
        }

        return $output;
    }

    /**
     * Function to create the pagination. This will only show up for non-js
     * enabled browsers.
     *
     * @param int $prev the previous page number
     * @param int $next the next page number
     * @return string $output html
     */
    // public function culupcoming_events_pagination($prev = false, $next = false) {
    //     $output = '';

    //     if ($prev || $next) {
    //         $output .= html_writer::start_tag('div', array('class' => 'pages'));

    //         if ($prev) {
    //             $prevurl = new moodle_url(
    //                 $this->page->url, 
    //                 array('block_culupcoming_events_cid' => $prev)
    //             );
    //             // $prevurl = new moodle_url($this->page->url, array('block_culupcoming_events_lastid' => $prev));
    //             $prevtext = get_string('sooner', 'block_culupcoming_events');
    //             $output .= html_writer::link($prevurl, $prevtext);
    //         }

    //         if ($prev && $next) {
    //             $output .= '&nbsp;|&nbsp;';
    //         }

    //         if ($next) {
    //             $nexturl = new moodle_url(
    //                 $this->page->url, 
    //                 array('block_culupcoming_events_cid' => $next)
    //             );
    //             // $nexturl = new moodle_url($this->page->url, array('block_culupcoming_events_lastid' => $next));
    //             $nexttext = get_string('later', 'block_culupcoming_events');
    //             $output .= html_writer::link($nexturl, $nexttext);
    //         }

    //         $output .= html_writer::end_tag('div'); // Closing div: .pages.
    //     }

    //     return $output;
    // }
}
