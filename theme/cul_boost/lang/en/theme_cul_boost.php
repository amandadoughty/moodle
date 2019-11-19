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
                                                             
defined('MOODLE_INTERNAL') || die();

// Generic
$string['choosereadme'] = 'Theme CUL Boost is a child theme of Boost.';
$string['pluginname'] = 'CUL Boost';

// General Settings
$string['configtitle'] = 'CUL Boost';
$string['generalsettings'] = 'General settings';
$string['logo'] = 'Logo';
$string['logotext'] = 'City, University of London';
$string['logodesc'] = 'Please upload your custom logo here if you want to add it to the header.<br>If you upload a logo it will replace the standard icon and name that was displayed by default.';
$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Whatever CSS rules you add to this textarea will be reflected in every page, making for easier customization of this theme.';
$string['footnote'] = 'Footnote';
$string['footnotedesc'] = 'Whatever you add to this textarea will be displayed in the footer throughout your Moodle site.';
$string['copyright'] = 'Copyright';
$string['copyrightdesc'] = 'The name of your organisation.';
$string['copyrightdefault'] = 'City, University of London';
$string['footerlinks'] = 'Footer Links';
$string['footerlinksdesc'] = 'Content area used for the bottom right of the footer.';
$string['cookiepolicy'] = 'Policies';
$string['cookiepolicydesc'] = 'Choose the policy that determines if user has consented to tracking cookies.';
$string['years'] = 'Years ordered in dropdown';
$string['yearsdesc'] = 'Example: the number of years to show ordered in dropdown';
$string['gakey'] = 'Google analytics key';
$string['gakeydesc'] = 'Please enter you Google Analytics key';

// Dashboard
$string['dashboard'] = 'Dashboard';
$string['region-nav-settings'] = 'Settings (Navbar)';
$string['region-dash-top-full'] = 'Dashboard (Top Full Width)';
$string['region-dash-content'] = 'Dashboard (Main Content)';
$string['region-dash-side'] = 'Dashboard (Side Content)';
$string['region-dash-left'] = 'Dashboard (Left)';
$string['region-dash-middle'] = 'Dashboard (Middle)';
$string['region-dash-right'] = 'Dashboard (Right)';
$string['region-dash-bottom-full'] = 'Dashboard (Bottom Full Width)';
$string['region-side-post'] = 'Right';
$string['settings'] = 'Settings';

// $string['coursesearch'] = 'Search for modules...';
// $string['recentmodules'] = 'Recently Accessed Modules';
// $string['mymodules'] = 'My Modules';
// $string['myfavourites'] = 'My Favourites';


// Course.
$string['hideblocks'] = 'Hide Sidebar';
$string['showblocks'] = 'Show Sidebar';
$string['viewmore'] = 'View More';
$string['viewless'] = 'View Less';
$string['coursehidden'] = 'Module is hidden';
$string['showcourse'] = 'Show Module';
$string['confirmshowcourse'] = 'Are you sure you want to make this module visible to students?';
$string['courseshown'] = 'Module is now visible to students';

// CUL Course Listing Block.
$string['PRD1'] = 'Term 1';
$string['PRD2'] = 'Term 2';
$string['PRD3'] = 'Term 3';
$string['searchfor'] = 'Search for...';

// Gradebook disclaimer.
$string['gradebookdisclaimer'] = 'In accordance with City University policy, any marks shown here are provisional, subject to consideration by an Assessment board and approval by Senate.';
$string['gradebookdisclaimerdesc'] = 'Gradebook disclaimer';

// assign_renderer
$string['gradehidden'] = 'This grade and feedback (if present) is hidden in the Grader Report and is not available to the student.';
$string['gradenothidden'] = 'This grade and feedback (if present) is visible in the Grader Report and is available to the student. 
<br/>If you have anonymous marking applied, you need to select Reveal student identities to release the grade to students.';
$string['markingguide'] = 'Marking guide';
$string['rubric'] = 'Rubric';
$string['returntoassign'] = 'Return to assignment';

$string['toaccessibility'] = 'Skip to accessibility';
$string['access'] = 'Accessibility Help';
$string['accesslink'] = 'http://www.city.ac.uk/accessibility';
$string['scrolltop'] = 'Back to top';
$string['on'] = 'Turn editing on';
$string['off'] = 'Turn editing off';

// Menus
// $string['mycourses'] = 'My Courses';

// $string['fullscreen'] = 'enter fullscreen';
// $string['closefullscreen'] = 'exit fullscreen';
$string['myfavourites'] = 'My Favourites';
$string['mycourses'] = 'My Modules';
$string['mymodules'] = 'My Modules';
$string['myprofile'] = 'My Profile';
// $string['morecourses'] = 'More Modules';
$string['coursemenu'] = 'Module Menu';
// $string['settings'] = 'Settings';

$string['favouriteadd'] = 'Add to Favourites';
$string['favouriteremove'] = 'Remove from Favourites';
$string['favourites'] = 'Favourites';

$string['helptext'] = 'Help & Support';
$string['helptextdesc'] = 'You can configure a custom help menu here. Each line consists of some menu text, a link URL (optional), a tooltip title (optional) and a language code or comma-separated list of codes (optional, for displaying the line to users of the specified language only), separated by pipe characters. You can specify a structure using hyphens, and dividers can be used by adding a line of one or more # characters where desired. For example:
<pre>
Moodle community|https://moodle.org" target="_blank
-Moodle free support|https://moodle.org/support" target="_blank
-###
-Moodle development|https://moodle.org/development" target="_blank
--Moodle Docs|http://docs.moodle.org" target="_blank|Moodle Docs
--German Moodle Docs|http://docs.moodle.org/de" target="_blank|Documentation in German|de
#####
Moodle.com|http://moodle.com/
</pre>';
$string['studentguidance'] = 'Student Guidance';
$string['studentguidancelink'] = 'http://www.city.ac.uk/edtechhelp/student';
$string['staffguidance'] = 'Staff Guidance';
$string['staffguidancelink'] = 'http://www.city.ac.uk/edtechhelp/staff';
$string['tooltext'] = 'Tools';
$string['rollovertool'] = 'Rollover Tool';
$string['rollovertoollink'] = '/local/culrollover/';
$string['rolloverguidance'] = 'Rollover Guidance';
$string['rolloverguidancelink'] = 'http://bit.ly/cityunirollovertool';

// Privacy.
$string['privacy:metadata'] = 'The CUL Boost theme does not store any personal data about any user.';
