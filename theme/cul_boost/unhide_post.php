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
 * Unhide a hidden course.
 *
 * @package    theme-cul_boost
 * @copyright  2018 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$cid = required_param('cid', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$returnurl = new moodle_url('/course/view.php', ['id' => $cid]);
$coursecontext = context_course::instance($cid);

// If we have got here as a confirmed action, do it.
if ($confirm && isloggedin() && confirm_sesskey()) {
    require_capability('moodle/course:update', $coursecontext);

    // Make the course visible.
	theme_cul_boost_show_course($cid);
    redirect($returnurl, get_string('courseshown', 'theme_cul_boost'));
}

// Otherwise, show a confirmation page.
$params = ['cid' => $cid, 'sesskey' => sesskey(), 'confirm' => 1];
$actionurl = new moodle_url('/theme/cul_boost/unhide_post.php', $params);

$PAGE->set_context($coursecontext);
$PAGE->set_url($actionurl);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('showcourse', 'theme_cul_boost'));
echo $OUTPUT->box_start('generalbox', 'notice');

echo html_writer::tag('p', get_string('confirmshowcourse', 'theme_cul_boost'));
echo $OUTPUT->single_button($actionurl, get_string('showcourse', 'theme_cul_boost'), 'post');
echo $OUTPUT->single_button($returnurl, get_string('cancel'), 'get');

echo $OUTPUT->box_end();
echo $OUTPUT->footer();
