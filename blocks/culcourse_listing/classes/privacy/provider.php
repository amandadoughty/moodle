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
	
	use \core_privacy\local\legacy_polyfill;

    /**
     * Returns meta data about this system.
     *
     * @param collection $itemcollection The initialised item collection to add items to.
     * @return collection A listing of user data stored through this system.
     */
    public static function _get_metadata($items) {
        // There are several user preferences.
        $items->add_user_preference('culcourse_listing_course_favourites', 'privacy:metadata:preference:culcourse_listing_course_favourites');
        $items->add_user_preference('culcourse_listing_filter_year', 'privacy:metadata:preference:culcourse_listing_filter_year');
        $items->add_user_preference('culcourse_listing_filter_period', 'privacy:metadata:preference:culcourse_listing_filter_period');

        return $items;
    }

    /**
     * Store all user preferences for the plugin.
     *
     * @param int $userid The userid of the user whose data is to be exported.
     */
    public static function _export_user_preferences($userid) {
        global $DB;

        $preferences = get_user_preferences();

        foreach ($preferences as $name => $value) {
            $descriptionidentifier = null;
            $courseid = null;

            if (strpos($name, 'culcourse_listing_course_favourites') === 0) {
                $descriptionidentifier = 'privacy:request:preference:culcourse_listing_course_favourites';
            }

            if ($descriptionidentifier !== null) {
                $decoded = json_decode($value);
                $decoded = (array)$decoded;
                $courses = $DB->get_records_list('courses', 'id', $decoded);
                $favourites = [];

                foreach ($courses as $course) {
                    $favourites[] = $course->fullname;
                }

                writer::export_user_preference(
                    'block_culcourse_listing',
                    $name,
                    $value,
                    get_string($descriptionidentifier, 'block_culcourse_listing', (object) [
                        'favourites' => join(',', $favourites)
                    ])
                );                
            }

            if (strpos($name, 'culcourse_listing_filter_year') === 0) {
                $descriptionidentifier = 'privacy:request:preference:culcourse_listing_filter_year';
            }

            if ($descriptionidentifier !== null) {
 
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
            }

            if ($descriptionidentifier !== null) {
 
                writer::export_user_preference(
                    'block_culcourse_listing',
                    $name,
                    $value,
                    get_string($descriptionidentifier, 'block_culcourse_listing', (object) [
                        'filterperiod' => $value
                    ])
                );                
            }
        }
    }
}
