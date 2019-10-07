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
 * Move/order favourites functionality for culcourse_listing block.
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_sesskey();
require_login();
// The id of the course to be moved.
$source = required_param('source', PARAM_INT);
// This will be -1 for moving up the list and +1 for moving down.
$move = required_param('move', PARAM_INT);
$preferences = block_culcourse_listing_get_preferences();
// Calling this function ensures we only get exisitng courses visible to user
// as courses may have been deleted or hidden since being added to favourites.
$favourites = block_culcourse_listing_get_favourite_api_courses($preferences);
$sortorder = array_keys($favourites);
// Now resort based on new weight for chosen course.
$neworder = array();
$sourcekey = array_search($source, $sortorder);

if ($sourcekey === false) {
    print_error("invalidcourseid", null, null, $source);
}

$destination = $sourcekey + $move;

if ($destination < 0) {
    print_error("listcantmoveup");
} else if ($destination >= count($favourites)) {
    print_error("listcantmovedown");
}

unset($sortorder[$sourcekey]);

if ($move == -1) {
    // If the course has not been moved to the top of the list.
    if ($destination > 0) {
        // Add the courses that appear before $source to a new array.
        $neworder = array_slice($sortorder, 0, $destination, true);
    }
    // Add $source to the new array.
    $neworder[] = $source;
    // Get the courses that appear after $source.
    $remaningcourses = array_slice($sortorder, $destination);
    // Append the remaining courses to the new array.
    foreach ($remaningcourses as $courseid) {
        $neworder[] = $courseid;
    }

} else if (($move == 1)) {
    // Add the courses that appear before $source to a new array.
    $neworder = array_slice($sortorder, 0, $destination);
    // Append $source in it's new position.
    $neworder[] = $source;
    // If the course has not been moved to the bottom of the list.
    if (($destination) < count($favourites)) {
        // Get the courses that appear after $source.
        $remaningcourses = array_slice($sortorder, $destination);
        // Append the remaining courses to the new array.
        foreach ($remaningcourses as $courseid) {
            $neworder[] = $courseid;
        }
    }
}
// Update the user preference.
block_culcourse_listing_update_favourites_pref($neworder);
redirect(new moodle_url('/my/index.php'));