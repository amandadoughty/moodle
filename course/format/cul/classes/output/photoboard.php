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
 * CUL Course Format Information
 *
 * A collapsed format that solves the issue of the 'Scroll of Death' when a course has many sections. All sections
 * except zero have a toggle that displays that section. One or more sections can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer.
 *
 * @package    course/format
 * @subpackage cul
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

namespace format_cul\output;

use renderer_base;
use renderable;
use templatable;
use stdClass;

defined('MOODLE_INTERNAL') || die();

// require_once($CFG->dirroot . '/course/format/cul/dashboard/locallib.php');

class photoboard implements templatable, renderable {

    /**
     * @var $course - The course instance.
     */
    public $course = null;

    /**
     * @var $course - The plugin settings.
     */
    public $users = [];

    /**
     * Constructor method, calls the parent constructor - MDL-21097
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct($course, $users = []) {
        global $PAGE;

        // $this->userisediting = $PAGE->user_is_editing();

        // if ($this->userisediting) {
        //     $adminurl = new \moodle_url('/course/format/cul/dashboard/quicklink_edit_ajax.php');
        //     $this->adminurl = $adminurl->out();
        // }

        $this->course = $course;
        $this->users = $users;
    }

    public function export_for_template(renderer_base $output) {
        $export = new stdClass();
        $export->course = $this->course;
        $export->users = $this->get_users($this->course);

        

        return $export;
    }


    public function get_users() {
        global $CFG, $USER, $OUTPUT, $DB;

        $course = $this->course;
        $xusers = [];
        $usersprinted = [];

        $courseformat = course_get_format($this->course)->get_format_options();

        if($courseformat['selectmoduleleaders']) {
            $moduleleaders = explode(',', $courseformat['selectmoduleleaders']);
        } else {
            $moduleleaders = [];
        }

        // Check to see if groups are being used in this course
        // and if so, set $currentgroup to reflect the current group.
        $groupmode    = groups_get_course_groupmode($this->course);   // Groups are being used.
        $currentgroup = groups_get_course_group($this->course, true);

        if (!$currentgroup) {      // To make some other functions work better later.
            $currentgroup  = null;
        }

        $context = \context_course::instance($course->id);
        // Get the hidden field list.
        if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
            $hiddenfields = [];
        } else {
            $hiddenfields = array_flip(explode(',', $CFG->hiddenuserfields));
            $hiddenfields['mobile'] = 0;
            $hiddenfields['webapps'] = 0;
            $hiddenfields['forumposts'] = 0;
            $hiddenfields['sendmessage'] = 0;
            $hiddenfields['allusergroups'] = 0;
        }


            foreach ($this->users as $user) {
                if (in_array($user->id, $usersprinted)) { // Prevent duplicates by r.hidden - MDL-13935.
                    continue;
                }
                $usersprinted[] = $user->id; // Add new user to the array of users printed.

                $xuser = $user;

                \context_helper::preload_from_record($user);

                
                $usercontext = \context_user::instance($user->id);
                $xuser->viewdetails = ($USER->id == $user->id) || has_capability('moodle/user:viewdetails', $context) || has_capability('moodle/user:viewdetails', $usercontext);

                


                $xuser->imghtml = $this->get_user_picture($user, $course);


                $moduleleaderstr = '';

                if (in_array($user->id, $moduleleaders)) {
                    $moduleleaderstr = get_string('moduleleader', 'format_cul');
                }

                $fullname = fullname($user, has_capability('moodle/site:viewfullnames', $context)) . $moduleleaderstr;

                if (has_capability('moodle/course:viewhiddenuserfields', $context)) {
                    $fullname = \html_writer::link(
                        new \moodle_url('/user/view.php', array('id' => $user->id, 'course' => $course->id)),
                        $fullname
                        );
                }

                $xuser->fullname = $fullname;

                // if (!empty($user->role)) {
                //     $row->cells[1]->text .= get_string('role') . get_string('labelsep', 'langconfig') . $user->role . '<br />';
                // }

                // if ($user->maildisplay == 1 or ($user->maildisplay == 2 and ($course->id != SITEID) and !isguestuser()) or
                //             has_capability('moodle/course:viewhiddenuserfields', $context) or
                //             or ($user->id == $USER->id)) {
                    
                // } else {
                //     $user->email = '';
                // }

                // foreach ($extrafields as $field) {
                //     if ($field === 'email') {
                //         // Skip email because it was displayed with different logic above
                //         // because this page is intended for students too.
                //         continue;
                //     }

                //     $row->cells[1]->text .= get_user_field_name($field) .
                //             get_string('labelsep', 'langconfig') . s($user->{$field}) . '<br />';
                // }

                if (isset($hiddenfields['mobile'])) {
                    $user->phone2 = '';
                }



                if (has_capability('moodle/course:viewhiddenuserfields', $context, $user)) {
                    $sql = 'SELECT shortname, data
                            FROM {user_info_data} uid
                            JOIN {user_info_field} uif
                            ON uid.fieldid = uif.id
                            WHERE uid.userid = :userid';

                    if ($result = $DB->get_records_sql($sql, array('userid' => $user->id))){
                        $xuser->stafftelephone = $result['stafftelephone']->data;
                        $xuser->staffofficehrs = $result['staffofficehrs']->data;
                        $xuser->stafflocation = $result['stafflocation']->data;
                    } else {
                        $xuser->stafftelephone = '';
                        $xuser->staffofficehrs = '';
                        $xuser->stafflocation = '';                
                    }

                    $xuser->course = $this->course->id;
                }

                $showgroups = !isset($hiddenfields['allusergroups']) || (isset($hiddenfields['allusergroups']) && has_capability('moodle/course:viewhiddenuserfields', $context, $user));

                if ($showgroups) {
                    if ($currentgroup) {
                        $group = groups_get_group($currentgroup);
                        $xuser->group = 'Groups: ' . $group->name . '<br/>';
                    } else {
                        // show all groups user belongs to:
                        $groups = groups_get_all_groups($this->course->id, $user->id);
                        $groupnames = array();

                        foreach ($groups as $group) {
                            $groupnames[] = $group->name;
                        }

                        if (count($groups)) {
                            $xuser->group = 'Groups: ' . implode(', ', $groupnames) . '<br/>';
                        }
                    }
                }

                if (!isset($hiddenfields['lastaccess'])) {
                    if ($user->lastaccess) {
                        $xuser->lastaccess = userdate($user->lastaccess);
                        $xuser->lastaccess .= '&nbsp; ('. format_time(time() - $user->lastaccess, $datestring) .')';
                    } else {
                        $xuser->lastaccess = get_string('never');
                    }
                } else {
                    $xuser->lastaccess = '';
                }


                $links = [];


                if (!isset($hiddenfields['webapps'])) {
                    // link to webapps, hacky check to see if user a student or not
                    // 99.9% student accounts start 'a%' and don't have id numbers starting 88
                    if ((substr($user->username, 0, 1) == 'a') && (substr($user->idnumber, 0, 2) <> '88')) {
                        $links[] = \html_writer::link(
                            new \moodle_url('https://webapps.city.ac.uk/sst/student/' . $user->idnumber),
                            get_string('linktowebapps', 'format_cul')
                            );
                    }
                }

                if (!isset($hiddenfields['forumposts'])) {
                    $links[] = \html_writer::link(
                                new \moodle_url('/mod/forum/user.php', array('id' => $user->id, 'course' => $course->id)),
                                get_string('forum-posts', 'format_cul')
                                );
                }

                if ($USER->id != $user->id && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $context) && !is_siteadmin($user->id)) {
                    $links[] = \html_writer::link(
                        new \moodle_url('/course/loginas.php', array('id' => $course->id, 'user' => $user->id, 'sesskey' => sesskey())),
                        get_string('loginas')
                        );
                }

                if (!isset($hiddenfields['sendmessage'])) {
                    $links[] = \html_writer::link(
                        new \moodle_url('/message/index.php', array('id' => $user->id, 'viewing' => 'course_' . $course->id)),
                        get_string('sendmessage', 'format_cul')
                        );
                }

                $xusers[] = $xuser;
            }
            return $xusers;
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
        if ($user->picture > 0) { // show photo from Moodle first if exists
            return $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
        } else { // then resort to Moodle grey man photo
            return $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
        }
    }

}