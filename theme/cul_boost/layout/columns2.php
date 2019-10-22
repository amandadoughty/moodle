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

defined('MOODLE_INTERNAL') || die();

// user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');
require_once(dirname(__FILE__).'/includes/navbar.php');
require_once(dirname(__FILE__) . '/includes/courseheader.php');

$PAGE->set_popup_notification_allowed(false);
// Accessibility stuff.
$PAGE->requires->skip_link_to('accessibility', get_string('toaccessibility', 'theme_cul_boost'));

// if (isloggedin()) {
//     $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
// } else {
//     $navdraweropen = false;
// }
$extraclasses = [];
// if ($navdraweropen) {
//     $extraclasses[] = 'drawer-open-left';
// }
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$iscourse = $PAGE->pagelayout == 'course' && $COURSE->id != 1;

// Block region setup
$hasblocks = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$knownregionpost = $PAGE->blocks->is_known_region('side-post');
$regions = theme_cul_boost_bootstrap_grid($hasblocks);

if ($knownregionpost) {
    $blockshtml = $OUTPUT->blocks('side-post', $regions['post']);
}

// $blockshtml = $OUTPUT->blocks('side-post');
// $hasblocks = strpos($blockshtml, 'data-block=') !== false;
// $regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,    
    'haspageheading' => $haspageheading,
    'hasnavbar' => $hasnavbar,    
    'hasleftnavbar' => $hasleftnavbar,
    'hasnavsettings' => $hasnavsettings,
    'sidepostblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'classes' => $regions['content'],
    'bodyattributes' => $bodyattributes,
    'sectioninfo' => $sectioninfo,
    'courseimgurl' => $url,
    // 'navdraweropen' => $navdraweropen,
    // 'regionmainsettingsmenu' => $regionmainsettingsmenu,
    // 'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

// $templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_cul_boost/columns2', $templatecontext);

