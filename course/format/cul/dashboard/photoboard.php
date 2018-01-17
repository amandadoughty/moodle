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
 * @copyright 2015 Amanda Doughty
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

require_once('../../../../config.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/course/format/cul/dashboard/locallib.php');
require_once($CFG->dirroot . '/course/format/cul/lib.php');
// require_once($CFG->dirroot . '/course/renderer.php');

define('USER_SMALL_CLASS', 20);   // Below this is considered small.
define('USER_LARGE_CLASS', 200);  // Above this is considered large.
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);
define('MODE_BRIEF', 0);
define('MODE_USERDETAILS', 1);

$page         = optional_param('page', 0, PARAM_INT); // Which page to show.
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT); // How many per page.
$mode         = optional_param('mode', 1, PARAM_INT); // Use the MODE_ constants.
$accesssince  = optional_param('accesssince', 0, PARAM_INT); // Filter by last access. -1 = never.
$search       = optional_param('search', '', PARAM_RAW); // Make sure it is processed with p() or s() when sending to output!
$roleid       = optional_param('roleid', 0, PARAM_INT); // Optional roleid, 0 means all enrolled users (or all on the frontpage).
$contextid    = optional_param('contextid', 0, PARAM_INT); // One of this or.
$courseid     = optional_param('id', 0, PARAM_INT); // This are required.

$PAGE->set_url('/course/format/cul/dashboard/photoboard.php', array(
        'page' => $page,
        'perpage' => $perpage,
        'mode' => $mode,
        'accesssince' => $accesssince,
        'search' => $search,
        'roleid' => $roleid,
        'contextid' => $contextid,
        'id' => $courseid));

$PAGE->add_body_class('path-format-cul-photos');

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

$systemcontext = context_system::instance();
$isfrontpage = ($course->id == SITEID);

$courseformat = course_get_format($course)->get_format_options();

if($courseformat['selectmoduleleaders']) {
    $moduleleaders = explode(',', $courseformat['selectmoduleleaders']);
} else {
    $moduleleaders = array();
}

if ($isfrontpage) {
    error('Photoboard only works in courses');
} else {
    $PAGE->set_pagelayout('base');
    require_capability('moodle/course:viewparticipants', $context);
}

$rolenamesurl = new moodle_url('/course/format/cul/dashboard/photoboard.php', array('contextid' => $context->id, 'mode' => $mode));

if (has_capability('format/cul:viewallphotoboard', $context)) {
    $rolenames = role_fix_names(get_profile_roles($context), $context, ROLENAME_ALIAS, true);
    $rolenames[0] = get_string('allparticipants');
} else {
    $profileroles = get_profile_roles($context);
    $photoboardroles = explode(',', $CFG->profileroles);
    $ccroles = array();

    if (in_array($roleid, $photoboardroles)) {
        $ccroles[$roleid] = $profileroles[$roleid];
    }

    $rolenames = role_fix_names($ccroles, $context, ROLENAME_ALIAS, true);
}

// Make sure other roles may not be selected by any means.
if (empty($rolenames[$roleid])) {
    if (has_capability('format/cul:viewallphotoboard', $context) && !empty($rolenames[0])) {
        redirect ($rolenamesurl);
    } else {
        print_error('noparticipants');
    }
}

// No roles to display yet?
if (empty($rolenames)) {
    if (has_capability('moodle/role:assign', $context)) {
        redirect($CFG->wwwroot.'/'.$CFG->admin.'/roles/assign.php?contextid='.$context->id);
    } else {
        print_error('noparticipants');
    }
}

$event = \core\event\user_list_viewed::create(array(
    'objectid' => $course->id,
    'courseid' => $course->id,
    'context' => $context,
    'other' => array(
        'courseshortname' => $course->shortname,
        'coursefullname' => $course->fullname
    )
));
$event->trigger();

$strnever = get_string('never');

$datestring = new stdClass();
$datestring->year  = get_string('year');
$datestring->years = get_string('years');
$datestring->day   = get_string('day');
$datestring->days  = get_string('days');
$datestring->hour  = get_string('hour');
$datestring->hours = get_string('hours');
$datestring->min   = get_string('min');
$datestring->mins  = get_string('mins');
$datestring->sec   = get_string('sec');
$datestring->secs  = get_string('secs');

if ($mode !== null) {
    $mode = (int)$mode;
    $SESSION->userindexmode = $mode;
} else if (isset($SESSION->userindexmode)) {
    $mode = (int)$SESSION->userindexmode;
} else {
    $mode = MODE_BRIEF;
}

// Check to see if groups are being used in this course
// and if so, set $currentgroup to reflect the current group.
$groupmode    = groups_get_course_groupmode($course);   // Groups are being used.
$currentgroup = groups_get_course_group($course, true);

if (!$currentgroup) {      // To make some other functions work better later.
    $currentgroup  = null;
}

$isseparategroups = ($course->groupmode == SEPARATEGROUPS and !has_capability('moodle/site:accessallgroups', $context));

$PAGE->set_title("$course->shortname: ".get_string('participants'));
$PAGE->set_heading($course->fullname);
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('path-user');                     // So we can style it independently.
$PAGE->set_other_editing_capability('moodle/course:manageactivities');

echo $OUTPUT->header();

echo '<div class="userlist">';

if ($isseparategroups and (!$currentgroup) ) {
    // The user is not in the group so show message and exit.
    echo $OUTPUT->heading(get_string("notingroup"));
    echo $OUTPUT->footer();
    exit;
}

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url(
    '/course/format/cul/dashboard/photoboard.php',
    array(
        'contextid' => $context->id,
        'roleid' => $roleid,
        'id' => $course->id,
        'perpage' => $perpage,
        'accesssince' => $accesssince,
        'search' => s($search),
        'mode' => $mode
        )
    );

// Setting up tags.
if ($course->id && !$currentgroup) {
    $filtertype = 'course';
    $filterselect = $course->id;
} else {
    $filtertype = 'group';
    $filterselect = $currentgroup;
}

// Get the hidden field list.
if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
    $hiddenfields = array();  // Teachers and admins are allowed to see everything.
} else {
    $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
}

if (isset($hiddenfields['lastaccess'])) {
    // Do not allow access since filtering.
    $accesssince = 0;
}

$colnum = 0;
$menuchoices = Array();
$menuchoices[$colnum] = new html_table_cell();
$menuchoices[$colnum]->attrutes['class'] = 'center';
if ($mode == MODE_BRIEF) {
    $menuchoices[$colnum]->text = "Photo Grid";
} else {
    $menuchoices[$colnum]->text = '<a title="Photos" href=' . $baseurl . '&mode=' . MODE_BRIEF . '>Photo Grid</a>';
}

$colnum++;
$menuchoices[$colnum] = new html_table_cell();
$menuchoices[$colnum]->attrutes['class'] = 'center';
if ($mode == MODE_USERDETAILS) {
    $menuchoices[$colnum]->text = "Detailed List";
} else {
    $menuchoices[$colnum]->text = '<a title="Detailed List" href=' . $baseurl . '&mode=' . MODE_USERDETAILS . '>Detailed List</a>';
}

$colnum++;

$controlstable = new html_table();
$controlstable->cellspacing = 0;
$controlstable->data[] = $menuchoices;

echo html_writer::table($controlstable);

// Print settings and things in a table across the top.
$controlstable = new html_table();
$controlstable->attributes['class'] = 'controls';
$controlstable->cellspacing = 0;
$controlstable->data[] = new html_table_row();

if (has_capability('format/cul:viewallphotoboard', $context)) {
    // Print my course menus.
    if ($mycourses = enrol_get_my_courses()) {
        $courselist = array();
        $popupurl = new moodle_url('/course/format/cul/dashboard/photoboard.php?roleid='.$roleid.'&sifirst=&silast=');
        foreach ($mycourses as $mycourse) {
            $coursecontext = context_course::instance($mycourse->id);
            $courselist[$mycourse->id] = format_string($mycourse->shortname, true, array('context' => $coursecontext));
        }
        if (has_capability('moodle/site:viewparticipants', $systemcontext)) {
            unset($courselist[SITEID]);
            $courselist = array(SITEID => format_string($SITE->shortname, true, array('context' => $systemcontext))) + $courselist;
        }
        $select = new single_select($popupurl, 'id', $courselist, $course->id, null, 'courseform');
        $select->set_label(get_string('mycourses'));
        $controlstable->data[0]->cells[] = $OUTPUT->render($select);
    }

    if (!isset($hiddenfields['lastaccess'])) {
        // Get minimum lastaccess for this course and display a dropbox to filter by lastaccess going back this far.
        // We need to make it diferently for normal courses and site course.
        if (!$isfrontpage) {
            $minlastaccess = $DB->get_field_sql('SELECT min(timeaccess)
                                                   FROM {user_lastaccess}
                                                  WHERE courseid = ?
                                                        AND timeaccess != 0', array($course->id));
            $lastaccess0exists = $DB->record_exists('user_lastaccess', array('courseid' => $course->id, 'timeaccess' => 0));
        } else {
            $minlastaccess = $DB->get_field_sql('SELECT min(lastaccess)
                                                   FROM {user}
                                                  WHERE lastaccess != 0');
            $lastaccess0exists = $DB->record_exists('user', array('lastaccess' => 0));
        }

        $now = usergetmidnight(time());
        $timeaccess = array();
        $baseurl->remove_params('accesssince');

        // Makes sense for this to go first.
        $timeoptions[0] = get_string('selectperiod');

        // Days.
        for ($i = 1; $i < 7; $i++) {
            if (strtotime('-'.$i.' days', $now) >= $minlastaccess) {
                $timeoptions[strtotime('-'.$i.' days', $now)] = get_string('numdays', 'moodle', $i);
            }
        }
        // Weeks.
        for ($i = 1; $i < 10; $i++) {
            if (strtotime('-'.$i.' weeks', $now) >= $minlastaccess) {
                $timeoptions[strtotime('-'.$i.' weeks', $now)] = get_string('numweeks', 'moodle', $i);
            }
        }
        // Months.
        for ($i = 2; $i < 12; $i++) {
            if (strtotime('-'.$i.' months', $now) >= $minlastaccess) {
                $timeoptions[strtotime('-'.$i.' months', $now)] = get_string('nummonths', 'moodle', $i);
            }
        }
        // Try a year.
        if (strtotime('-1 year', $now) >= $minlastaccess) {
            $timeoptions[strtotime('-1 year', $now)] = get_string('lastyear');
        }

        if (!empty($lastaccess0exists)) {
            $timeoptions[-1] = get_string('never');
        }

        if (count($timeoptions) > 1) {
            $select = new single_select($baseurl, 'accesssince', $timeoptions, $accesssince, null, 'timeoptions');
            $select->set_label(get_string('usersnoaccesssince'));
            $controlstable->data[0]->cells[] = $OUTPUT->render($select);
        }
    }
}

$controlstable->data[0]->cells[] = groups_print_course_menu($course, $baseurl->out(), true);
echo html_writer::table($controlstable);

// Define a table showing a list of users in the current role selection.
$tablecolumns = array();
$tableheaders = array();
$tablecolumns[] = 'userpic';
$tablecolumns[] = 'fullname';

$extrafields = get_extra_user_fields($context);
$tableheaders[] = get_string('userpic');
$tableheaders[] = get_string('fullnameuser');

if ($mode === MODE_BRIEF) {
    foreach ($extrafields as $field) {
        $tablecolumns[] = $field;
        $tableheaders[] = get_user_field_name($field);
    }
}

if (!isset($hiddenfields['lastaccess'])) {
    $tablecolumns[] = 'lastaccess';
    if ($course->id == SITEID) {
        // Exception case for viewing participants on site home.
        $tableheaders[] = get_string('lastsiteaccess');
    } else {
        $tableheaders[] = get_string('lastcourseaccess');
    }
}

$table = new flexible_table('user-index-participants-' . $course->id);
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());
$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'participants');
$table->set_attribute('class', 'generaltable generalbox');
$table->sortable(true, 'lastname', SORT_ASC);
$table->no_sorting('roles');
$table->no_sorting('groups');
$table->no_sorting('groupings');
$table->no_sorting('select');

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'participants');
$table->set_attribute('class', 'generaltable generalbox');

$table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_HIDE    => 'shide',
            TABLE_VAR_SHOW    => 'sshow',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
            ));
$table->setup();

// list($esql, $params) = get_enrolled_sql($context, null, 0, true);
list($esql, $params) = get_enrolled_sql($context, null, $currentgroup, true);
$joins = array("FROM {user} u");
$userfields = array('username', 'email', 'city', 'country', 'lang', 'timezone', 'maildisplay', 'idnumber', 'phone2');
$mainuserfields = user_picture::fields('u', $userfields);
$extrasql = get_extra_user_fields_sql($context, 'u', '', $userfields);
$select = "SELECT $mainuserfields, COALESCE(ul.timeaccess, 0) AS lastaccess $extrasql";
$joins[] = "JOIN ($esql) e ON e.id = u.id"; // Course enrolled users only.
$joins[] = "LEFT JOIN {user_lastaccess} ul ON (ul.userid = u.id AND ul.courseid = :courseid)"; // Not everybody accessed course yet.
if ($accesssince) {
    $accesssincewhere = get_course_lastaccess_sql($accesssince);
}

// Performance hacks - we preload user contexts together with accounts.
$ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
$ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = u.id AND ctx.contextlevel = :contextlevel)";
$params['contextlevel'] = CONTEXT_USER;
$params['courseid'] = $course->id;
$select .= $ccselect;
$joins[] = $ccjoin;
$from = implode("\n", $joins);

// Save the params that will apply to any role.
$allroleparams = $params;
// We want to query both the current context and parent contexts.
list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

// Used same code as user/index.php but assigned count values for all roles.
foreach ($rolenames as $key => $rolename) {
    $wheres = array();

    if($accesssince) {
        $wheres[] = $accesssincewhere;
    }

    if ($key) {
        $wheres[] = "u.id IN (SELECT userid FROM {role_assignments} WHERE roleid = :roleid AND contextid $relatedctxsql)";
        $params = array_merge($params, array('roleid' => $key), $relatedctxparams);
    }

    if ($wheres) {
        $where = "WHERE " . implode(" AND ", $wheres);
    } else {
        $where = "";
    }

    $totalcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);

    if (!empty($search)) {
        $fullname = $DB->sql_fullname('u.firstname', 'u.lastname');
        $wheres[] = "(". $DB->sql_like($fullname, ':search1', false, false) .
                    " OR ". $DB->sql_like('email', ':search2', false, false) .
                    " OR ". $DB->sql_like('idnumber', ':search3', false, false) .") ";
        $params['search1'] = "%$search%";
        $params['search2'] = "%$search%";
        $params['search3'] = "%$search%";
    }

    list($twhere, $tparams) = $table->get_sql_where();

    if ($twhere) {
        $wheres[] = $twhere;
        $params = array_merge($params, $tparams);
    }

    if ($wheres) {
        $where = "WHERE " . implode(" AND ", $wheres);
    } else {
        $where = "";
    }

    if ($table->get_sql_sort()) {
        $sort = ' ORDER BY ' . $table->get_sql_sort();
    } else {
        $sort = '';
    }

    $matchcount = $DB->count_records_sql("SELECT COUNT(u.id) $from $where", $params);
    $countdisplay = ($matchcount < $totalcount)? $matchcount . '/' .  $totalcount: $totalcount;
    $roleoptions[$key] = $rolename . ' (' .  $countdisplay . ')';

    if ($roleid == $key) {
        $roletotalcount = $totalcount;
        $rolematchcount = $matchcount;
        $rolewhere = $where;
        $roleparams = $params;
    }
}

$table->initialbars(true);
$table->pagesize($perpage, $rolematchcount);

// List of users at the current visible page - paging makes it relatively short.
$userlist = $DB->get_recordset_sql("$select $from $rolewhere $sort", $roleparams, $table->get_page_start(), $table->get_page_size());

// If there are multiple Roles in the course, then show a drop down menu for switching.
if (count($rolenames) > 1) {
    echo '<div class="rolesform">';
    echo '<label for="rolesform_jump">'.get_string('roles').'&nbsp;</label>';
    echo $OUTPUT->single_select($rolenamesurl, 'roleid', $roleoptions, $roleid, null, 'rolesform');
    echo '</div>';

} else if (count($rolenames) == 1) {
    // When all users with the same role - print its name.
    echo '<div class="rolesform">';
    echo get_string('role').get_string('labelsep', 'langconfig');
    $rolename = reset($rolenames);
    echo $rolename;
    echo '</div>';
}

if ($roletotalcount > $perpage) {
    $firstinitial = $table->get_initial_first();
    $lastinitial  = $table->get_initial_last();
    $strall = get_string('all');
    $alpha  = explode(',', get_string('alphabet', 'langconfig'));

    // Bar of first initials.
    echo '<div class="initialbar firstinitial">'.get_string('firstname').' : ';
    if (!empty($firstinitial)) {
        echo '<a href="'.$baseurl->out().'&amp;sifirst=">'.$strall.'</a>';
    } else {
        echo '<strong>'.$strall.'</strong>';
    }

    foreach ($alpha as $letter) {
        if ($letter == $firstinitial) {
            echo ' <strong>'.$letter.'</strong>';
        } else {
            echo ' <a href="'.$baseurl->out().'&amp;sifirst='.$letter.'">'.$letter.'</a>';
        }
    }

    echo '</div>';

    // Bar of last initials.
    echo '<div class="initialbar lastinitial">'.get_string('lastname').' : ';
    if (!empty($lastinitial)) {
        echo '<a href="'.$baseurl->out().'&amp;silast=">'.$strall.'</a>';
    } else {
        echo '<strong>'.$strall.'</strong>';
    }

    foreach ($alpha as $letter) {
        if ($letter == $lastinitial) {
            echo ' <strong>'.$letter.'</strong>';
        } else {
            echo ' <a href="'.$baseurl->out().'&amp;silast='.$letter.'">'.$letter.'</a>';
        }
    }

    echo '</div>';

    $pagingbar = new paging_bar($rolematchcount, intval($table->get_page_start() / $perpage), $perpage, $baseurl);
    $pagingbar->pagevar = 'spage';
    echo $OUTPUT->render($pagingbar);
}

if ($mode === MODE_USERDETAILS) {    // Print detailed listing.
    if ($totalcount < 1) {
        echo $OUTPUT->heading(get_string('nothingtodisplay'));
    } else {
        if ($rolematchcount > 0) {
            $usersprinted = array();

            foreach ($userlist as $user) {
                if (in_array($user->id, $usersprinted)) { // Prevent duplicates by r.hidden - MDL-13935.
                    continue;
                }
                $usersprinted[] = $user->id; // Add new user to the array of users printed.

                context_helper::preload_from_record($user);

                $context = context_course::instance($course->id);
                $usercontext = context_user::instance($user->id);

                $countries = get_string_manager()->get_list_of_countries();

                // Get the hidden field list.
                if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
                    $hiddenfields = array();
                } else {
                    $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
                    $hiddenfields['mobile'] = 0;
                    $hiddenfields['webapps'] = 0;
                    $hiddenfields['forumposts'] = 0;
                    $hiddenfields['sendmessage'] = 0;
                    $hiddenfields['allusergroups'] = 0;
                }

                $table = new html_table();
                $table->attributes['class'] = 'userinfobox';

                $row = new html_table_row();
                $row->cells[0] = new html_table_cell();
                $row->cells[0]->attributes['class'] = 'left side';
                $userpic = get_user_picture($user, $course);
                $row->cells[0]->text = '<div class="galleryItem">' . $userpic . '</div>';

                $row->cells[1] = new html_table_cell();
                $row->cells[1]->attributes['class'] = 'content';

                $moduleleaderstr = '';

                if (in_array($user->id, $moduleleaders)) {
                    $moduleleaderstr = get_string('moduleleader', 'format_cul');
                }

                $fullname = $OUTPUT->container(fullname($user, has_capability('moodle/site:viewfullnames', $context)) . $moduleleaderstr, 'username');

                if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
                    $fullname = html_writer::link(
                        new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id)),
                        $fullname
                        );
                }

                $row->cells[1]->text = $fullname;
                $row->cells[1]->text .= $OUTPUT->container_start('info');

                if (!empty($user->role)) {
                    $row->cells[1]->text .= get_string('role') . get_string('labelsep', 'langconfig') . $user->role . '<br />';
                }

                if ($user->maildisplay == 1 or ($user->maildisplay == 2 and ($course->id != SITEID) and !isguestuser()) or
                            has_capability('moodle/course:viewhiddenuserfields', $context) or
                            in_array('email', $extrafields) or ($user->id == $USER->id)) {
                    $row->cells[1]->text .= get_string('email') . get_string('labelsep', 'langconfig') . html_writer::link("mailto:$user->email", $user->email) . '<br />';
                }

                foreach ($extrafields as $field) {
                    if ($field === 'email') {
                        // Skip email because it was displayed with different logic above
                        // because this page is intended for students too.
                        continue;
                    }

                    $row->cells[1]->text .= get_user_field_name($field) .
                            get_string('labelsep', 'langconfig') . s($user->{$field}) . '<br />';
                }

                if (!isset($hiddenfields['mobile'])) {
                    $row->cells[1]->text .= get_string('mobile', 'format_cul') . get_string('labelsep', 'langconfig') . $user->phone2 . '<br/>';
                }

                $stafftelephone = '';
                $staffofficehrs = '';
                $stafflocation = '';

                if (has_capability('moodle/course:viewhiddenuserfields', $context, $user)) {
                    $sql = 'SELECT uid.data as stafftelephone
                            FROM {user_info_data} uid
                            JOIN {user_info_field} uif
                            ON uid.fieldid = uif.id
                            AND uif.shortname = \'stafftelephone\'
                            WHERE uid.userid = :userid';

                    if ($result = $DB->get_record_sql($sql, array('userid' => $user->id))){
                        $stafftelephone = $result->stafftelephone;
                    }

                    $sql = 'SELECT uid.data as staffofficehrs
                            FROM {user_info_data} uid
                            JOIN {user_info_field} uif
                            ON uid.fieldid = uif.id
                            AND uif.shortname = \'staffofficehrs\'
                            WHERE uid.userid = :userid';

                    if ($result = $DB->get_record_sql($sql, array('userid' => $user->id))) {
                        $staffofficehrs = $result->staffofficehrs;
                    }

                    $sql = 'SELECT uid.data as stafflocation
                            FROM {user_info_data} uid
                            JOIN {user_info_field} uif
                            ON uid.fieldid = uif.id
                            AND uif.shortname = \'stafflocation\'
                            WHERE uid.userid = :userid';

                    if ($result = $DB->get_record_sql($sql, array('userid' => $user->id))) {
                        $stafflocation = $result->stafflocation;
                    }

                    $row->cells[1]->text .= get_string('phone') . get_string('labelsep', 'langconfig') . $stafftelephone . '<br/>';
                    $row->cells[1]->text .= get_string('officehours', 'block_cul_dashboard') . get_string('labelsep', 'langconfig') . $staffofficehrs . '<br/>';
                    $row->cells[1]->text .= get_string('buildinglocation', 'block_cul_dashboard') . get_string('labelsep', 'langconfig') . $stafflocation . '<br/>';
                }

                $showgroups = !isset($hiddenfields['allusergroups']) || (isset($hiddenfields['allusergroups']) && has_capability('moodle/course:viewhiddenuserfields', $context, $user));

                if ($showgroups) {
                    if ($currentgroup) {
                        $group = groups_get_group($currentgroup);
                        $row->cells[1]->text .= 'Groups: ' . $group->name . '<br/>';
                    } else {
                        // show all groups user belongs to:
                        $groups = groups_get_all_groups($COURSE->id, $user->id);
                        $groupnames = array();

                        foreach ($groups as $group) {
                            $groupnames[] = $group->name;
                        }

                        if (count($groups)) {
                            $row->cells[1]->text .= 'Groups: ' . implode(', ', $groupnames) . '<br/>';
                        }
                    }
                }

                if (!isset($hiddenfields['lastaccess'])) {
                    if ($user->lastaccess) {
                        $row->cells[1]->text .= get_string('lastaccess').get_string('labelsep', 'langconfig').userdate($user->lastaccess);
                        $row->cells[1]->text .= '&nbsp; ('. format_time(time() - $user->lastaccess, $datestring) .')';
                    } else {
                        $row->cells[1]->text .= get_string('lastaccess').get_string('labelsep', 'langconfig').get_string('never');
                    }
                }

                $row->cells[1]->text .= $OUTPUT->container_end();

                $row->cells[2] = new html_table_cell();
                $row->cells[2]->attributes['class'] = 'links';
                $row->cells[2]->text = '';

                $links = array();

                $row->cells[2] = new html_table_cell();
                $row->cells[2]->attributes['class'] = 'links';
                $row->cells[2]->text = '';

                if (!isset($hiddenfields['webapps'])) {
                    // link to webapps, hacky check to see if user a student or not
                    // 99.9% student accounts start 'a%' and don't have id numbers starting 88
                    if ((substr($user->username, 0, 1) == 'a') && (substr($user->idnumber, 0, 2) <> '88')) {
                        $links[] = html_writer::link(
                            new moodle_url('https://webapps.city.ac.uk/sst/student/' . $user->idnumber),
                            get_string('linktowebapps', 'format_cul')
                            );
                    }
                }

                if (!isset($hiddenfields['forumposts'])) {
                    $links[] = html_writer::link(
                                new moodle_url('/mod/forum/user.php', array('id' => $user->id, 'course' => $course->id)),
                                get_string('forum-posts', 'format_cul')
                                );
                }

                if ($USER->id != $user->id && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $context) && !is_siteadmin($user->id)) {
                    $links[] = html_writer::link(
                        new moodle_url('/course/loginas.php', array('id' => $course->id, 'user' => $user->id, 'sesskey' => sesskey())),
                        get_string('loginas')
                        );
                }

                if (!isset($hiddenfields['sendmessage'])) {
                    $links[] = html_writer::link(
                        new moodle_url('/message/index.php', array('id' => $user->id, 'viewing' => 'course_' . $course->id)),
                        get_string('sendmessage', 'format_cul')
                        );
                }

                $row->cells[2]->text .= implode('', $links);

                $table->data = array($row);
                echo html_writer::table($table);
            }

        } else {
            echo $OUTPUT->heading(get_string('nothingtodisplay'));
        }
    }

} else {
    $timeformat = get_string('strftimedate');

    if ($userlist) {
        $html = '<div class = "gallerycontainer">';
        $usersprinted = array();

        foreach ($userlist as $user) {
            if (in_array($user->id, $usersprinted)) { // Prevent duplicates by r.hidden - MDL-13935.
                continue;
            }

            $usersprinted[] = $user->id; // Add new user to the array of users printed.
            context_helper::preload_from_record($user);

            if ($user->lastaccess) {
                $lastaccess = format_time(time() - $user->lastaccess, $datestring);
            } else {
                $lastaccess = $strnever;
            }

            $usercontext = context_user::instance($user->id);
            $moduleleaderstr = '';

            if (in_array($user->id, $moduleleaders)) {
                $moduleleaderstr = get_string('moduleleader', 'format_cul');
            }

            if ($piclink = ($USER->id == $user->id || has_capability('moodle/user:viewdetails', $context) || has_capability('moodle/user:viewdetails', $usercontext))) {
                $profilelink = '<strong><a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id.'">'.fullname($user) . $moduleleaderstr . '</a></strong>';
            } else {
                $profilelink = '<strong>'.fullname($user) . $moduleleaderstr . '</strong>';
            }

            $userpic = get_user_picture($user, $course);
            $html .= '<div class="galleryItem">' . $userpic . '<br/>' . $profilelink . '</div>';
        }
    }

    $html = $html . "</div>";
    echo $html;

}

// Show a search box if all participants don't fit on a single screen.
if ($totalcount > $perpage) {
    echo '<form action="photoboard.php" class="searchform"><div><input type="hidden" name="id" value="'.$course->id.'" />';
    echo '<input type="hidden" name="roleid" value="' . $roleid . '" />';
    echo '<input type="hidden" name="mode" value="' . $mode . '" />';
    echo '<label for="search">' . get_string('search', 'search') . ' </label>';
    echo '<input type="text" id="search" name="search" value="'.s($search) . '" />&nbsp;<input type="submit" value="'.get_string('search').'" /></div></form>'."\n";
}

$perpageurl = clone($baseurl);
$perpageurl->remove_params('perpage');

if ($perpage == SHOW_ALL_PAGE_SIZE) {
    $perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');

} else if ($rolematchcount > 0 && $perpage < $rolematchcount) {
    $perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $rolematchcount)), array(), 'showall');
}

echo '</div>';  // Userlist.
echo $OUTPUT->footer();

if ($userlist) {
    $userlist->close();
}

/**
 * Returns SQL that can be used to limit a query to a period where the user last accessed a course..
 *
 * @param string $accesssince
 * @return string
 */
function get_course_lastaccess_sql($accesssince='') {
    if (empty($accesssince)) {
        return '';
    }
    if ($accesssince == -1) { // Never.
        return 'ul.timeaccess = 0';
    } else {
        return 'ul.timeaccess != 0 AND ul.timeaccess < ' . $accesssince;
    }
}

/**
 * Returns SQL that can be used to limit a query to a period where the user last accessed the system.
 *
 * @param string $accesssince
 * @return string
 */
function get_user_lastaccess_sql($accesssince='') {
    if (empty($accesssince)) {
        return '';
    }
    if ($accesssince == -1) { // Never.
        return 'u.lastaccess = 0';
    } else {
        return 'u.lastaccess != 0 AND u.lastaccess < ' . $accesssince;
    }
}

/**
 * Returns html for a user image.
 *
 * @param stdClass $user
 * @return string
 */
function get_user_picture($user, $course) {
    global $OUTPUT;
    // get photo from most appropriate place
    if ( $user->picture > 0) { // show photo from Moodle first if exists
        return $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
    } else { // then resort to Moodle grey man photo
        return $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
    }
}
