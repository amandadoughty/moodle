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
 * External xapi log store plugin
 *
 * @package    logstore_jisc_xapi
 * @copyright  2015 Jerrett Fowler <jfowler@charitylearning.org>
 *                  Ryan Smith <ryan.smith@ht2.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Endpoint.
    $settings->add(new admin_setting_configtext('logstore_jisc_xapi/endpoint',
        get_string('endpoint', 'logstore_jisc_xapi'), '',
        'http://your.domain.com/endpoint/location/', PARAM_URL));
    // Username.
    $settings->add(new admin_setting_configtext('logstore_jisc_xapi/username',
        get_string('username', 'logstore_jisc_xapi'), '', 'username', PARAM_TEXT));
    // Key or password.
    $settings->add(new admin_setting_configtext('logstore_jisc_xapi/password',
        get_string('password', 'logstore_jisc_xapi'), '', 'password', PARAM_TEXT));



    // Switch background batch mode on
    $settings->add(new admin_setting_configcheckbox('logstore_jisc_xapi/backgroundmode',
        get_string('backgroundmode', 'logstore_jisc_xapi'),
        get_string('backgroundmode_desc', 'logstore_jisc_xapi'), 0));

    $settings->add(new admin_setting_configtext('logstore_jisc_xapi/maxbatchsize',
        get_string('maxbatchsize', 'logstore_jisc_xapi'),
        get_string('maxbatchsize_desc', 'logstore_jisc_xapi'), 30, PARAM_INT));


        //privacy


        // whitelisting


      $settings->add(new admin_setting_configcheckbox('logstore_jisc_xapi/whitelistenabled',
                        'Whitelisting Students Enabled',
                        'Only allowed whitelisted moodle student ids to be sent', 0));

      $settings->add(new admin_setting_configtext('logstore_jisc_xapi/whitelist',
                                          'Whitelist of student ids (comma seperated)', '', '', PARAM_TEXT));

      $settings->add(new admin_setting_configcheckbox('logstore_jisc_xapi/whitelistcoursesenabled',
                          'Whitelisting Courses Enabled',
                          'Only allowed whitelisted moodle course ids to be sent', 0));

      $settings->add(new admin_setting_configtext('logstore_jisc_xapi/whitelistcourses',
                              'Whitelist of course ids (comma seperated)', '', '', PARAM_TEXT));

}
