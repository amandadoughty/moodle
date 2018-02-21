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
    public function __construct(
        $course, 
        $users = [], 
        $mode = 1, 
        $unifiedfilter = null, 
        $initialbar = null,
        $pagingbar = null,
        $baseurl
        ) 
    {
        global $PAGE;

        // $this->userisediting = $PAGE->user_is_editing();

        // if ($this->userisediting) {
        //     $adminurl = new \moodle_url('/course/format/cul/dashboard/quicklink_edit_ajax.php');
        //     $this->adminurl = $adminurl->out();
        // }

        $this->course = $course;
        $this->users = $users;
        $this->mode = $mode;
        $this->unifiedfilter = $unifiedfilter;
        $this->initialbar = $initialbar;
        $this->pagingbar = $pagingbar;
        $this->baseurl = $baseurl;
    }

    public function export_for_template(renderer_base $output) {
        $export = new stdClass();
        $export->course = $this->course;
        $export->users = $this->get_users();
        $export->modes = $this->get_modes();
        $export->unifiedfilter = $this->unifiedfilter;
        $export->initialbar = $this->initialbar;
        $export->pagingbar = $this->pagingbar;

        

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
                        $timesince = format_time(time() - $user->lastaccess);
                        $xuser->lastaccess = userdate($user->lastaccess);
                        $xuser->lastaccess .= " ($timesince)";
                    } else {
                        $xuser->lastaccess = get_string('never');
                    }
                } else {
                    $xuser->lastaccess = '';
                }


                $links = [];
                $link = [];

                if (!isset($hiddenfields['forumposts'])) {
                    $link['link'] = \html_writer::link(
                                new \moodle_url('/mod/forum/user.php', array('id' => $user->id, 'course' => $course->id)),
                                get_string('forumposts', 'mod_forum')
                                );

                    $links[] = $link;
                }

                if ($USER->id != $user->id && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $context) && !is_siteadmin($user->id)) {
                    $link['link'] = \html_writer::link(
                        new \moodle_url('/course/loginas.php', array('id' => $course->id, 'user' => $user->id, 'sesskey' => sesskey())),
                        get_string('loginas')
                        );

                    $links[] = $link;
                }

                if (!isset($hiddenfields['sendmessage'])) {
                    $link['link'] = \html_writer::link(
                        new \moodle_url('/message/index.php', array('id' => $user->id, 'viewing' => 'course_' . $course->id)),
                        get_string('sendmessage', 'format_cul')
                        );

                    $links[] = $link;
                }

                $xuser->links = $links;

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
    public function get_user_picture($user, $course) {
        global $OUTPUT;
        // get photo from most appropriate place
        if ($user->picture > 0) { // show photo from Moodle first if exists
            return $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
        } else { // then resort to Moodle grey man photo
            return $OUTPUT->user_picture($user, array('size' => 100, 'courseid' => $course->id));
        }
    }

    public function get_modes() {
        $modes[MODE_BRIEF]['title'] = get_string('photogrid', 'format_cul');
        $modes[MODE_USERDETAILS]['title'] = get_string('detailedlist', 'format_cul');

        if ($this->mode == MODE_BRIEF) {
            $modes[MODE_BRIEF]['active'] = 1;
            $modes[MODE_BRIEF]['link'] = '#brief';
        } else {
            $modes[MODE_BRIEF]['active'] = 0;
            // $this->baseurl->param('mode', MODE_BRIEF);
            // $modes[MODE_BRIEF]['link'] = $this->baseurl->out();
            $modes[MODE_BRIEF]['link'] = '#brief';
        }

        if ($this->mode == MODE_USERDETAILS) {
            $modes[MODE_USERDETAILS]['active'] = 1;
            $modes[MODE_USERDETAILS]['link'] = '#detailed';
        } else {
            $modes[MODE_USERDETAILS]['active'] = 0;
            // $this->baseurl->param('mode', MODE_USERDETAILS);
            // $modes[MODE_USERDETAILS]['link'] = $this->baseurl->out();
            $modes[MODE_USERDETAILS]['link'] = '#detailed';
        }

        return $modes;
    }

}