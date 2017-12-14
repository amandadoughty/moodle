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
 * Helper functions for CUL Upcoming Events block
 *
 * @package    block
 * @subpackage culupcoming_events
 * @copyright  2013 Tim Gagen <Tim.Gagen.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

require_once($CFG->dirroot . '/calendar/lib.php');

/**
 * Get the calendar view output. Function based on calendar_get_view.
 *
 * @param   \calendar_information $calendar The calendar being represented
 * @param   string  $view The type of calendar to have displayed
 * @param   bool    $includenavigation Whether to include navigation
 * @return  array[array, string]
 */
function block_culupcoming_get_view(\calendar_information $calendar, $tstart = 0, $tstartaftereventid = 0, $eventlimit = 5) {
    global $PAGE, $CFG;

    $renderer = $PAGE->get_renderer('core_calendar');
    $type = \core_calendar\type_factory::get_calendar_instance();

    // Calculate the bounds of the month.
    $calendardate = $type->timestamp_to_date_array($calendar->time);

    $date = new \DateTime('now', core_date::get_user_timezone_object(99));


        // Number of days in the future that will be used to fetch events.
        if (isset($CFG->calendar_lookahead)) {
            $defaultlookahead = intval($CFG->calendar_lookahead);
        } else {
            $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
        }
        $lookahead = get_user_preferences('calendar_lookahead', $defaultlookahead);

        // Maximum number of events to be displayed on upcoming view.
        $defaultmaxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;
        if (isset($CFG->calendar_maxevents)) {
            $defaultmaxevents = intval($CFG->calendar_maxevents);
        }


        $tstart = $type->convert_to_timestamp($calendardate['year'], $calendardate['mon'], $calendardate['mday'],
                $calendardate['hours']);
        $date->setTimestamp($tstart);
        $date->modify('+' . $lookahead . ' days');


    // We need to extract 1 second to ensure that we don't get into the next day.
    $date->modify('-1 second');
    $tend = $date->getTimestamp();

    list($userparam, $groupparam, $courseparam, $categoryparam) = array_map(function($param) {
        // If parameter is true, return null.
        if ($param === true) {
            return null;
        }

        // If parameter is false, return an empty array.
        if ($param === false) {
            return [];
        }

        // If the parameter is a scalar value, enclose it in an array.
        if (!is_array($param)) {
            return [$param];
        }

        // No normalisation required.
        return $param;
    }, [$calendar->users, $calendar->groups, $calendar->courses, $calendar->categories]);

    $events = \core_calendar\local\api::get_events(
        $tstart,
        $tend,
        null,
        null,
        $tstartaftereventid,
        null,
        $eventlimit,
        null,
        $userparam,
        $groupparam,
        $courseparam,
        $categoryparam,
        true,
        true,
        function ($event) {
            if ($proxy = $event->get_course_module()) {
                $cminfo = $proxy->get_proxied_instance();
                return $cminfo->uservisible;
            }

            if ($proxy = $event->get_category()) {
                $category = $proxy->get_proxied_instance();

                return $category->is_uservisible();
            }

            return true;
        }
    );

    $related = [
        'events' => $events,
        'cache' => new \core_calendar\external\events_related_objects_cache($events),
        'type' => $type,
    ];

    $data = [];
 
        $upcoming = new \core_calendar\external\calendar_upcoming_exporter($calendar, $related);
        $data = $upcoming->export($renderer);




    return [$data, ''];
}

/**
 * Retrieves and filters the calendar upcoming events and adds meta data
 *
 * @param int $lookahead the number of days to look ahead
 * @param int $courseid the course the block is displaying events for
 * @param int $lastid the id of the last event loaded
 * @param array|int $lastdate the date of the last event loaded
 * @param int $limitfrom the index to start from (for non-JS paging)
 * @param int $limitnum maximum number of events
 * @return array $more bool if there are more events to load, $output array of upcoming events
 */
function block_culupcoming_events_get_events(
    $lookahead = 365,
    $courseid = SITEID,
    $lastid = 0,
    $lastdate = 0,
    $limitfrom = 0,
    $limitnum = 5) {

    global $DB;

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $output = array();
    $processed = 0;
    list($filtercourse, $events) = block_culupcoming_events_get_all_events($lookahead, $course, $lastdate, $lastid, $limitnum);

    $events = $events->events;

    if ($events !== false) {
        // Gets the cached stuff for the current course, others are checked below.
        // $modinfo = get_fast_modinfo($courseid);
echo "Lastid $lastid";
        foreach ($events as $key => $event) {
//             unset($events[$key]);

//             // if (!empty($event->modulename)) {
//             //     if ($event->course->id == $courseid) {
//             //         if (isset($modinfo->instances[$event->modulename][$event->instance])) {
//             //             $cm = $modinfo->instances[$event->modulename][$event->instance];
//             //             if (!$cm->uservisible) {
//             //                 continue;
//             //             }
//             //         }
//             //     } else {
//             //         if (!$cm = get_coursemodule_from_instance($event->modulename, $event->instance)) {
//             //             continue;
//             //         }
//             //         if (!\core_availability\info_module::is_user_visible($cm)) {
//             //             continue;
//             //         }
//             //     }
//             // }
// echo "<br/>$event->id";
//             ++$processed;

//             if ($event->id == $lastid) {
//                 continue;
//             }

//             if ($processed <= $limitfrom) {
//                 continue;
//             }

//             if ($processed > ($limitnum + $limitfrom)) {
//                 break;
//             }

            $event = block_upcoming_events_add_event_metadata($event, $filtercourse);
            $output[] = $event;
        }
    }

    // Find out if there are more to display.
    // $more = false;
    // if ($events !== false) {

    //     foreach ($events as $event) {
    //         if (!empty($event->modulename)) {
    //             if ($event->courseid == $courseid) {
    //                 if (isset($modinfo->instances[$event->modulename][$event->instance])) {
    //                     $cm = $modinfo->instances[$event->modulename][$event->instance];
    //                     if (!$cm->uservisible) {
    //                         continue;
    //                     }
    //                 }
    //             } else {
    //                 if (!$cm = get_coursemodule_from_instance($event->modulename, $event->instance)) {
    //                     continue;
    //                 }
    //                 if (!\core_availability\info_module::is_user_visible($cm)) {
    //                     continue;
    //                 }
    //             }
    //         }

    //         $more = true;

    //         if ($more) {
    //             break;
    //         }
    //     }
    // }

    $more = true;

    return array($more, $output);
}

/* Gets the raw calendar upcoming events
 *
 * @param int $lookahead the number of days to look ahead
 * @param stClass $course the course the block is displaying events for
 * @param array|int $lastdate the date of the last event loaded
 * @return array $filterclass, $events
 */
function block_culupcoming_events_get_all_events ($lookahead, $course, $lastdate = 0, $lastid = 0, $limitnum = 5) {
    global $USER, $PAGE;

    $filtercourse = array();
    $courseshown = $course->id;
    // Filter events to include only those from the course we are in.
    if ($courseshown == SITEID) {
        $filtercourse = calendar_get_default_courses();
        list($courses, $groups, $user) = calendar_set_filters($filtercourse, true);
    } else {
        $filtercourse = array($courseshown => $course);
        list($courses, $groups, $user) = calendar_set_filters($filtercourse, true);
        $courses = array($courseshown);
        $user = false;
    }

    $processed = 0;
    $now = time(); // We 'll need this later.
    $usermidnighttoday = usergetmidnight($now);

    if ($lastdate) {
        $tstart = $lastdate;
    } else {
        $tstart = $usermidnighttoday;
    }

    // This function adds the lookahead (in seconds) plus one day (in seconds)
    // to the current timestamp.
    // It then deducts one second to get the resulting end date at 23:59.
    // The extra day is added to account for resetting the result to midnight.
    // Otherwise a lookahead setting of 1 day would give an end date of today at 23:59.
    $tend = usergetmidnight($now + DAYSECS * $lookahead + DAYSECS) - 1;
    // Get the events matching our criteria.
    $events = calendar_get_events($tstart, $tend, $user, $groups, $courses);

    $courseid = $PAGE->course->id;
        $categoryid = ($PAGE->context->contextlevel === CONTEXT_COURSECAT) ? $PAGE->category->id : null;
        $calendar = \calendar_information::create(time(), $courseid, $categoryid);
        list($data, $template) = block_culupcoming_get_view($calendar, $lastdate = 0, $lastid = 0, $limitnum = 5);
// die(var_dump($data));
    return array($filtercourse, $data);
}



/**
 * Gets the calendar upcoming event metadata
 *
 * @param stdClass $event
 * @return stdClass $event with additional attributes
 */
function block_upcoming_events_add_event_metadata($event, $filtercourse) {

    // calendar_add_event_metadata($event);
    $event->timeuntil = block_culupcoming_events_human_timing($event->timestart);
    $courseid  = is_numeric($event->course->id) ? $event->course->id : 0;

    $a = new stdClass();
    $a->name = $event->name;

    if ($courseid && $courseid != SITEID) {
        $a->course = block_culupcoming_events_get_course_displayname ($courseid, $filtercourse);
        $event->description = get_string('courseevent', 'block_culupcoming_events', $a);
    } else {
        $event->description = get_string('event', 'block_culupcoming_events', $a);
    }

    switch (strtolower($event->eventtype)) {
        case 'user':
            $event->img = block_culupcoming_events_get_user_img($event->userid);
            break;
        case 'course':
            $event->img = block_culupcoming_events_get_course_img($event->courseid, $filtercourse);
            break;
        case 'site':
            $event->img = block_culupcoming_events_get_site_img();
            break;
        default:
            $event->img = block_culupcoming_events_get_course_img($event->course->id, $filtercourse);
    }

    return $event;
}


/**
 * Function that compares a time stamp to the current time and returns a human
 * readable string saying how long until time stamp
 *
 * @param int $time unix time stamp
 * @return string representing time since message created
 */
function block_culupcoming_events_human_timing ($time) {
    // To get the time until that moment.
    $time = $time - time();
    $timeuntil = get_string('today');

    $tokens = array (
        31536000 => get_string('year'),
        2592000 => get_string('month'),
        604800 => get_string('week'),
        86400 => get_string('day'),
        3600 => get_string('hour'),
        60 => get_string('minute'),
        1 => get_string('second', 'block_culupcoming_events')
    );

    foreach ($tokens as $unit => $text) {

        if ($time < $unit) {
            continue;
        }

        $numberofunits = floor($time / $unit);
        $units = $numberofunits . ' ' . $text . (($numberofunits > 1) ? 's' : '');
        return get_string('time', 'block_culupcoming_events', $units);
    }

    return $timeuntil;
}

/**
 * Get the course display name
 * 
 * @param  int $courseid
 * @return string
 */
function block_culupcoming_events_get_course_displayname ($courseid, $filtercourse) {
    global $DB;

    if (!$courseid) {
        return '';
    } else if (array_key_exists($courseid, $filtercourse)) {
        $courseshortname = $filtercourse[$courseid]->shortname;
    } else {
        $course = $DB->get_record('course', array('id' => $courseid));
        $courseshortname = $course->shortname;
    }

    return $courseshortname;
}

/**
 * Get a course avatar
 * 
 * @param  int $courseid
 * @return string Image tag, wrapped in a hyperlink.
 */
function block_culupcoming_events_get_course_img ($courseid) {
    global $CFG, $DB, $PAGE, $OUTPUT;

    $courseid  = is_numeric($courseid) ? $courseid : null;
    $coursedisplayname = block_culupcoming_events_get_course_displayname ($courseid, array());

    if ($course = $DB->get_record('course', array('id' => $courseid))) {
        $courseimgrenderer = $PAGE->get_renderer('block_culupcoming_events', 'renderers_course_picture');
        $coursepic = new block_culupcoming_events_course_picture($course);
        $coursepic->link = true;
        $coursepic->class = 'coursepicture';
        $courseimg = $courseimgrenderer->render($coursepic);
    } else {
        $url = $OUTPUT->pix_url('u/f2');
        $attributes = array(
            'src' => $url,
            'alt' => get_string('pictureof', '', $coursedisplayname),
            'class' => 'courseimage'
        );
        $img = html_writer::empty_tag('img', $attributes);
        $attributes = array('href' => $CFG->wwwroot);
        $courseimg = html_writer::tag('a', $img, $attributes);
    }

    return $courseimg;
}

/**
 * Get a user avatar
 * 
 * @param  int $userid
 * @return string Image tag, possibly wrapped in a hyperlink.
 */
function block_culupcoming_events_get_user_img ($userid) {
    global $CFG, $DB, $OUTPUT;

    $userid  = is_numeric($userid) ? $userid : null;

    if ($user = $DB->get_record('user', array('id' => $userid))) {
        $userpic = new user_picture($user);
        $userpic->link = true;
        $userpic->class = 'personpicture';
        $userimg = $OUTPUT->render($userpic);
    } else {
        $url = $OUTPUT->pix_url('u/f2');
        $attributes = array(
            'src' => $url,
            'alt' => get_string('anon', 'block_culupcoming_events'),
            'class' => 'personpicture'
        );
        $img = html_writer::empty_tag('img', $attributes);
        $attributes = array('href' => $CFG->wwwroot);
        $userimg = html_writer::tag('a', $img, $attributes);
    }

    return $userimg;
}

/**
 * Get a site avatar
 * 
 * @return string full image tag, possibly wrapped in a link.
 */
function block_culupcoming_events_get_site_img () {

    $admins      = get_admins();
    $adminuserid = 2;

    foreach ($admins as $admin) {
        if ('admin' == $admin->username) {
            $adminuserid = $admin->id;
            break;
        }
    }

    $siteimg = block_culupcoming_events_get_user_img($adminuserid);

    return $siteimg;
}


/**
 * Reload the events including newer ones via ajax call
 * 
 * @param  int $courseid the course id
 * @param  int $lastdate the date of the last event loaded
 * @return array $events array of upcoming event events
 */
function block_culupcoming_events_ajax_reload($lookahead, $courseid, $lastid) {
    global $DB;

    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $output = array();
    list($filtercourse, $events) = block_culupcoming_events_get_all_events($lookahead, $course);

    if ($events !== false) {
        // Gets the cached stuff for the current course, others are checked below.
        $modinfo = get_fast_modinfo($course);

        foreach ($events as $key => $event) {
            unset($events[$key]);

            if (!empty($event->modulename)) {
                if ($event->course->id == $course->id) {
                    if (isset($modinfo->instances[$event->modulename][$event->instance])) {
                        $cm = $modinfo->instances[$event->modulename][$event->instance];
                        if (!$cm->uservisible) {
                            continue;
                        }
                    }
                } else {
                    if (!$cm = get_coursemodule_from_instance($event->modulename, $event->instance)) {
                        continue;
                    }
                    if (!\core_availability\info_module::is_user_visible($cm)) {
                        continue;
                    }
                }
            }
            $output[] = $event;
            // We only want the events up to the last one currently displayed
            // when we are reloading.
            if ($event->id == $lastid) {
                break;
            }
        }
    }

    foreach ($output as $key => $event) {
        $output[$key] = block_upcoming_events_add_event_metadata($event, $filtercourse);
    }

    // Find out if there are more to display.
    $more = false;
    if ($events !== false) {

        foreach ($events as $event) {
            if (!empty($event->modulename)) {
                if ($event->courseid == $course->id) {
                    if (isset($modinfo->instances[$event->modulename][$event->instance])) {
                        $cm = $modinfo->instances[$event->modulename][$event->instance];
                        if (!$cm->uservisible) {
                            continue;
                        }
                    }
                } else {
                    if (!$cm = get_coursemodule_from_instance($event->modulename, $event->instance)) {
                        continue;
                    }
                    if (!\core_availability\info_module::is_user_visible($cm)) {
                        continue;
                    }
                }
            }

            $more = true;

            if ($more) {
                break;
            }
        }
    }

    return array($more, $output);
}