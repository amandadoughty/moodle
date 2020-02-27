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
 * Ajax version of moving and hiding dashboard links.
 *
 * @package   local_culcourse_dashboard
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/local/culcourse_dashboard/locallib.php');

$courseid = required_param('courseid', PARAM_INT);
$action = required_param('action', PARAM_INT);
$name = optional_param('name', null, PARAM_RAW);
$showhide = optional_param('showhide', 0, PARAM_INT);
$copy = optional_param('copy', null, PARAM_RAW);
$moveto = optional_param('moveto', null, PARAM_RAW);
$cancelcopy = optional_param('cancelcopy', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$lnktxt = optional_param('lnktxt', '', PARAM_RAW);
$keyboard = optional_param('keyboard', false, PARAM_INT);

require_login();

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$usercanedit = has_capability('moodle/course:update', context_course::instance($courseid));

if (!$usercanedit) {
    print_error('noeditcoursesettings', 'local_culcourse_dashboard');
}

if ($action == SHOWHIDE) {
    if ($name) {
        echo local_culcourse_dashboard_quicklink_visibility($courseid, $section, $name, $showhide, $lnktxt);
    } else {
        print_error('noname', 'local_culcourse_dashboard');
    }
} else if ($action == MOVE) {
    if (!empty($moveto) && !empty($copy) && !empty($name) && confirm_sesskey()) {
        $updated = local_culcourse_dashboard_dashlink_move($courseid, $name, $copy, $moveto, $keyboard);

        if (!$updated) {
            print_error('courseformatmissing');
        }   
    } else {
        print_error('unknowaction');
    }
} else {
    print_error('unknowaction');
}