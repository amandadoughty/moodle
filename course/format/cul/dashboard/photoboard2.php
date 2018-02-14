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
 * Lists all the users within a given course.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

use format_cul\output\photoboard;

require_once('../../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->libdir . '/filelib.php');

define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);
define('MODE_BRIEF', 0);
define('MODE_USERDETAILS', 1);

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$mode         = optional_param('mode', 1, PARAM_INT); // Use the MODE_ constants.
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('id', 0, PARAM_INT); // This are required.
$roleid       = optional_param('roleid', 0, PARAM_INT);
$groupparam   = optional_param('group', 0, PARAM_INT);
$sifirst = optional_param('sifirst', 'all', PARAM_NOTAGS);
$silast  = optional_param('silast', 'all', PARAM_NOTAGS);

$PAGE->set_url('/course/format/cul/dashboard/photoboard2.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'contextid' => $contextid,
        'id' => $courseid));

if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id' => $context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $context = context_course::instance($course->id, MUST_EXIST);
}

// Not needed anymore.
unset($contextid);
unset($courseid);

require_login($course);

$PAGE->set_pagelayout('base');
course_require_view_participants($context);

// Trigger events.
user_list_view($course, $context);

$PAGE->set_title("$course->shortname: ".get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('path-format-cul-photos'); // So we can style it independently.
// $PAGE->set_other_editing_capability('moodle/course:manageactivities');

echo $OUTPUT->header();

if (has_capability('block/culcourse_dashboard:viewallphotoboard', $context)) {

}

// Get the currently applied filters.
$filtersapplied = optional_param_array('unified-filters', [], PARAM_NOTAGS);
$filterwassubmitted = optional_param('unified-filter-submitted', 0, PARAM_BOOL);

// If they passed a role make sure they can view that role.
if ($roleid) {
    $viewableroles = get_profile_roles($context);

    // Check if the user can view this role.
    if (array_key_exists($roleid, $viewableroles)) {
        $filtersapplied[] = USER_FILTER_ROLE . ':' . $roleid;
    } else {
        $roleid = 0;
    }
}

// Default group ID.
$groupid = false;
$canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);

if ($course->groupmode != NOGROUPS) {
    if ($canaccessallgroups) {
        // Change the group if the user can access all groups and has specified group in the URL.
        if ($groupparam) {
            $groupid = $groupparam;
        }
    } else {
        // Otherwise, get the user's default group.
        $groupid = groups_get_course_group($course, true);
        if ($course->groupmode == SEPARATEGROUPS && !$groupid) {
            // The user is not in the group so show message and exit.
            echo $OUTPUT->notification(get_string('notingroup'));
            echo $OUTPUT->footer();
            exit;
        }
    }
}

$hasgroupfilter = false;
$lastaccess = 0;
$searchkeywords = [];
$enrolid = 0;
$status = -1;

foreach ($filtersapplied as $filter) {
    $filtervalue = explode(':', $filter, 2);
    $value = null;
    if (count($filtervalue) == 2) {
        $key = clean_param($filtervalue[0], PARAM_INT);
        $value = clean_param($filtervalue[1], PARAM_INT);
    } else {
        // Search string.
        $key = USER_FILTER_STRING;
        $value = clean_param($filtervalue[0], PARAM_TEXT);
    }

    switch ($key) {
        case USER_FILTER_ENROLMENT:
            $enrolid = $value;
            break;
        case USER_FILTER_GROUP:
            $groupid = $value;
            $hasgroupfilter = true;
            break;
        case USER_FILTER_LAST_ACCESS:
            $lastaccess = $value;
            break;
        case USER_FILTER_ROLE:
            $roleid = $value;
            break;
        case USER_FILTER_STATUS:
            // We only accept active/suspended statuses.
            if ($value == ENROL_USER_ACTIVE || $value == ENROL_USER_SUSPENDED) {
                $status = $value;
            }
            break;
        default:
            // Search string.
            $searchkeywords[] = $value;
            break;
    }
}

// If course supports groups we may need to set a default.
if ($groupid !== false) {
    if ($canaccessallgroups) {
        // User can access all groups, let them filter by whatever was selected.
        $filtersapplied[] = USER_FILTER_GROUP . ':' . $groupid;
    } else if (!$filterwassubmitted && $course->groupmode == VISIBLEGROUPS) {
        // If we are in a course with visible groups and the user has not submitted anything and does not have
        // access to all groups, then set a default group.
        $filtersapplied[] = USER_FILTER_GROUP . ':' . $groupid;
    } else if (!$hasgroupfilter && $course->groupmode != VISIBLEGROUPS) {
        // The user can't access all groups and has not set a group filter in a course where the groups are not visible
        // then apply a default group filter.
        $filtersapplied[] = USER_FILTER_GROUP . ':' . $groupid;
    } else if (!$hasgroupfilter) { // No need for the group id to be set.
        $groupid = false;
    }
}

if ($groupid && ($course->groupmode != SEPARATEGROUPS || $canaccessallgroups)) {
    $grouprenderer = $PAGE->get_renderer('core_group');
    $groupdetailpage = new \core_group\output\group_details($groupid);
    echo $grouprenderer->group_details($groupdetailpage);
}

// Render the unified filter.
$renderer = $PAGE->get_renderer('core_user');
echo $renderer->unified_filter($course, $context, $filtersapplied);

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/course/format/cul/dashboard/photoboard2.php', array(
        'contextid' => $context->id,
        'id' => $course->id,
        'perpage' => $perpage));

// User search
if ($sifirst !== 'all') {
    set_user_preference('ifirst', $sifirst);
}
if ($silast !== 'all') {
    set_user_preference('ilast', $silast);
}

if (!empty($USER->preference['ifirst'])) {
    $sifirst = $USER->preference['ifirst'];
} else {
    $sifirst = 'all';
}

if (!empty($USER->preference['ilast'])) {
    $silast = $USER->preference['ilast'];
} else {
    $silast = 'all';
}

// Generate where clause
$where = array();
$where_params = array();

if ($sifirst !== 'all') {
    $where[] = $DB->sql_like('u.firstname', ':sifirst', false);
    $where_params['sifirst'] = $sifirst.'%';
}

if ($silast !== 'all') {
    $where[] = $DB->sql_like('u.lastname', ':silast', false);
    $where_params['silast'] = $silast.'%';
}

$where = join(' AND ', $where);
$users = user_get_participants($course->id, $groupid, 0, $roleid, 0, -1, $searchkeywords, $where, $where_params, '', $page, $perpage);
$grandtotal = user_get_total_participants($course->id);
$total = user_get_total_participants($course->id, $groupid, 0, $roleid, 0, -1, $searchkeywords, $where, $where_params);

// Initials bar.
$prefixfirst = 'sifirst';
$prefixlast = 'silast';
$initialbar = $OUTPUT->initials_bar($sifirst, 'firstinitial', get_string('firstname'), $prefixfirst, $baseurl);
$initialbar .= $OUTPUT->initials_bar($silast, 'lastinitial', get_string('lastname'), $prefixlast, $baseurl);
echo $initialbar;

// Search utility heading.
echo $OUTPUT->heading(get_string('matched', 'format_cul') . get_string('labelsep', 'langconfig') . $total . '/' . $grandtotal, 3);

if ($total > $perpage) {     
    $pagingbar = new paging_bar($total, $page, $perpage, $baseurl);
    $pagingbar->pagevar = 'page';
    echo $OUTPUT->render($pagingbar);
}

$templates = [MODE_BRIEF => 'format_cul/briefphotoboard', MODE_USERDETAILS => 'format_cul/detailedphotoboard'];

$photoboard = new photoboard($COURSE, $users);
$templatecontext = $photoboard->export_for_template($OUTPUT);
echo $OUTPUT->render_from_template($templates[$mode], $templatecontext);

$PAGE->requires->js_call_amd('core_user/name_page_filter', 'init');
$perpageurl = clone($baseurl);

$perpageurl->remove_params('perpage');

if ($perpage == SHOW_ALL_PAGE_SIZE && $total > DEFAULT_PAGE_SIZE) {
    $perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');

} else if ($perpage < $total) {
    $perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $total)),
        array(), 'showall');
}

echo $OUTPUT->footer();
