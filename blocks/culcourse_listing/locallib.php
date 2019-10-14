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
 * Helper functions for culcourse_listing block
 *
 * @package    block_culcourse501_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*** USER PREFERENCE FUNCTIONS ***/

/**
 * This function retireves and returns user preferences for widget settings
 *
 * @return array $preferences
 */
function block_culcourse_listing_get_preferences() {
    // Get all of the user preferences for this block.
    $preferences = array();
    $all = get_string('all', 'block_culcourse_listing');
    $userpref = 'culcourse_listing_filter_year';
    // You need to call this function if you wish to use the
    // set_user_preference method in javascript_static.php.
    user_preference_allow_ajax_update($userpref, PARAM_RAW);
    $preferences[$userpref] = get_user_preferences($userpref, $all);
    $userpref = 'culcourse_listing_filter_period';
    user_preference_allow_ajax_update($userpref, PARAM_RAW);
    $preferences[$userpref] = get_user_preferences($userpref, $all);
    $userpref = 'culcourse_listing_course_favourites';
    user_preference_allow_ajax_update($userpref, PARAM_RAW);
    $preferences[$userpref] = get_user_preferences($userpref);
    // These ones are not currently used, so just the default value is used.
    $userpref = 'culcourse_listing_category_collapsed';
    user_preference_allow_ajax_update($userpref, PARAM_BOOL);
    $preferences[$userpref] = get_user_preferences($userpref, true);
    $userpref = 'culcourse_listing_course_collapsed';
    user_preference_allow_ajax_update($userpref, PARAM_BOOL);
    $preferences[$userpref] = get_user_preferences($userpref, true);

    return $preferences;
}

/**
 * Sets the user preference culcourse_listing_course_favourites
 *
 * @param array $favourites of course ids in sort order
 */
function block_culcourse_listing_update_favourites_pref($favourites) {

    // If user favourites have been transferred to the 
    // Favourites API then do not recreate the user
    // preference.
    if (is_null($myfavourites = get_user_preferences('culcourse_listing_course_favourites'))) {
        return;
    } 

    try {
        set_user_preference('culcourse_listing_course_favourites', serialize($favourites));
        return true;
    } catch (exception $e) {
        return false;
    }    
}

/**
 * Sets the user preferences:
 * culcourse_listing_filter_year
 * culcourse_listing_filter_period
 *
 * @param array $filter containing year and period settings
 */
function block_culcourse_listing_update_filter_pref($filter) {
    set_user_preference('culcourse_listing_filter_year', $filter['year']);
    set_user_preference('culcourse_listing_filter_period', $filter['period']);
}

/*** FAVOURITE FUNCTIONS ***/

/**
 * Gets the users favourites as an array of core_course_list_element objects.
 *
 * @param array $preferences
 * @return array $favourites of core_course_list_element objects with course id as key,
 * or empty array if none.
 */
function block_culcourse_listing_get_old_favourite_courses($preferences) {
    global $CFG, $DB;    

    $favourites = array();
    $usersortorder = $preferences['culcourse_listing_course_favourites'];
    // Array of favourite course ids in sort order.
    $usersortorder = unserialize($usersortorder);

    if(!$usersortorder) {
        $usersortorder = array();
    }

    try {
        $courses = $DB->get_records_list('course', 'id', $usersortorder);
        $updated = false;

        foreach ($usersortorder as $key => $favourite) {
            // Check that the favourite course still exists.
            if (array_key_exists($favourite, $courses)) {
                // Get array of core_course_list_element objects in usersortorder.
                $course = new core_course_list_element($courses[$favourite]);

                if ($course->is_uservisible()) {
                    $favourites[$favourite] = $course;
                }

            } else {
                // Course has been deleted so remove it from the list.
                unset($favourites[$key]);
                $updated = true;
            }
        }
    } catch (exception $e) {
        // Update the user preference.
        block_culcourse_listing_update_favourites_pref(array());
        return $favourites;
    }

    if ($updated) {
        // Update the user preference.
        block_culcourse_listing_update_favourites_pref(array_keys($favourites));
    }

    return $favourites;
}

/**
 * Gets the users favourites as an array of core_course_list_element objects.
 *
 * @param array $preferences
 * @return array $favourites of core_course_list_element objects with course id as key,
 * or empty array if none.
 */
function block_culcourse_listing_get_favourite_courses($preferences) {
    global $CFG, $DB, $USER;

    // If the users favourites have not been transferred to the Favourite API then use the user preference.
    if (!is_null($preferences['culcourse_listing_course_favourites'])) {
        return block_culcourse_listing_get_old_favourite_courses($preferences);;
    }


    $usercontext = context_user::instance($USER->id);

    // Get the user favourites service, scoped to a single user (their favourites only).
    $userservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

    // Get the favourites, by type, for the user.
    $favourites = $userservice->find_favourites_by_type('core_course', 'courses');

    // Sort the favourites by order set and then last added.
    usort($favourites, function($a, $b) {
        /* We don't want null to count as zero because that will display last added courses first. */
        if (is_null($b->ordering)) {
            // $b->ordering = $a->ordering + 1;
            $ordering = 0;
        } else {
            $ordering = $a->ordering - $b->ordering;
        }

        if ($ordering === 0) {
            return $a->timemodified - $b->timemodified;
        }

        return $ordering;
    });

    $formattedcourses = [];

    foreach ($favourites as $favourite) {
        $course = get_course($favourite->itemid);
        $course = new core_course_list_element($course);

        if ($course->is_uservisible()) {
            $formattedcourses[$course->id] = $course;
        } 
    }

    // $formattedcourses = array_map(function($favourite) {
    //     $course = get_course($favourite->itemid);
    //     $course = new core_course_list_element($course);

    //     if ($course->is_uservisible()) {
    //         return $course;
    //     }        

    // }, $favourites);

    return $formattedcourses;
}

/**
 * Edits the user preference 'culcourse_listing_course_favourites'
 * Adds or deletes course id's
 *
 * @param string $action add or delete
 * @param int $cid course id
 * @param int $userid user id
 * @return array $favourites a sorted array of course id's
 */
function block_culcourse_listing_edit_favourites($action, $cid, $userid = 0) {
    global $USER;

    $favourites = [];

    if (is_null($myfavourites = get_user_preferences('culcourse_listing_course_favourites'))) {
        return true;
    } else {
        $favourites = unserialize($myfavourites);
    }

    switch ($action) {
        case 'add':
            // Original block user preference setting.
            if (!in_array($cid, $favourites)) {
                $favourites[] = $cid;
            }

            break;
        case 'remove':
            // Original block user preference setting.
            $key = array_search($cid, $favourites);

            if ($key !== false) {
                unset($favourites[$key]);
            }

            break;
        default:
            break;
    }
    // Update the user preference.
    $success = block_culcourse_listing_update_favourites_pref($favourites);

    if (!$success) {
        return false;
    }

    return $favourites;
}

/**
 * Edits the favourites api.
 *
 * @param string $action add or delete
 * @param int $cid course id
 * @param int $userid user id
 * @return array $favourites a sorted array of course id's
 */
function block_culcourse_listing_edit_favourites_api($action, $cid, $userid = 0) {
    global $USER;

    $coursecontext = \context_course::instance($cid);
    $usercontext = \context_user::instance($USER->id);
    $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

    $exists = $ufservice->favourite_exists('core_course', 'courses', $cid, $coursecontext);

    switch ($action) {
        case 'add':
            // New favourite api.
            if (!$exists) {
                $ufservice->create_favourite('core_course', 'courses', $cid, $coursecontext);
            }

            break;
        case 'remove':
            // New favourite api.
            if ($exists) {
                $ufservice->delete_favourite('core_course', 'courses', $cid, $coursecontext);
            }

            break;
        default:
            break;
    }
}

/**
 * Updates from the favourites api.
 *
 * @param string $action add or delete
 * @param int $cid course id
 * @return array $favourites a sorted array of course id's
 */
function block_culcourse_listing_update_from_favourites_api($action, $cid) {

    if ($cid && !is_null($myfavourites = get_user_preferences('culcourse_listing_course_favourites'))) {
        $favourites = (array)unserialize($myfavourites);

        if ($action == 'add') {
            $favourites[] = $cid;
        } else {
            if (($key = array_search($cid, $favourites)) !== false) {                
                unset($favourites[$key]);
            }
        }

        // Update the user preference.
        block_culcourse_listing_update_favourites_pref($favourites);
    }
  
    if ($cid) {
        if ($action == 'delete') {
            return ['action' => 'remove', 'cid' => $cid];
        } else if ($action == 'add') {
            return ['action' => 'add', 'cid' => $cid]; //@TODO return node
        }
    } else {
        return ['action' => 'error', 'cid' => null];
    }
}

/**
 * Clears the courses from the favourites api.
 *
 * @param string $action add or delete
 * @param int $cid course id
 * @param int $userid user id
 * @return array $favourites a sorted array of course id's
 */
function block_culcourse_listing_clear_favourites_api() {
    global $USER;

    $usercontext = \context_user::instance($USER->id);

    // Get the user favourites service, scoped to a single user (their favourites only).
    $userservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

    // Get the favourites, by type, for the user.
    $apifavourites = $userservice->find_favourites_by_type('core_course', 'courses');

    foreach ($apifavourites as $apifavourite) {
        try {
            $userservice->delete_favourite('core_course', 'courses', $apifavourite->itemid,
                    \context_course::instance($apifavourite->itemid));
        } catch (Exception $e) {
            print_error('clearfavouritesfail', 'block_culcourse_listing');
        }
    }
}

/**
 * Binary safe case-insensitive string comparison.
 *
 * @param array a first favourite core_course_list_element object
 * @param string b second favourite core_course_list_element object
 * @return Returns < 0 if a->displayname is less than b->displayname;
 * > 0 if a->displayname is greater than b->displayname, and 0 if they are equal.
 */
function block_culcourse_listing_strcasecmp($a, $b) {
    // Set the display name.
    $config = get_config('block_culcourse_listing');
    $displayname = $config->displayname;
    $displaynamea = !empty($a->$displayname) ? $a->$displayname : get_course_display_name_for_list($a);
    $displaynamea = format_string($displaynamea);
    $displaynameb = !empty($b->$displayname) ? $b->$displayname : get_course_display_name_for_list($b);
    $displaynameb = format_string($displaynameb);

    return strcasecmp($displaynamea, $displaynameb);
}

/**
 * Sorts the favourites by display name.
 * The new sort order is updated in the user preference setting.
 *
 * @param array $preferences
 * @param array $favourites of course ids
 * @return array $favourites of core_course_list_element objects
 */
function block_culcourse_listing_reorder_favourites($preferences, $favourites) {
    global $CFG, $DB, $USER;

    if (is_null($preferences['culcourse_listing_course_favourites'])) {
        return;
    }

    if (!$favourites) {
        return false;
    }

    $site = get_site();

    if (in_array($site->id, $favourites)) {
        unset($favourites[$site->id]);
    }

    $favourites = $DB->get_records_list('course', 'id', array_keys($favourites));

    foreach ($favourites as $favourite) {
        // Get array of core_course_list_element objects in usersortorder.
        $favourites[$favourite->id] = new core_course_list_element($favourite);
    }

    // Sort in aphabetical order.
    uasort($favourites, 'block_culcourse_listing_strcasecmp');
    // Update the user preference.
    block_culcourse_listing_update_favourites_pref(array_keys($favourites));

    return $favourites;
}

/**
 * Sorts the favourites by display name.
 * The new sort order is updated in the user preference setting.
 *
 * @param array $favourites of course ids
 * @return array $favourites of favourite objects
 */
function block_culcourse_listing_reorder_favourites_api($favourites) {
    global $CFG, $DB, $USER;

    $usercontext = \context_user::instance($USER->id);
    $favouritesrepo = new \core_favourites\local\repository\favourite_repository($usercontext);

    if (!$favourites) {
        return false;
    }

    $site = get_site();

    if (in_array($site->id, $favourites)) {
        unset($favourites[$site->id]);
    }

    // Sort in aphabetical order.
    uasort($favourites, 'block_culcourse_listing_strcasecmp');
    // Update the favourites.
    $i = 1;

    foreach ($favourites as $courseid => $course) {
        $coursecontext = \context_course::instance($courseid);

        try {
            $favourite = $favouritesrepo->find_favourite($USER->id, 'core_course', 'courses', $courseid,
                $coursecontext->id);

            $favourite->ordering = $i;
            $favouritesrepo->update($favourite);
            $i++;
        } catch (Exception $e) {
            debugging("The course with id $courseid is not in the 
                Favourite API",
              DEBUG_DEVELOPER);
        }   
    }

    return $favourites;
}

/*** FILTER FUNCTIONS ***/

/**
 * Updates the year and period arrays based on regex matching of a course field/attribute
 *
 * @param core_course_list_element $course
 * @param array $config
 * @param array $years passed by reference
 * @param array $periods passed by reference
 * @param array $daterangeperiods not used
 */
function block_culcourse_listing_get_filter_list_regex($course, $config, &$years, &$periods, $daterangeperiods) {
    $filterfield = $config->filterfield;
    $glue = $config->filterglue;

    if (isset($course->$filterfield)) {
        $elements = explode($glue, $course->$filterfield);

        foreach ($elements as $element) {
            if (preg_match($config->filteryearregex, $element, $matches)) {
                $years[$matches[0]] = $matches[0];
            }

            if (preg_match($config->filterperiodregex, $element, $matches)) {
                $periods[$matches[0]] = get_string($matches[0], 'block_culcourse_listing');
            }
        }
    }
}

/**
 * Updates the year and period arrays on comparing the course start date with ranges in
 * block_culcourse_listing_prds
 *
 * @param core_course_list_element $course
 * @param array $config
 * @param array $years
 * @param array $periods
 * @param array $daterangeperiods block_culcourse_listing_prds records.
 */
function block_culcourse_listing_get_filter_list_date($course, $config, &$years, &$periods, $daterangeperiods) {

    foreach($daterangeperiods as $daterangeperiod) {
        if (($course->startdate >= $daterangeperiod->startdate) && ($course->startdate < $daterangeperiod->enddate)) {
            if($daterangeperiod->type == 0) {
                $years[$daterangeperiod->name] = $daterangeperiod->name;
            } else {
                $periods[$daterangeperiod->name] = $daterangeperiod->name;
            }
        }
    }
}

/**
 * Checks state of filters and returns relevant class for CSS
 *
 * @param array $config
 * @param array $preferences
 * @return array class to add to category tree
 */
function block_culcourse_listing_get_filter_state($config, $preferences) {
    $all = get_string('all', 'block_culcourse_listing');
    $userpref = 'culcourse_listing_filter_year';
    $selectedyear = (isset($preferences[$userpref]) && $preferences[$userpref] != $all) ? true : false;
    $userpref = 'culcourse_listing_filter_period';
    $selectedperiod = (isset($preferences[$userpref]) && $preferences[$userpref] != $all) ? true : false;

    if (!$config->filterbyyear && !$config->filterbyperiod) {
        return array('class' => 'filter-off');
    } else if (($config->filterbyyear && $selectedyear ) || ($config->filterbyperiod && $selectedperiod)) {
        return array('class' => 'filter-on');
    } else {
        return array('class' => 'filter-off');
    }
}

/**
 * Builds the year/period filter form
 *
 * @param string $selectedyear
 * @param string $selectedperiod
 * @param array $years
 * @param array $periods
 * @return string $output html for the form
 */
function block_culcourse_listing_filter_form($config, $years, $periods, $selectedyear, $selectedperiod) {
    global $CFG, $PAGE;
    $output = '';
    // If the year or period filter are enabled then build the form.
    if ($config->filterbyyear || $config->filterbyperiod) {
        $class = 'hide';
        $url = new moodle_url($CFG->wwwroot . '/blocks/culcourse_listing/filter_post.php');
        $sesskey = sesskey();
        $output .= html_writer::empty_tag('input',
                array('type' => 'hidden', 'name' => 'sesskey', 'value' => $sesskey));

        if ($config->filterbyyear) {
            $yearstring = get_string('year', 'block_culcourse_listing') . ': ';
            $output .= html_writer::start_tag('span', array('class' => 'nowrap'));
            $output .= html_writer::label($yearstring, 'culcourse_listing_filter_year');
            $output .= html_writer::select($years, 'year', $selectedyear, '',
                    array('id' => 'culcourse_listing_filter_year'));
            $go = html_writer::empty_tag('input',
                    array('type' => 'submit', 'value' => get_string('go')));
            $output .= html_writer::tag('noscript', html_writer::tag('div', $go),
                    array('class' => 'inline'));
            $output .= html_writer::end_tag('span'); // End .nowrap.

            if (count($years) > 1) {
                $class = 'show';
            }
        }

        if ($config->filterbyperiod) {
            $periodstring = get_string('period', 'block_culcourse_listing') . ': ';
            $output .= html_writer::start_tag('span', array('class' => 'nowrap'));
            $output .= html_writer::label($periodstring, 'culcourse_listing_filter_period');
            $output .= html_writer::select($periods, 'period', $selectedperiod, '',
                    array('id' => 'culcourse_listing_filter_period'));
            $go = html_writer::empty_tag('input',
                    array('type' => 'submit', 'value' => get_string('go')));
            $output .= html_writer::tag('noscript', html_writer::tag('div', $go),
                    array('class' => 'inline'));
            $output .= html_writer::end_tag('span'); // End .nowrap.

            if (count($periods) > 1) {
                $class = 'show';
            }
        }

        $output = html_writer::tag('div', $output);
        $formattributes = array('method' => 'POST',
            'action' => $url,
            'id'     => 'culcourse_listing_filter',
            'class' => $class);
        $output = html_writer::tag('form', $output, $formattributes);
    }

    return $output;
}

/*** COURSE FUNCTIONS ***/

/**
 * Builds an array of the filter state for each course, based on the user filter
 * preferences 'culcourse_listing_filter_year' and 'culcourse_listing_filter_period'
 * and returns the array.
 *
 * @param array $courses array of stdClass courses that the user is enrolled on
 * @param array $config block admin settings
 * @param array $preferences user preferences
 * @param array $daterangeperiods block_culcourse_listing_prds records.
 * @return array $filteredcourseids with course id as key and value on/off (0/1)
 */
function block_culcourse_listing_get_filtered_course_ids($courses, $config, $preferences, $daterangeperiods) {
    $year = block_culcourse_listing_get_filtered_year($config, $preferences);
    $period = block_culcourse_listing_get_filtered_period($config, $preferences);
    $filteredcourseids = array();
    $filterfunction = 'block_culcourse_listing_set_' . $config->filtertype . '_filtered_course';

    foreach ($courses as $course) {
        $filteredcourseids[$course->id] = $filterfunction($course, $config, $year, $period, $daterangeperiods);
    }

    return $filteredcourseids;
}

/**
 * Returns the filter setting for year based on the user filter
 * preference 'culcourse_listing_filter_year'
 *
 * @param array $config block admin settings
 * @param array $preferences user preferences
 * @return string $year
 */
function block_culcourse_listing_get_filtered_year($config, $preferences) {
    $all = get_string('all', 'block_culcourse_listing');
    $userpref = 'culcourse_listing_filter_year';
    $year = $preferences[$userpref];
    $year = ($year == $all || !$config->filterbyyear) ? false : $year;

    return $year;
}

/**
 * Returns the filter setting for period based on the user filter
 * preference 'culcourse_listing_filter_period'
 *
 * @param array $config block admin settings
 * @param array $preferences user preferences
 * @return string $period
 */
function block_culcourse_listing_get_filtered_period($config, $preferences) {
    $all = get_string('all', 'block_culcourse_listing');
    $userpref = 'culcourse_listing_filter_period';
    $period = $preferences[$userpref];
    $period = ($period == $all || !$config->filterbyperiod) ? false : $period;

    return $period;
}

/**
 * This function uses the regex method to determine if the course is filtered or
 * not.
 *
 * @param stdClass $course
 * @param array $config block admin settings
 * @param string $year the year to filter by
 * @param string $period the period to filter by
 * @param array $daterangeperiods not used
 */
function block_culcourse_listing_set_regex_filtered_course($course, $config, $year, $period, $daterangeperiods) {
    $elements = explode($config->filterglue, $course->{$config->filterfield});
    $filtered = 1;

    if ($year && !in_array($year, $elements)) {
        $filtered = 0;
    }

    if ($period && !in_array($period, $elements)) {
        $filtered = 0;
    }

    return $filtered;
}

/**
 * This function compares the course startdate to the ranges set for each year and
 * period in block_culcourse_listing_prds, to determine if the course is filtered or
 * not.
 *
 * @param stdClass $course
 * @param array $config block admin settings
 * @param string $year the year to filter by
 * @param string $period the period to filter by
 * @param array $daterangeperiods block_culcourse_listing_prds records.
 */
function block_culcourse_listing_set_date_filtered_course($course, $config, $year, $period, $daterangeperiods) {
    $elements = [];
    $filtered = 1;

    foreach($daterangeperiods as $daterangeperiod) {
        if (($course->startdate >= $daterangeperiod->startdate) && ($course->startdate < $daterangeperiod->enddate)) {
            $elements[] = $daterangeperiod->name;
        }
    }

    if ($year && !in_array($year, $elements)) {
        $filtered = 0;
    }

    if ($period && !in_array($period, $elements)) {
        $filtered = 0;
    }

    return $filtered;
}

/**
 * Returns the course year and period based on regex matching of a course field/attribute
 *
 * @param core_course_list_element $course
 * @param array $config
 * @param array $daterangeperiods not used
 * @return array of year and period values for $course 
 */
function block_culcourse_listing_get_filter_meta_regex($course, $config, $daterangeperiods = null) {
    $courseyear = '';
    $courseperiod = '';
    $filterfield = $config->filterfield;
    $glue = $config->filterglue;

    if (isset($course->$filterfield)) {
        $elements = explode($glue, $course->$filterfield);
        $courseyears = preg_grep($config->filteryearregex, $elements);
        $courseyear = array_pop($courseyears);
        $courseperiods = preg_grep($config->filterperiodregex, $elements);
        $courseperiod = array_pop($courseperiods);
    }

    return array (
        'year' => $courseyear,
        'period' => $courseperiod
        );
}

/**
 * Returns the course year and period based on comparing the course start date with ranges in
 * culcourse_listing_periods
 *
 * @param core_course_list_element $course
 * @param array $config
 * @param array $daterangeperiods block_culcourse_listing_prds records.
 * @return array of year and period values for $course
 */
function block_culcourse_listing_get_filter_meta_date($course, $config, $daterangeperiods) {
    $courseyear = '';
    $courseperiod = '';

    foreach($daterangeperiods as $period) {
        if (($course->startdate >= $period->startdate) && ($course->startdate < $period->enddate)) {
            if($period->type == 0) {
                $courseyear = $period->name;
            } else {
                $courseperiod = $period->name;
            }
        }
    }

    return array (
        'year' => $courseyear,
        'period' => $courseperiod
        );
}

/*** CATEGORY FUNCTIONS ***/

/**
 * Builds an array of all of the categories of each course and the ancestor
 * categories,  and a second array of the filter state for each category.
 * Note that this method does not check if all catgegories are accessible by current user.
 * The renderer filters out those that are not.
 *
 * @param array $courses array of stdClass courses that the user is enrolled on
 * @param array $filteredcourseids
 * @return array:
 * array $categories coursecat categories with category id as key
 * array $filteredcategoryids with category id as key and value on/off (0/1)
 */
function block_culcourse_listing_get_categories($courses, $filteredcourseids) {
    global $DB;
    // An array of the direct parent categories of $courses.
    $parents = array();
    // An array of all the ascendant categories of each $parent.
    $ascendants = array();
    // An array of all the ascendant categories of $parents.
    $allascendants = array();
    // An array with ascendant category ids as the keys and a value of true/false (0/1)
    // for the filter flag.
    $filteredcategoryids = array();
    // An array of category ids that have courses that are shown by the filter.
    $filteredparentids = array();

    foreach ($courses as $course) {
        // Build an array of the parent category ids.
        $parents[] = $course->category;

        if ($filteredcourseids[$course->id]) {
            // Build an array of the parent category ids that have courses
            // that are shown by the filter. This is used to set the filter
            // flag for all the ascendant categories.
            $filteredparentids[] = $course->category;
        }
    }

    $filteredparentids = array_unique($filteredparentids);
    // Many courses will share the same parent. So get the unique parent ids and then
    // convert them into an array of core_course_category objects.
    $parents = core_course_category::get_many(array_unique($parents));

    foreach ($parents as $parent) {
        $ascendants = array();
        // If the category contains any course with a filter flag value of 1 then the category must
        // also have a filter value of 1. (But check that it has not already been set to 1
        // by virtue of being an ascendant of a category with a filter value of 1. We do not want to overwrite
        // a value set in the ascendants loop below).
        if (!isset($filteredcategoryids[$parent->id]) || !$filteredcategoryids[$parent->id]) {
            if (in_array($parent->id, $filteredparentids)) {
                $filteredcategoryids[$parent->id] = 1;
            } else {
                $filteredcategoryids[$parent->id] = 0;
            }
        }

        // Get an array of core_course_category objects for every ascendant of every $parent.
        $ascendants = core_course_category::get_many($parent->get_parents());

        foreach ($ascendants as $ascendant) {
            // If the ascendant category does not contain a course with a filter flag value of 1 then test
            // if it contains a category with a filter value of 1.
            if (!isset($filteredcategoryids[$ascendant->id]) || !$filteredcategoryids[$ascendant->id]) {
                // If the ascendant category contains any category with a filter flag value of 1 then the
                // ascendant category must also have a filter value of 1.
                if (in_array($parent->id, $filteredparentids)) {
                    $filteredcategoryids[$ascendant->id] = 1;
                } else {
                    $filteredcategoryids[$ascendant->id] = 0;
                }
            }
        }
        $allascendants = $ascendants + $allascendants;
    }

    // NB array_merge() is not used as it would result in the numeric (categoryid) keys being renumbered.
    // The + operand appends the second array to the first and in the case of duplicate numeric keys,
    // overwrites the value in the first array with the value in the second.
    $categories = $parents + $allascendants;

    return array($categories, $filteredcategoryids);
}

/**
 * Edits the periods set up to filter courses by eg Year/Term.
 *
 * @param string $action just delete at the moment.
 * @param int $cid course id
 * @return array $periods
 */
function block_culcourse_listing_edit_period($action, $id) {
    global $DB;

    $DB->delete_records('block_culcourse_listing_prds', array('id' => $id));
    $periods = $DB->get_records('block_culcourse_listing_prds');

    return $periods;
}

