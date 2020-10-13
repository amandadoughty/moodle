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
 * This file defines the admin settings for this plugin
 *
 * @package   peerworkcalculator_webpa
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$settings->add(new admin_setting_configtext(
    'peerworkcalculator_simplepa/simplepa_upperboundary',
    get_string('upperboundary', 'peerworkcalculator_simplepa'),
    get_string('upperboundary_help', 'peerworkcalculator_simplepa'),
    2.0,
    PARAM_RAW,
    5
));

$settings->add(new admin_setting_configtext(
    'peerworkcalculator_simplepa/simplepa_lowerboundary',
    get_string('lowerboundary', 'peerworkcalculator_simplepa'),
    get_string('lowerboundary_help', 'peerworkcalculator_simplepa'),
    -2.0,
    PARAM_RAW,
    5
));

$scales = get_scales_menu();

$settings->add(new admin_setting_configmultiselect(
    'peerworkcalculator_simplepa/availablescales',
    get_string('availablescales', 'mod_peerwork'),
    get_string('availablescales_help', 'mod_peerwork'),
    [],
    $scales
));

