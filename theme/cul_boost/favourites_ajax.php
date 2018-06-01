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
 * Helps moodle-theme_cul_boost-favourites to serve AJAX requests
 *
 * @see theme_cul_boost::favourites_ajax()
 *
 * @package    theme_cul_boost
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/theme/bootstrapbase/renderers/core_renderer.php');
require_once($CFG->dirroot . '/theme/cul_boost/classes/city_core_renderer.php');

if ($CFG->forcelogin) {
    require_login();
}

$PAGE->set_context(context_system::instance());
$citycore = $PAGE->get_renderer('theme_cul_boost', 'city_core');

echo json_encode($citycore->favourites_ajax());