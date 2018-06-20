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
$string['region-side-post'] = 'Right';
$string['configtitle'] = 'CUL Boost settings';

// General Settings
$string['generalsettings'] = 'General settings';
$string['logo'] = 'Logo';
$string['logodesc'] = 'Please upload your custom logo here if you want to add it to the header.<br>If you upload a logo it will replace the standard icon and name that was displayed by default.';
$string['customcss'] = 'Custom CSS';
$string['customcssdesc'] = 'Whatever CSS rules you add to this textarea will be reflected in every page, making for easier customization of this theme.';
$string['searchfor'] = 'Search for...';
$string['region-nav-settings'] = 'Settings (Navbar)';
$string['scrolltop'] = 'Back to top';
$string['footnote'] = 'Footnote';
$string['footnotedesc'] = 'Whatever you add to this textarea will be displayed in the footer throughout your Moodle site.';
$string['copyright'] = 'Copyright';
$string['copyrightdesc'] = 'The name of your organisation.';
$string['footerlinks'] = 'Footer Links';
$string['footerlinksdesc'] = 'Content area used for the bottom right of the footer.';
$string['on'] = 'Turn editing on';
$string['off'] = 'Turn editing off';

// Dashboard
$string['dashboard'] = 'Dashboard';
$string['region-dash-cl'] = 'Dashboard (Course Listing)';
$string['region-dash-full'] = 'Dashboard (Full Width)';
$string['region-dash-feed'] = 'Dashboard (Activity Feed)';
$string['region-dash-left'] = 'Dashboard (Left)';
$string['region-dash-middle'] = 'Dashboard (Middle)';
$string['region-dash-right'] = 'Dashboard (Right)';
$string['frontpageslider'] = 'Frontpage Slider';
$string['frontpagesliderdesc'] = 'Whatever images are entered in to the slidehow settings will appear on the frontpage slider';
$string['usefrontpageslider'] = 'Enable Frontpage slider';
$string['usefrontpagesliderdesc'] = 'If enabled this will display a slider on the frontpage';
$string['slideduration'] = 'Slide Duration';
$string['slidedurationdesc'] = 'Enter the slide duration (in seconds) for the slider.';
$string['slide'] = 'Slide {$a}';
$string['slideinfodesc'] = 'Enter the settings for your slide.';
$string['slideimage'] = 'Slide Image';
$string['slideimagedesc'] = 'Images for the slider should all be the same dimensions and high resolution';
$string['slidecaption'] = 'Slide Caption';
$string['slidecaptiondesc'] = 'Enter the caption text to use for the first slide';
$string['slidebuttontext'] = 'Slide Button Text';
$string['slidebuttontextdesc'] = 'Enter the text to appear in the slider button';
$string['slideurl'] = 'Slide Button Link';
$string['slideurldesc'] = 'Enter the target destination of the slide\'s button';
$string['slidenewtab'] = 'Open in New Tab';
$string['slidenewtabdesc'] = 'Open the button link in a new browser tab';
$string['pause'] = 'Pause';
$string['play'] = 'Play';
$string['viewevent'] = 'View Event';
$string['findamodule'] = 'Find a Module';
$string['findamoduledesc'] = 'Content area for the find a module section';
$string['findtitle'] = 'Find a Module Title';
$string['findtitledesc'] = 'Enter the title for the Find a Module Area';
$string['findcontent'] = 'Find a Module Description';
$string['findcontentdesc'] = 'Enter the text to appear in the description of the Find a Moudle section.';
$string['coursesearch'] = 'Search for modules...';
$string['recentmodules'] = 'Recently Accessed Modules';
$string['mymodules'] = 'My Modules';
$string['myfavourites'] = 'My Favourites';


// Course
$string['hideblocks'] = 'Hide Sidebar';
$string['showblocks'] = 'Show Sidebar';
$string['viewmore'] = 'View More';
$string['viewless'] = 'View Less';
$string['modulehidden'] = 'Module is hidden';

/* 
 * theme_cul strings
 */

// Gradebook disclaimer.
$string['gradebookdisclaimer'] = 'In accordance with City University policy, any marks shown here are provisional, subject to consideration by an Assessment board and approval by Senate.';
$string['gradebookdisclaimerdesc'] = 'Gradebook disclaimer';

// assign_renderer
$string['gradehidden'] = 'This grade and feedback (if present) is hidden in the Grader report and is not available to the student.';
$string['gradenothidden'] = 'This grade and feedback (if present) is visible in the Grader report and is available to the student. 
<br/>If you have anonymous marking applied, you need to select Reveal student identities to release the grade to students.';
$string['markingguide'] = 'Marking guide';
$string['rubric'] = 'Rubric';
$string['returntoassign'] = 'Return to assignment';

$string['mycourses'] = 'My Courses';
$string['archive'] = '2012-13 and earlier';
$string['archivedisplay'] = '2012-13 and earlier (link to archived Moodle)';
$string['settings'] = 'Settings';
$string['fullscreen'] = 'enter fullscreen';
$string['closefullscreen'] = 'exit fullscreen';
$string['mycourses'] = 'My Modules';
$string['myprofile'] = 'My Profile';
$string['morecourses'] = 'More Modules';
$string['coursemenu'] = 'Module menu';
$string['settings'] = 'Settings';

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
$string['access'] = 'Accessibility Help';
$string['accesslink'] = 'http://www.city.ac.uk/accessibility';

$string['tooltext'] = 'Tools';
$string['rollovertool'] = 'Rollover Tool';
$string['rollovertoollink'] = '/local/culrollover/';
$string['rolloverguidance'] = 'Rollover Guidance';
$string['rolloverguidancelink'] = 'http://bit.ly/cityunirollovertool';