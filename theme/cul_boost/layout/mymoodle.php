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
require_once(dirname(__FILE__) . '/includes/header.php');
require_once(dirname(__FILE__) . '/includes/footer.php');

$PAGE->set_popup_notification_allowed(false);
$isloggedin = isloggedin();
// Accessibility stuff.
$OUTPUT->standard_head_html();
$PAGE->requires->skip_link_to('accessibility', get_string('toaccessibility', 'theme_cul_boost'));
$bodyattributes = $OUTPUT->body_attributes();
// Block region setup.
$hasblocks = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$knownregionpost = $PAGE->blocks->is_known_region('side-post');
$regions = theme_cul_boost_bootstrap_grid($hasblocks);
$blockshtml = '';

if ($knownregionpost) {
    $blockshtml = $OUTPUT->synergyblocks('side-post', $regions['post']);
}

// Main region setup.
$dashclasses = 'd-flex flex-wrap align-items-stretch mb-4';
$dashtopfull = $OUTPUT->synergyblocks('dash-top-full', 'col-12 col-md-12 ' . $dashclasses);
$dashcontent = $OUTPUT->synergyblocks('dash-content', 'col-12 col-md-8 ' . $dashclasses);
$dashside = $OUTPUT->synergyblocks('dash-side', 'col-12 col-md-4 ' . $dashclasses);
$dashleft = $OUTPUT->synergyblocks('dash-left', 'col-12 col-md-4 ' . $dashclasses);
$dashmiddle = $OUTPUT->synergyblocks('dash-middle', 'col-12 col-md-4 ' . $dashclasses);
$dashright = $OUTPUT->synergyblocks('dash-right', 'col-12 col-md-4 ' . $dashclasses);
$dashbottomfull = $OUTPUT->synergyblocks('dash-bottom-full', 'col-12 col-md-12 ' . $dashclasses);

$templatecontext = [    
    'output' => $OUTPUT,
    'isloggedin' => $isloggedin,   
    'sidepostblocks' => $blockshtml,
    'hasblocks' => $hasblocks,    
    'classes' => $regions['content'],
    'bodyattributes' => $bodyattributes,
    'dashtopfull' => $dashtopfull,
    'dashcontent' => $dashcontent,
    'dashside' => $dashside,
    'dashleft' => $dashleft,
    'dashmiddle' => $dashmiddle,
    'dashright' => $dashright,
    'dashbottomfull' => $dashbottomfull,
    'navbar' => $navbartemplatecontext,
    'header' => $headertemplatecontext,
    'footer' => $footertemplatecontext
];

echo $OUTPUT->render_from_template('theme_cul_boost/mymoodle', $templatecontext);

