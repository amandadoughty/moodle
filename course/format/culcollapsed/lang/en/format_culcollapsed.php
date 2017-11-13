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
 * Collapsed Topics Information
 *
 * A topic based format that solves the issue of the 'Scroll of Death' when a course has many topics. All topics
 * except zero have a toggle that displays that topic. One or more topics can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer by a small
 * code change. Full installation instructions, code adaptions and credits are included in the 'Readme.md' file.
 *
 * @package    course/format
 * @subpackage culcollapsed
 * @version    See the value of '$plugin->version' in version.php.
 * @copyright  &copy; 2009-onwards G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @link       http://docs.moodle.org/en/Collapsed_Topics_course_format
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */
// Used by the Moodle Core for identifing the format and displaying in the list of formats for a course in its settings.
// Possibly legacy to be removed after Moodle 2.0 is stable.
$string['nameculcollapsed'] = 'CUL Collapsed Topics 2';
$string['formatculcollapsed'] = 'CUL Collapsed Topics 2';

// Used in format.php.
$string['culcollapsedtoggle'] = 'Toggle';
$string['culcollapsedsidewidthlang'] = 'en-28px';

// Toggle all - Moodle Tracker CONTRIB-3190.
$string['culcollapsedall'] = 'sections.';  // Leave as AMOS maintains only the latest translation - so previous versions are still supported.
$string['culcollapsedopened'] = 'Open all';
$string['culcollapsedclosed'] = 'Close all';

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages.
$string['sectionname'] = 'Section';
$string['pluginname'] = 'CUL Collapsed Topics 2';
$string['section0name'] = 'General';

// MDL-26105.
$string['page-course-view-culcollapsed'] = 'Any course main page in the collapsed topics format';
$string['page-course-view-culcollapsed-x'] = 'Any course page in the collapsed topics format';

// Moodle 2.3 Enhancement.
$string['hidefromothers'] = 'Hide section';
$string['showfromothers'] = 'Show section';
$string['currentsection'] = 'This section';
$string['editsection'] = 'Edit section';
$string['deletesection'] = 'Delete section';
$string['reinstate'] = 'Reinstate section';
$string['reinstatethissection'] = 'Reinstate section';
// These are 'sections' as they are only shown in 'section' based structures.
$string['markedthissection'] = 'This section is highlighted as the current section';
$string['markthissection'] = 'Highlight this section as the current section';
$string['viewonly'] = 'View only \'{$a->sectionname}\'';

// MDL-51802.
$string['editsectionname'] = 'Edit section name';
$string['newsectionname'] = 'New name for section {$a}';

// Reset.
$string['resetgrp'] = 'Reset:';
$string['resetallgrp'] = 'Reset all:';

// Layout enhancement - Moodle Tracker CONTRIB-3378.
$string['formatsettings'] = 'Format reset settings'; // CONTRIB-3529.
$string['formatsettingsinformation'] = '<br />To reset the settings of the course format to the defaults, click on the icon to the right.';
$string['setlayout'] = 'Set layout';

// Negative view of layout, kept for previous versions until such time as they are updated.
$string['setlayout_default'] = 'Default'; // 1.
$string['setlayout_no_toggle_section_x'] = 'No toggle section x'; // 2.
$string['setlayout_no_section_no'] = 'No section number'; // 3.
$string['setlayout_no_toggle_section_x_section_no'] = 'No toggle section x and section number'; // 4.
$string['setlayout_no_toggle_word'] = 'No toggle word'; // 5.
$string['setlayout_no_toggle_word_toggle_section_x'] = 'No toggle word and toggle section x'; // 6.
$string['setlayout_no_toggle_word_toggle_section_x_section_no'] = 'No toggle word, toggle section x and section number'; // 7.
// Positive view of layout.
$string['setlayout_all'] = "Toggle word, 'Topic x' / 'Week x' / 'Day x' and section number"; // 1.
$string['setlayout_toggle_word_section_number'] = 'Toggle word and section number'; // 2.
$string['setlayout_toggle_word_section_x'] = "Toggle word and 'Topic x' / 'Week x' / 'Day x'"; // 3.
$string['setlayout_toggle_word'] = 'Toggle word'; // 4.
$string['setlayout_toggle_section_x_section_number'] = "'Topic x' / 'Week x' / 'Day x' and section number"; // 5.
$string['setlayout_section_number'] = 'Section number'; // 6.
$string['setlayout_no_additions'] = 'No additions'; // 7.
$string['setlayout_toggle_section_x'] = "'Topic x' / 'Week x' / 'Day x'"; // 8.

$string['setlayoutelements'] = 'Elements';
$string['setlayoutstructure'] = 'Structure';
$string['setlayoutstructuretopic'] = 'Topic';
$string['setlayoutstructureweek'] = 'Week';
$string['setlayoutstructurelatweekfirst'] = 'Current week first';
$string['setlayoutstructurecurrenttopicfirst'] = 'Current topic first';
$string['setlayoutstructureday'] = 'Day';
$string['resetlayout'] = 'Layout'; // CONTRIB-3529.
$string['resetalllayout'] = 'Layouts';

// Colour enhancement - Moodle Tracker CONTRIB-3529.
$string['setcolour'] = 'Colour';
$string['colourrule'] = "Please enter a valid RGB colour, six hexadecimal digits.";
$string['settoggleforegroundcolour'] = 'Toggle foreground';
$string['settoggleforegroundhovercolour'] = 'Toggle foreground hover';
$string['settogglebackgroundcolour'] = 'Toggle background';
$string['settogglebackgroundhovercolour'] = 'Toggle background hover';
$string['resetcolour'] = 'Colour';
$string['resetallcolour'] = 'Colours';

// Columns enhancement.
$string['setlayoutcolumns'] = 'Columns';
$string['one'] = 'One';
$string['two'] = 'Two';
$string['three'] = 'Three';
$string['four'] = 'Four';
$string['setlayoutcolumnorientation'] = 'Column orientation';
$string['columnvertical'] = 'Vertical';
$string['columnhorizontal'] = 'Horizontal';

// MDL-34917 - implemented in M2.5 but needs to be here to support M2.4- versions.
$string['maincoursepage'] = 'Main course page';

// Help.
$string['setlayoutelements_help'] = 'How much information about the toggles / sections you wish to be displayed.';
$string['setlayoutstructure_help'] = "The layout structure of the course.  You can choose between:<br />'Topics' - where each section is presented as a topic in section number order.<br />'Weeks' - where each section is presented as a week in ascending week order from the start date of the course.<br />'Current week first' - which is the same as weeks but the current week is shown at the top and preceding weeks in descending order are displayed below except in editing mode where the structure is the same as 'Weeks'.<br />'Current topic first' - which is the same as 'Topics' except that the current topic is shown at the top if it has been set.<br />'Day' - where each section is presented as a day in ascending day order from the start date of the course.";
$string['setlayout_help'] = 'Contains the settings to do with the layout of the format within the course.';
$string['resetlayout_help'] = 'Resets the layout element, structure, columns, icon position, one section and shown section summary to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalllayout_help'] = 'Resets the layout element, structure, columns, icon position, one section and shown section summary to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics \'format.';
// Moodle Tracker CONTRIB-3529.
$string['setcolour_help'] = 'Contains the settings to do with the colour of the format within the course.';
$string['settoggleforegroundcolour_help'] = 'Sets the colour of the text on the toggle.';
$string['settoggleforegroundhovercolour_help'] = 'Sets the colour of the text on the toggle when the mouse moves over it.';
$string['settogglebackgroundcolour_help'] = 'Sets the background colour of the toggle.';
$string['settogglebackgroundhovercolour_help'] = 'Sets the background colour of the toggle when the mouse moves over it.';
$string['resetcolour_help'] = 'Resets the colours and opacities to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetallcolour_help'] = 'Resets the colours and opacities to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
// Columns enhancement.
$string['setlayoutcolumns_help'] = 'How many columns to use.';
$string['setlayoutcolumnorientation_help'] = 'Vertical - Sections go top to bottom.<br />Horizontal - Sections go left to right.';

// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Number of sections';
$string['ctreset'] = 'Course format reset options';
$string['ctreset_help'] = 'Reset to course format defaults.';

// Toggle alignment - CONTRIB-4098.
$string['settogglealignment'] = 'Toggle text alignment';
$string['settogglealignment_help'] = 'Sets the alignment of the text in the toggle.';
$string['left'] = 'Left';
$string['center'] = 'Centre';
$string['right'] = 'Right';
$string['resettogglealignment'] = 'Toggle alignment';
$string['resetalltogglealignment'] = 'Toggle alignments';
$string['resettogglealignment_help'] = 'Resets the toggle alignment to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalltogglealignment_help'] = 'Resets the toggle alignment to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';

// Icon position - CONTRIB-4470.
$string['settoggleiconposition'] = 'Icon position';
$string['settoggleiconposition_help'] = 'States that the icon should be on the left or the right of the toggle text.';
$string['defaulttoggleiconposition'] = 'Icon position';
$string['defaulttoggleiconposition_desc'] = 'States if the icon should be on the left or the right of the toggle text.';

// Icon set enhancement.
$string['settoggleiconset'] = 'Icon set';
$string['settoggleiconset_help'] = 'Sets the icon set of the toggle.';
$string['settoggleallhover'] = 'Toggle all icon hover';
$string['settoggleallhover_help'] = 'Sets if the toggle all icons will change when the mouse moves over them.';
$string['arrow'] = 'Arrow';
$string['bulb'] = 'Bulb';
$string['cloud'] = 'Cloud';
$string['eye'] = 'Eye';
$string['folder'] = 'Folder';
$string['groundsignal'] = 'Ground signal';
$string['led'] = 'Light emitting diode';
$string['point'] = 'Point';
$string['power'] = 'Power';
$string['radio'] = 'Radio';
$string['smiley'] = 'Smiley';
$string['square'] = 'Square';
$string['sunmoon'] = 'Sun / Moon';
$string['switch'] = 'Switch';
$string['resettoggleiconset'] = 'Toggle icon set';
$string['resetalltoggleiconset'] = 'Toggle icon sets';
$string['resettoggleiconset_help'] = 'Resets the toggle icon set and toggle all hover to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalltoggleiconset_help'] = 'Resets the toggle icon set and toggle all hover to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';

// One section enhancement.
$string['onesection'] = 'One section';
$string['onesection_help'] = 'States if only one section should be open at any given time.  Note: Ignored when editing to allow activities and resources to be moved around the sections.';
$string['defaultonesection'] = 'One section';
$string['defaultonesection_desc'] = "States if only one section should be open at any given time.  Note: Ignored when editing to allow activities and resources to be moved around the sections.";
// Site Administration -> Plugins -> Course formats -> Collapsed Topics.
$string['defaultheadingsub'] = 'Defaults';
$string['defaultheadingsubdesc'] = 'Default settings';
$string['configurationheadingsub'] = 'Configuration';
$string['configurationheadingsubdesc'] = 'Configuration settings';

$string['off'] = 'Off';
$string['on'] = 'On';
$string['defaultlayoutelement'] = 'Layout';
// Negative view of layout, kept for previous versions until such time as they are updated.
$string['defaultlayoutelement_desc'] = "The layout setting can be one of:<br />'Default' with everything displayed.<br />No 'Topic x' / 'Week x' / 'Day x'.<br />No section number.<br />No 'Topic x' / 'Week x' / 'Day x' and no section number.<br />No 'Toggle' word.<br />No 'Toggle' word and no 'Topic x' / 'Week x' / 'Day x'.<br />No 'Toggle' word, no 'Topic x' / 'Week x' / 'Day x' and no section number.";
// Positive view of layout.
$string['defaultlayoutelement_descpositive'] = "The layout setting can be one of:<br />Toggle word, 'Topic x' / 'Week x' / 'Day x' and section number.<br />Toggle word and 'Topic x' / 'Week x' / 'Day x'.<br />Toggle word and section number.<br />'Topic x' / 'Week x' / 'Day x' and section number.<br />Toggle word.<br />'Topic x' / 'Week x' / 'Day x'.<br />Section number.<br />No additions.";

$string['defaultlayoutstructure'] = 'Structure configuration';
$string['defaultlayoutstructure_desc'] = "The structure setting can be one of:<br />Topic<br />Week<br />Latest Week First<br />Current Topic First<br />Day";

$string['defaultlayoutcolumns'] = 'Number of columns';
$string['defaultlayoutcolumns_desc'] = "Number of columns between one and four.";

$string['defaultlayoutcolumnorientation'] = 'Column orientation';
$string['defaultlayoutcolumnorientation_desc'] = "The default column orientation: Vertical or Horizontal.";

$string['defaulttgfgcolour'] = 'Toggle foreground colour';
$string['defaulttgfgcolour_desc'] = "Toggle foreground colour in hexidecimal RGB.";

$string['defaulttgfghvrcolour'] = 'Toggle foreground hover colour';
$string['defaulttgfghvrcolour_desc'] = "Toggle foreground hover colour in hexidecimal RGB.";

$string['defaulttgbgcolour'] = 'Toggle background colour';
$string['defaulttgbgcolour_desc'] = "Toggle background colour in hexidecimal RGB.";

$string['defaulttgbghvrcolour'] = 'Toggle background hover colour';
$string['defaulttgbghvrcolour_desc'] = "Toggle background hover colour in hexidecimal RGB.";

$string['defaulttogglealignment'] = 'Toggle text alignment';
$string['defaulttogglealignment_desc'] = "'Left', 'Centre' or 'Right'.";

$string['defaulttoggleiconset'] = 'Toggle icon set';
$string['defaulttoggleiconset_desc'] = "'Arrow'                => Arrow icon set.<br />'Bulb'                 => Bulb icon set.<br />'Cloud'                => Cloud icon set.<br />'Eye'                  => Eye icon set.<br />'Light Emitting Diode' => LED icon set.<br />'Point'                => Point icon set.<br />'Power'                => Power icon set.<br />'Radio'                => Radio icon set.<br />'Smiley'               => Smiley icon set.<br />'Square'               => Square icon set.<br />'Sun / Moon'           => Sun / Moon icon set.<br />'Switch'               => Switch icon set.";

$string['defaulttoggleallhover'] = 'Toggle all icon hovers';
$string['defaulttoggleallhover_desc'] = "'No' or 'Yes'.";

$string['defaulttogglepersistence'] = 'Toggle persistence';
$string['defaulttogglepersistence_desc'] = "'On' or 'Off'.  Turn off for an AJAX performance increase but user toggle selections will not be remembered on page refresh or revisit.<br />Note: When turning persistence off, please remove any rows containing 'culcollapsed_toggle_x' in the 'name' field of the 'user_preferences' table in the database.  Where the 'x' in 'culcollapsed_toggle_x' will be a course id.  This is to save space if you do not intend to turn it back on.";

$string['defaultuserpreference'] = 'Initial toggle state';
$string['defaultuserpreference_desc'] = 'States what to do with the toggles when the user first accesses the course, the state of additional sections when they are added or toggle persistence is off.';

// Toggle opacities.
$string['settoggleforegroundopacity'] = 'Toggle foreground opacity';
$string['settoggleforegroundopacity_help'] = 'Sets the opacity of the text on the toggle between 0 and 1 in 0.1 increments.';
$string['defaulttgfgopacity'] = 'Toggle foreground opacity';
$string['defaulttgfgopacity_desc'] = "Toggle foreground text opacity between 0 and 1 in 0.1 increments.";

$string['settoggleforegroundhoveropacity'] = 'Toggle foreground hover opacity';
$string['settoggleforegroundhoveropacity_help'] = 'Sets the opacity of the text on hover on the toggle between 0 and 1 in 0.1 increments.';
$string['defaulttgfghvropacity'] = 'Toggle foreground hover opacity';
$string['defaulttgfghvropacity_desc'] = "Toggle foreground text on hover opacity between 0 and 1 in 0.1 increments.";

$string['settogglebackgroundopacity'] = 'Toggle background opacity';
$string['settogglebackgroundopacity_help'] = 'Sets the opacity of the background on the toggle between 0 and 1 in 0.1 increments.';
$string['defaulttgbgopacity'] = 'Toggle background opacity';
$string['defaulttgbgopacity_desc'] = "Toggle background opacity between 0 and 1 in 0.1 increments.";

$string['settogglebackgroundhoveropacity'] = 'Toggle background hover opacity';
$string['settogglebackgroundhoveropacity_help'] = 'Sets the opacity of the background on hover on the toggle between 0 and 1 in 0.1 increments.';
$string['defaulttgbghvropacity'] = 'Toggle background hover opacity';
$string['defaulttgbghvropacity_desc'] = "Toggle background on hover opacity between 0 and 1 in 0.1 increments.";
// Toggle icon size.
$string['defaulttoggleiconsize'] = 'Toggle icon size';
$string['defaulttoggleiconsize_desc'] = "Icon size: Small = 16px, Medium = 24px and Large = 32px.";
$string['small'] = 'Small';
$string['medium'] = 'Medium';
$string['large'] = 'Large';

// Toggle border radius.
$string['defaulttoggleborderradiustl'] = 'Toggle top left border radius';
$string['defaulttoggleborderradiustl_desc'] = 'Border top left radius of the toggle.';
$string['defaulttoggleborderradiustr'] = 'Toggle top right border radius';
$string['defaulttoggleborderradiustr_desc'] = 'Border top right radius of the toggle.';
$string['defaulttoggleborderradiusbr'] = 'Toggle bottom right border radius';
$string['defaulttoggleborderradiusbr_desc'] = 'Border bottom right radius of the toggle.';
$string['defaulttoggleborderradiusbl'] = 'Toggle bottom left border radius';
$string['defaulttoggleborderradiusbl_desc'] = 'Border bottom left radius of the toggle.';
$string['em0_0'] = '0.0em';
$string['em0_1'] = '0.1em';
$string['em0_2'] = '0.2em';
$string['em0_3'] = '0.3em';
$string['em0_4'] = '0.4em';
$string['em0_5'] = '0.5em';
$string['em0_6'] = '0.6em';
$string['em0_7'] = '0.7em';
$string['em0_8'] = '0.8em';
$string['em0_9'] = '0.9em';
$string['em1_0'] = '1.0em';
$string['em1_1'] = '1.1em';
$string['em1_2'] = '1.2em';
$string['em1_3'] = '1.3em';
$string['em1_4'] = '1.4em';
$string['em1_5'] = '1.5em';
$string['em1_6'] = '1.6em';
$string['em1_7'] = '1.7em';
$string['em1_8'] = '1.8em';
$string['em1_9'] = '1.9em';
$string['em2_0'] = '2.0em';
$string['em2_1'] = '2.1em';
$string['em2_2'] = '2.2em';
$string['em2_3'] = '2.3em';
$string['em2_4'] = '2.4em';
$string['em2_5'] = '2.5em';
$string['em2_6'] = '2.6em';
$string['em2_7'] = '2.7em';
$string['em2_8'] = '2.8em';
$string['em2_9'] = '2.9em';
$string['em3_0'] = '3.0em';
$string['em3_1'] = '3.1em';
$string['em3_2'] = '3.2em';
$string['em3_3'] = '3.3em';
$string['em3_4'] = '3.4em';
$string['em3_5'] = '3.5em';
$string['em3_6'] = '3.6em';
$string['em3_7'] = '3.7em';
$string['em3_8'] = '3.8em';
$string['em3_9'] = '3.9em';
$string['em4_0'] = '4.0em';

$string['formatresponsive'] = 'Format responsive';
$string['formatresponsive_desc'] = "Turn on if you are using a non-responsive theme and the format will adjust to the screen size / device.  Turn off if you are using a responsive theme.  Bootstrap 2.3.2 support is built in, for other frameworks and versions, override the methods 'get_row_class()' and 'get_column_class()' in renderer.php.";

// Show section summary when collapsed.
$string['setshowsectionsummary'] = 'Show the section summary when collapsed';
$string['setshowsectionsummary_help'] = 'States if the section summary will always be shown regardless of toggle state.';
$string['defaultshowsectionsummary'] = 'Show the section summary when collapsed';
$string['defaultshowsectionsummary_desc'] = 'States if the section summary will always be shown regardless of toggle state.';

// Do not show date.
$string['donotshowdate'] = 'Do not show the date';
$string['donotshowdate_help'] = 'Do not show the date when using a weekly based structure and \'Use default section name\' has been un-ticked.';

// Capabilities.
$string['culcollapsed:changelayout'] = 'Change or reset the layout';
$string['culcollapsed:changecolour'] = 'Change or reset the colour';
$string['culcollapsed:changetogglealignment'] = 'Change or reset the toggle alignment';
$string['culcollapsed:changetoggleiconset'] = 'Change or reset the toggle icon set';

// Instructions.
$string['instructions'] = 'Instructions: Clicking on the section name will show / hide the section.';
$string['displayinstructions'] = 'Display instructions';
$string['displayinstructions_help'] = 'States that the instructions should be displayed to the user or not.';
$string['defaultdisplayinstructions'] = 'Display instructions to users';
$string['defaultdisplayinstructions_desc'] = "Display instructions to users informing them how to use the toggles.  Can be yes or no.";
$string['resetdisplayinstructions'] = 'Display instructions';
$string['resetalldisplayinstructions'] = 'Display instructions';
$string['resetdisplayinstructions_help'] = 'Resets the display instructions to the default value so it will be the same as a course the first time it is in the Collapsed Topics format.';
$string['resetalldisplayinstructions_help'] = 'Resets the display instructions to the default value for all courses so it will be the same as a course the first time it is in the Collapsed Topics format.';

// Readme.
$string['readme_title'] = 'Collapsed Topics read-me';
$string['readme_desc'] = 'Please click on \'{$a->url}\' for lots more information about Collapsed Topics.';

// Depreciated.  Remove in M3.4 version.
$string['defaultcoursedisplay'] = 'Course display';
$string['defaultcoursedisplay_desc'] = "Either show all the sections on a single page or section zero and the chosen section on page.";

// Show course summary.
$string['setshowcoursesummary'] = 'Show the course summary';
$string['setshowcoursesummary_help'] = 'States if the course summary will be shown.';
$string['defaultshowcoursesummary'] = 'Show the course summary';
$string['defaultshowcoursesummary_desc'] = 'States if the course summary will be shown.';

/*** Module Leaders ***/
// Select module leaders header.
$string['setselectmoduleleadershdr'] = 'Select the Module Leader(s)';
$string['setselectmoduleleadershdr_help'] = '
Identifies the Lecturers on the module who are Module Leaders. 
To select more than one Lecturer as the Module Leader click on one Lecturer, 
and then press and hold the Ctrl key. While holding down the Ctrl key, 
click each of the other lecturers you want to select as Module Leaders.
';
$string['setselectmoduleleaders'] = 'Select the Module Leader(s)';
$string['setselectmoduleleaders_help'] = '
Identifies the Lecturers on the module who are Module Leaders. 
To select more than one Lecturer as the Module Leader click on one Lecturer, 
and then press and hold the Ctrl key. While holding down the Ctrl key, 
click each of the other lecturers you want to select as Module Leaders.
';
$string['defaultselectmoduleleaders'] = 'Select the Module Leader(s)';
$string['defaultselectmoduleleaders_desc'] = '
Identifies the Lecturers on the module who are Module Leaders. 
To select more than one Lecturer as the Module Leader click on one Lecturer, 
and then press and hold the Ctrl key. While holding down the Ctrl key, 
click each of the other lecturers you want to select as Module Leaders.
';
$string['nolecturers'] = 'No Lecturers';

// Advanced heading
$string['setadvancedhdr'] = 'Advanced course format settings for admins';

// Dashboard heading
$string['setdashboardhdr'] = 'Dashboard settings';

// Show activities links.
$string['setshowactivitieslinks'] = 'Show the activities links';
$string['setshowactivitieslinks_help'] = 'States if the activities links will be shown.';
$string['defaultshowactivitieslinks'] = 'Show the activities links';
$string['defaultshowactivitieslinks_desc'] = 'States if the activities links will be shown.';

$string['setshowmodname'] = 'Show {$a} link';
$string['setshowmod'] = 'Show this activity link';
$string['setshowmod_help'] = 'States if this activity link will be shown.';


/*** Quick links ***/
// Show participants.
$string['setshowparticipants'] = 'Show the participants';
$string['setshowparticipants_help'] = 'States if the participants will be shown.';
$string['defaultshowparticipants'] = 'Show the participants';
$string['defaultshowparticipants_desc'] = 'States if the participants will be shown.';

// Show reading lists.
$string['setshowreadinglists'] = 'Show the reading lists';
$string['setshowreadinglists_help'] = 'States if the reading lists will be shown.';
$string['defaultshowreadinglists'] = 'Show the reading lists';
$string['defaultshowreadinglists_desc'] = 'States if the reading lists will be shown.';
$string['quickshowreadinglists'] = 'Show the reading lists';
$string['quickhidereadinglists'] = 'Hide the reading lists';

// Show timetable.
$string['setshowtimetable'] = 'Show the timetable';
$string['setshowtimetable_help'] = 'States if the timetable will be shown.';
$string['defaultshowtimetable'] = 'Show the timetable';
$string['defaultshowtimetable_desc'] = 'States if the timetable will be shown.';
$string['quickshowtimetable'] = 'Show the timetable';
$string['quickhidetimetable'] = 'Hide the timetable';

// Show grader report.
$string['setshowgraderreport'] = 'Show the grader report';
$string['setshowgraderreport_help'] = 'States if the grader report will be shown.';
$string['defaultshowgraderreport'] = 'Show the grader report';
$string['defaultshowgraderreport_desc'] = 'States if the grader report will be shown.';
$string['quickshowgraderreport'] = 'Show the grader report';
$string['quickhidegraderreport'] = 'Hide the grader report';

// Show calendar.
$string['setshowcalendar'] = 'Show the calendar';
$string['setshowcalendar_help'] = 'States if the calendar will be shown.';
$string['defaultshowcalendar'] = 'Show the calendar';
$string['defaultshowcalendar_desc'] = 'States if the calendar will be shown.';
$string['quickshowcalendar'] = 'Show the calendar';
$string['quickhidecalendar'] = 'Hide the calendar';

// Show students.
$string['setshowstudents'] = 'Show the students';
$string['setshowstudents_help'] = 'States if the students will be shown.';
$string['defaultshowstudents'] = 'Show the students';
$string['defaultshowstudents_desc'] = 'States if the students will be shown.';
$string['quickshowstudents'] = 'Show the students';
$string['quickhidestudents'] = 'Hide the students';

// Show lecturers.
$string['setshowlecturers'] = 'Show the lecturers';
$string['setshowlecturers_help'] = 'States if the lecturers will be shown.';
$string['defaultshowlecturers'] = 'Show the lecturers';
$string['defaultshowlecturers_desc'] = 'States if the lecturers will be shown.';
$string['quickshowlecturers'] = 'Show the lecturers';
$string['quickhidelecturers'] = 'Hide the lecturers';

// Show courseofficers.
$string['setshowcourseofficers'] = 'Show the courseofficers';
$string['setshowcourseofficers_help'] = 'States if the courseofficers will be shown.';
$string['defaultshowcourseofficers'] = 'Show the courseofficers';
$string['defaultshowcourseofficers_desc'] = 'States if the courseofficers will be shown.';
$string['quickshowcourseofficers'] = 'Show the courseofficers';
$string['quickhidecourseofficers'] = 'Hide the courseofficers';

// Show media.
$string['setshowmedia'] = 'Show the media gallery';
$string['setshowmedia_help'] = 'States if the media gallery will be shown.';
$string['defaultshowmedia'] = 'Show the media gallery';
$string['defaultshowmedia_desc'] = 'States if the media gallery will be shown.';
$string['quickshowmedia'] = 'Show the media gallery';
$string['quickhidemedia'] = 'Hide the media gallery';
$string['quickshowlink'] = 'Show this link';
$string['quickhidelink'] = 'Hide this link';
$string['noeditcoursesettings'] = 'You are not permitted to edit course settings.';

// NEEDS TIDYING
$string['aspirelists'] = 'Reading Lists';
$string['activities'] = 'Activities';
$string['config_connection_timeout'] = 'Connection timeout';
$string['config_connection_timeout_desc'] = 'Enter the maximum time in seconds to wait for a connection to the Reading Lists server (upper limit: 6).';
$string['config_connection_timeout_ex'] = '4';
$string['config_transfer_timeout'] = 'Transfer timeout';
$string['config_transfer_timeout_desc'] = 'Enter the maximum time in seconds to wait for data transfer from the Reading Lists server to complete, before displaying an error message (upper limit 16).';
$string['config_transfer_timeout_ex'] = '8';
$string['culcollapsed:viewphotoboard'] = 'View Photoboard';
$string['culcollapsed:viewallphotoboard'] = 'View all Photoboards';
$string['forum-posts'] = 'Forum posts';
$string['headerconfig'] = 'Config section header';
$string['hide_activitieslinks'] = "Hide 'Activities' links";
$string['hide_activitieslinks_help'] = 'Hide the panel containing links to activities and resources used within the module.';
$string['hide_calendar'] = "Hide 'Calendar' link";
$string['hide_media'] = "Hide 'Media Gallery' link";
$string['hide_coursesummary'] = 'Hide Module Summary';
$string['hide_coursesummary_help'] = 'Always hide the Module Summary. Note that the summary panel will not display if no summary has been entered, even if this is unchecked.';
$string['hide_graderreport'] = "Hide 'Grader report' link";
$string['hide_participants'] = "Hide 'Participants' link";
$string['hide_photoboard'] = "Hide 'Photoboard' link";
$string['hide_readinglists'] = "Hide 'Reading Lists' link";
$string['hide_timetable'] = "Hide 'Timetable' link";
$string['media'] = 'Media Gallery';
$string['moduleleader'] = ' (Module Leader)';
$string['modulesummary'] = 'Module Summary';
$string['no-readinglist'] = 'There are no reading lists matching this module';
$string['error-readinglist'] = 'Sorry - an error occurred while fetching the reading lists data';
$string['no-view-grades'] = 'Sorry, you are unable to view grades';
$string['no-view-photoboard'] = 'Sorry, you are unable to view module photoboards';
$string['no-view-student-photoboard'] = 'There are no {$a} enrolled on this module';
$string['no-view-lecturer-photoboard'] = 'There are no {$a} enrolled on this module';
$string['no-view-courseofficer-photoboard'] = 'There are no {$a} enrolled on this module';
$string['no-timetable'] = 'There is no timetable link matching this module';
$string['error-timetable'] = 'Sorry - an error occurred while fetching the timetable data';
$string['not-installed-readinglist'] = 'Reading List (not installed)';
$string['not-installed-timetable'] = 'Timetable link (not installed)';
$string['photoboard'] = 'Photoboard';
$string['photoboard_info'] = '';
$string['breadcrumb_photo'] = 'Photoboard';
$string['allparticipants'] = 'Number of Participants';
$string['quicklinks'] = 'Quick Links';
$string['seeall'] = 'See all';
$string['sendmessage'] = 'Send a message';
$string['timetable'] = 'Timetable';
$string['title'] = 'Module Dashboard';
$string['view-calendar'] = 'View calendar';
$string['view-media'] = 'View Media Gallery';
$string['view-graderreport'] = 'View grader report';
$string['view-participantlist'] = 'View module participant list';
$string['view-photoboard'] = 'View photoboard';
$string['view-student-photoboard'] = 'View {$a} photoboard';
$string['view-lecturer-photoboard'] = 'View {$a} photoboard';
$string['view-courseofficer-photoboard'] = 'View {$a} photoboard';
$string['view-readinglist-module'] = 'View reading list(s) for this module';
$string['view-readinglist-module-year'] = 'View reading list for this module and year';
$string['view-timetable'] = 'View timetable';
$string['view-mod'] = 'View list of {$a}';
$string['linktowebapps'] = 'Student info (intranet)';

/* Photoboard fields */
$string['buildinglocation'] = 'Building/location';
$string['mobile'] = 'Mobile phone';
$string['officehours'] = 'Office hours';
$string['telephone'] = 'Telephone';

/* Section editing */
$string['addsection'] = 'Add section';
$string['removesection'] = 'Remove section';
