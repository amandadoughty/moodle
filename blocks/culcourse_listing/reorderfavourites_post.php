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
 * Reset order of favourites functionality for culcourse_listing block.
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $PAGE;

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

require_sesskey();
require_login();
$PAGE->set_context(context_system::instance());
// Reorder confirmation hash.
$reorder = optional_param('reorder', '', PARAM_ALPHANUM);

if (!$reorder) {
    // Show the confirmation page.
    $strreordercheck = get_string('reorderfavourites', 'block_culcourse_listing');
    $strreorderfavouritescheck = get_string('reorderfavouritescheck',
            'block_culcourse_listing');
    $site = get_site();
    $PAGE->set_url('/blocks/culcourse_listing/reorderfavourites_post.php');
    $PAGE->navbar->add(get_string('mycourses'), new moodle_url('/my/index.php'));
    $PAGE->navbar->add($strreordercheck);
    $PAGE->set_title("$site->shortname: $strreordercheck");
    $PAGE->set_heading($site->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->confirm($strreorderfavouritescheck, 'reorderfavourites_post.php?reorder=1',
            '/my/index.php');
    echo $OUTPUT->footer();
    exit;
}
// The user has clicked the confirmation link, so reorder the favourites and redirect back.
$preferences = block_culcourse_listing_get_preferences();
$favourites = block_culcourse_listing_get_favourite_api_courses($preferences);
block_culcourse_listing_reorder_favourites_api($favourites);
redirect(new moodle_url('/my/index.php'));