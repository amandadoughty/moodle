<?php

/**
 * Edits the user preference 'culcourse_listing_course_favourites'
 * Adds or deletes course id's
 *
 * @param string $action add or delete
 * @param int $cid course id
 * @return array $favourites a sorted array of course id's
 */
function theme_cul_boost_edit_favourites($action, $cid) {
    $favourites = [];

    if (is_null($myfavourites = get_user_preferences('culcourse_listing_course_favourites'))) {
        return false;
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

    theme_cul_boost_update_favourites($favourites);

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
function theme_cul_boost_edit_favourites_api($action, $cid, $userid = 0) {
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
 * Sets user course favourites preference in culcourse_listing block
 *
 * @param array $favourites of course ids in sort order
 */
function theme_cul_boost_update_favourites($favourites) {
    // If user favourites have been transferred to the 
    // Favourites API then do not recreate the user
    // preference.
    if (is_null($myfavourites = get_user_preferences('culcourse_listing_course_favourites'))) {
        return true;
    } 

    try {
        set_user_preference('culcourse_listing_course_favourites', serialize($favourites));
        return true;
    } catch (exception $e) {
        return false;
    }
}

/**
 * Makes hidden course visible.
 *
 * @param int $cid course id 
 */
function theme_cul_boost_show_course($cid) {
    global $DB;

    $coursecontext = context_course::instance($cid);

    if (has_capability('moodle/course:update', $coursecontext)) { 
        return $DB->set_field('course', 'visible', 1, ['id' => $cid]);
    }

    return false;
}