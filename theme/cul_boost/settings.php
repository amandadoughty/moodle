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
 * CUL Boost.
 *
 * @package    theme_cul_boost
 * @copyright  2018 Stephen Sharpe, Synergy Learning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$settings = null;

defined('MOODLE_INTERNAL') || die;

if (!$ADMIN->locate('theme_cul_boost')) {
    $ADMIN->add('themes', new admin_category('theme_cul_boost', get_string('configtitle', 'theme_cul_boost')));
}

    require($CFG->dirroot.'/theme/cul_boost/settings/general.php');
