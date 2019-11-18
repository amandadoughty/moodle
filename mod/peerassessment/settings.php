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
 * This file adds the settings pages to the navigation menu
 *
 * @package   mod_peerassessment
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


    $calculations = array('simple' => 'simple', 'outlier' => 'outlier');
    $settings->add(new admin_setting_configselect('peerassessment/calculationtype', get_string('calculationtype', 'peerassessment'),
        get_string('defaultcalculationtype', 'peerassessment'), 'simple', $calculations));

    $settings->add(new admin_setting_configtext('peerassessment/standard_deviation',
        get_string('standard_deviation', 'peerassessment'), get_string('defaultstandard_deviation', 'peerassessment'), '1.15'));

    $settings->add(new admin_setting_configtext('peerassessment/moderation', get_string('moderation', 'peerassessment'),
        get_string('defaultmoderation', 'peerassessment'), '2'));

    $multiplybyvalues = array(3 => 3, 4 => 4, 5 => 5);
    $settings->add(new admin_setting_configselect('peerassessment/multiplyby', get_string('multiplyby', 'peerassessment'),
        get_string('multiplyby', 'peerassessment'), 4, $multiplybyvalues));
