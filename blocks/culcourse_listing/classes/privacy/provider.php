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
 * Privacy Subsystem implementation for block_culcourse_listing.
 *
 * @package    block_culcourse_listing
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_culcourse_listing\privacy;

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for block_culcourse_listing implementing null_provider.
 *
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements 
    // This plugin has data.
    \core_privacy\local\metadata\provider,

    // This plugin has some sitewide user preferences to export.
    \core_privacy\local\request\user_preference_provider
{
	
    /**
     * Returns meta data about this system.
     *
     * @param collection $itemcollection The initialised item collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {
        // There are several user preferences.
        $collection->add_user_preference('culcourse_listing_course_favourites', 'privacy:metadata:preference:culcourse_listing_course_favourites');
        $collection->add_user_preference('culcourse_listing_filter_year', 'privacy:metadata:preference:culcourse_listing_filter_year');
        $collection->add_user_preference('culcourse_listing_filter_period', 'privacy:metadata:preference:culcourse_listing_filter_period');

        return $collection;
    }

    /**
     * Store all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {
        global $DB;

        $preferences = get_user_preferences();

        foreach ($preferences as $name => $value) {
            $descriptionidentifier = null;
            $courseid = null;

            if (strpos($name, 'culcourse_listing_course_favourites') === 0) {
                $descriptionidentifier = 'privacy:request:preference:culcourse_listing_course_favourites';
                $decoded = unserialize($value);
                $courses = $DB->get_records_list('course', 'id', $decoded);
                $favourites = [];

                foreach ($courses as $course) {
                    $favourites[] = $course->fullname;
                }

                $favourites = join(', ', $favourites);

                writer::export_user_preference(
                    'block_culcourse_listing',
                    $name,
                    $value,
                    get_string($descriptionidentifier, 'block_culcourse_listing', (object) [
                        'favourites' => $favourites
                    ])
                );                
            }

            if (strpos($name, 'culcourse_listing_filter_year') === 0) {
                $descriptionidentifier = 'privacy:request:preference:culcourse_listing_filter_year';
 
                writer::export_user_preference(
                    'block_culcourse_listing',
                    $name,
                    $value,
                    get_string($descriptionidentifier, 'block_culcourse_listing', (object) [
                        'filteryear' => $value
                    ])
                );                
            }

            if (strpos($name, 'culcourse_listing_filter_period') === 0) {
                $descriptionidentifier = 'privacy:request:preference:culcourse_listing_filter_period';
 
                writer::export_user_preference(
                    'block_culcourse_listing',
                    $name,
                    $value,
                    get_string($descriptionidentifier, 'block_culcourse_listing', (object) [
                        'filterperiod' => get_string($value, 'block_culcourse_listing')
                    ])
                );                
            }
        }
    }
}
