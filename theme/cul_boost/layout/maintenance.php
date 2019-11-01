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
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// defined('MOODLE_INTERNAL') || die();

// // user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
// require_once($CFG->libdir . '/behat/lib.php');

// $PAGE->set_popup_notification_allowed(false);
// $isloggedin = isloggedin();
// // Accessibility stuff.
// $OUTPUT->standard_head_html();
// $PAGE->requires->skip_link_to('accessibility', get_string('toaccessibility', 'theme_cul_boost'));
// $bodyattributes = $OUTPUT->body_attributes();
// // Block region setup
// $hasblocks = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
// $regions = theme_cul_boost_bootstrap_grid($hasblocks);

// $templatecontext = [    
//     'output' => $OUTPUT,
//     'isloggedin' => $isloggedin,
//     'classes' => $regions['content'],
//     'bodyattributes' => $bodyattributes,
// ];

// echo $OUTPUT->render_from_template('theme_cul_boost/maintenance', $templatecontext);


defined('MOODLE_INTERNAL') || die();

$templatecontext = [
    // We cannot pass the context to format_string, this layout can be used during
    // installation. At that stage database tables do not exist yet.
    'sitename' => format_string($SITE->shortname, true, ["escape" => false]),
    'output' => $OUTPUT
];

echo $OUTPUT->render_from_template('theme_cul_boost/maintenance', $templatecontext);

