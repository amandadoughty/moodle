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
 * A two column layout for the boost theme.
 *
 * @package   theme_cul_boost
 * @copyright 2019 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/../../../../message/lib.php');

$help = $OUTPUT->help_menu();
$custommenu = $OUTPUT->custom_menu();
$usermenu = $OUTPUT->user_menu();
$userinfo = $OUTPUT->user_info();
$messagepopover = core_message_render_navbar_output($this);

$headertemplatecontext = [
    'help' => $help,
    'custommenu' => $custommenu,
    'usermenu' => $usermenu,
    'userinfo' => $userinfo,
    'messagepopover' => $messagepopover
];