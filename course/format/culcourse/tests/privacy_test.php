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
 * Unit tests for the format_culcourse implementation of the privacy API.
 *
 * @package    format_culcourse
 * @category   test
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// vendor/bin/phpunit course/format/culcourse/tests/privacy_test.php

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\writer;
use \format_culcourse\privacy\provider;

/**
 * Unit tests for the format_culcourse implementation of the privacy API.
 *
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_culcourse_privacy_testcase extends \core_privacy\tests\provider_testcase {

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
     * Ensure that export_user_preferences returns single preferences.
     * These preferences can be set on each course, but the value is shared in the whole site.
     */
    public function test_export_user_preferences_single() {
        // Define a user preference.
        $user = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $sectiontoggles = [];
        $sectiontoggles[3] = 1;
        $sectiontoggles = json_encode($sectiontoggles, true);
        $this->setUser($user);
        set_user_preference('format_culcourse_expanded' . $course->id, $sectiontoggles, $user);
        set_user_preference('format_culcourse_toggledash' . $course->id, false, $user);

        // Validate exported data.
        provider::export_user_preferences($user->id);
        $context = context_user::instance($user->id);
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $prefs = $writer->get_user_preferences('format_culcourse');
        $this->assertCount(2, (array) $prefs);
        $pref = $prefs->{'format_culcourse_expanded' . $course->id}->value;
        $this->assertEquals($sectiontoggles, $pref);
        $pref = $prefs->{'format_culcourse_toggledash' . $course->id}->value;
        $this->assertEquals(false, $pref);
    }
}
