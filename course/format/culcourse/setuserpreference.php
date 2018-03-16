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
 * This file is used to deliver a branch from the navigation structure
 * in XML format back to a page from an AJAX call
 *
 * @since Moodle 2.0
 * @package core
 * @copyright 2009 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

/** Include config */
require_once(__DIR__ . '/../../../config.php');

if (!empty($CFG->forcelogin)) {
    require_login();
}

// Get the name of the preference to update, and check that it is allowed.
$type = required_param('type', PARAM_RAW);

if (!isset($USER->ajax_updatable_user_prefs[$type])) {
    print_error('notallowedtoupdateprefremotely');
}

$value = required_param('value', PARAM_INT);

// Update.
if (isset($value)) {
    if (!set_user_preference($type, $value)) {
        print_error('errorsettinguserpref');
    }
    echo 'OK';
} else {
    header('HTTP/1.1 406 Not Acceptable');
    echo 'Not Acceptable';
}
