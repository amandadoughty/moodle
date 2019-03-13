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
 * Unit tests for the block_culcourse_listing implementation of the privacy API.
 *
 * @package    block_culcourse_listing
 * @category   test
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\writer;
use \block_culcourse_listing\privacy\provider;

/**
 * Unit tests for the block_culcourse_listing implementation of the privacy API.
 *
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_culcourse_listing_privacy_testcase extends \core_privacy\tests\provider_testcase {

    /**
     * Basic setup for these tests.
     */
    public function setUp() {
        $this->resetAfterTest(true);
    }

    /**
     * Ensure that export_user_preferences returns no data if the user has no data.
     */
    public function test_export_user_preferences_not_defined() {
        $user = \core_user::get_user_by_username('admin');
        provider::export_user_preferences($user->id);

        $writer = writer::with_context(\context_system::instance());
        $this->assertFalse($writer->has_any_data());
    }

    /**
     * Test for provider::test_export_user_preferences().
     */
    public function test_export_user_preferences() {
        // Define a user preference.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $favourites = [5, 6021, 50];
        set_user_preference('culcourse_listing_course_favourites', serialize($favourites), $user);
        set_user_preference('culcourse_listing_filter_year', '2018-19', $user);
        set_user_preference('culcourse_listing_filter_period', 'PRD2', $user);

        // Validate exported data.
        provider::export_user_preferences($user->id);
        $context = context_user::instance($user->id);
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $prefs = $writer->get_user_preferences('block_culcourse_listing');
        $this->assertCount(3, (array) $prefs);
        $this->assertEquals(
            get_string('privacy:metadata:preference:culcourse_listing_course_favourites', 'block_culcourse_listing'),
            $prefs->culcourse_listing_course_favourites->description
        );
        $this->assertEquals($favourites, unserialize($prefs->culcourse_listing_course_favourites->value));
        $this->assertEquals(
            get_string('privacy:metadata:preference:culcourse_listing_filter_year', 'block_culcourse_listing'),
            $prefs->culcourse_listing_filter_year->description
        );
        $this->assertEquals('2018-19', $prefs->culcourse_listing_filter_year->value);
        $this->assertEquals(
            get_string('privacy:metadata:preference:culcourse_listing_filter_period', 'block_culcourse_listing'),
            $prefs->culcourse_listing_filter_period->description
        );
        $this->assertEquals('PRD2', $prefs->culcourse_listing_filter_period->value);
    }
}
