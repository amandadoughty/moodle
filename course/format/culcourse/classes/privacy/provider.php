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
 * Privacy Subsystem implementation for format_culcourse.
 *
 * @package    format_culcourse
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_culcourse\privacy;

use \core_privacy\local\request\writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Privacy Subsystem for format_culcourse implementing null_provider.
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
        $items->add_user_preference('format_culcourse_expanded', 'privacy:metadata:preference:format_culcourse_expanded');

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

            if (strpos($name, 'format_culcourse_expanded') === 0) {
                $descriptionidentifier = 'privacy:request:preference:format_culcourse_expanded';
                $courseid = substr($name, strlen('format_culcourse_expanded'));
            }

            if ($descriptionidentifier !== null) {
                $decoded = json_decode($value);
                $sectionstates = '';
                $modinfo = get_fast_modinfo($courseid);
                $course = $modinfo->get_course();

                if ($course) {
                    $sections = $modinfo->get_section_info_all();

                    foreach ($sections as $number => $section) {
                        if (isset($decoded->{$section->id}) && $decoded->{$section->id}) {
                            $sectionstate = get_string('expanded', 'format_culcourse');
                        } else {
                            $sectionstate = get_string('collapsed', 'format_culcourse');
                        }

                        if ($section->name) {
                            $sectionname = $section->name;
                        } else {
                            $sectionname = get_string('sectionname', 'format_culcourse');
                            $sectionname .= " $number";
                        }

                        $sectionstates .= "$sectionname: $sectionstate, ";
                    }                    

                    writer::export_user_preference(
                        'format_culcourse',
                        $name,
                        $value,
                        get_string($descriptionidentifier, 'format_culcourse', (object) [
                            'course' => $course->fullname,
                            'sectionstates' => $sectionstates,
                        ])
                    );
                }
            }
        }
    }
}
