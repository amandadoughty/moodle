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
 * @subpackage culcourse
 * @version    See the value of '$plugin->version' in below.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 */

$string['openinnewwindow'] = 'Open in new window';

// Used by the Moodle Core for identifing the format and displaying in the list of formats for a course in its settings.
// Possibly legacy to be removed after Moodle 2.0 is stable.
$string['nameculcourse'] = 'CUL Collapsed Topics';
$string['formatculcourse'] = 'CUL Collapsed Topics';

// Used in format.php.
$string['culcoursetoggle'] = 'Toggle';
$string['culcoursesidewidth'] = '28px';

// Toggle all - Moodle Tracker CONTRIB-3190.
$string['culcourseall'] = 'sections.';  // Leave as AMOS maintains only the latest translation - so previous versions are still supported.
$string['culcourseopened'] = 'Open all';
$string['culcourseclosed'] = 'Close all';

// Moodle 2.0 Enhancement - Moodle Tracker MDL-15252, MDL-21693 & MDL-22056 - http://docs.moodle.org/en/Development:Languages.
$string['sectionname'] = 'Section';
$string['pluginname'] = 'CUL Collapsed Topics';
$string['section0name'] = 'General';

// MDL-26105.
$string['page-course-view-culcourse'] = 'Any course main page in the collapsed topics format';
$string['page-course-view-culcourse-x'] = 'Any course page in the collapsed topics format';

// Moodle 2.3 Enhancement.
$string['hidefromothers'] = 'Hide section';
$string['showfromothers'] = 'Show section';
$string['currentsection'] = 'This section';
// These are 'topic' as they are only shown in 'topic' based structures.
$string['markedthissection'] = 'This section is highlighted as the current section';
$string['markthissection'] = 'Highlight this section as the current section';

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

$string['setlayoutelements'] = 'Set elements';
$string['setlayoutstructure'] = 'Set structure';
$string['setlayoutstructuretopic'] = 'Topic';
$string['setlayoutstructureweek'] = 'Week';
$string['setlayoutstructurelatweekfirst'] = 'Current Week First';
$string['setlayoutstructurecurrenttopicfirst'] = 'Current Topic First';
$string['setlayoutstructureday'] = 'Day';
$string['resetlayout'] = 'Layout'; // CONTRIB-3529.
$string['resetalllayout'] = 'Layouts';





// MDL-34917 - implemented in M2.5 but needs to be here to support M2.4- versions.
$string['maincoursepage'] = 'Main course page';

// Help.
$string['setlayoutelements_help'] = 'How much information about the toggles / sections you wish to be displayed.';
$string['setlayoutstructure_help'] = "The layout structure of the course.  You can choose between:

'Topics' - where each section is presented as a topic in section number order.

'Weeks' - where each section is presented as a week in ascending week order from the start date of the course.

'Current Week First' - which is the same as weeks but the current week is shown at the top and preceding weeks in descending order are displayed below except in editing mode where the structure is the same as 'Weeks'.

'Current Topic First' - which is the same as 'Topics' except that the current topic is shown at the top if it has been set.

'Day' - where each section is presented as a day in ascending day order from the start date of the course.";
$string['setlayout_help'] = 'Contains the settings to do with the layout of the format within the course.';
$string['resetlayout_help'] = 'Resets the layout element, structure, coloumns, icon position and shown section summary to the default values so it will be the same as a course the first time it is in the \'Collapsed Topics\' format.';
$string['resetalllayout_help'] = 'Resets the layout to the default values for all courses so it will be the same as a course the first time it is in the \'Collapsed Topics \'format.';


// Moodle 2.4 Course format refactoring - MDL-35218.
$string['numbersections'] = 'Number of sections';
$string['editsummary'] = 'Edit section summary';
$string['summarycalltoaction'] = '
Topic 0 is used to provide an overview of the module (this can be presented as uploaded files, links or text).</br>
Topic 0 is also an appropriate place to locate administrative documentation for students.</br></br> 

Click on the cog icon to the right of this section to add/update a title and/or summary. 
';

// Site Administration -> Plugins -> Course formats -> Collapsed Topics.
$string['defaultheadingsub'] = 'Defaults';
$string['defaultheadingsubdesc'] = 'Default settings';
$string['configurationheadingsub'] = 'Toggles';
$string['configurationheadingsubdesc'] = 'Toggle settings';
$string['dashboardheadingsub'] = 'Dashboard';
$string['dashboardheadingsubdesc'] = 'Dashboard settings';

$string['off'] = 'Off';
$string['on'] = 'On';
$string['defaultcoursedisplay'] = 'Course display';
$string['defaultcoursedisplay_desc'] = "Either show all the sections on a single page or section zero and the chosen section on page.";
$string['defaultlayoutelement'] = 'Layout';
// Negative view of layout, kept for previous versions until such time as they are updated.
$string['defaultlayoutelement_desc'] = "The layout setting can be one of:

'Default' with everything displayed.

No 'Topic x' / 'Week x' / 'Day x'.

No section number.

No 'Topic x' / 'Week x' / 'Day x' and no section number.

No 'Toggle' word.

No 'Toggle' word and no 'Topic x' / 'Week x' / 'Day x'.

No 'Toggle' word, no 'Topic x' / 'Week x' / 'Day x' and no section number.";
// Positive view of layout.
$string['defaultlayoutelement_descpositive'] = "The layout setting can be one of:

Toggle word, 'Topic x' / 'Week x' / 'Day x' and section number.

Toggle word and 'Topic x' / 'Week x' / 'Day x'.

Toggle word and section number.

'Topic x' / 'Week x' / 'Day x' and section number.

Toggle word.

'Topic x' / 'Week x' / 'Day x'.

Section number.

No additions.";

$string['defaultlayoutstructure'] = 'Structure configuration';
$string['defaultlayoutstructure_desc'] = "The structure setting can be one of:

Topic

Week

Latest Week First

Current Topic First

Day";





$string['defaulttoggleallhover'] = 'Toggle all icon hovers';
$string['defaulttoggleallhover_desc'] = "'No' or 'Yes'.";

$string['defaulttogglepersistence'] = 'Toggle persistence';
$string['defaulttogglepersistence_desc'] = "'On' or 'Off'.  Turn off for an AJAX performance increase but user toggle selections will not be remembered on page refresh or revisit.

Note: When turning persistence off, please remove any rows containing 'culcourse_toggle_x' in the 'name' field
      of the 'user_preferences' table in the database.  Where the 'x' in 'culcourse_toggle_x' will be
      a course id.  This is to save space if you do not intend to turn it back on.";

$string['defaultuserpreference'] = 'What to do with the toggles when the user first accesses the course, adds more sections or toggle peristence is off';
$string['defaultuserpreference_desc'] = 'States what to do with the toggles when the user first accesses the course, the state of additional sections when they are added or toggle persistence id off.';

$string['defaultblocks'] = 'Default blocks';
$string['defaultblocks_desc'] = 'Default blocks to include in new course. String should be in the format \'block1,block2\'';

// Show section summary when collapsed.
$string['setshowsectionsummary'] = 'Show the section summary when collapsed';
$string['setshowsectionsummary_help'] = 'States if the section summary will always be shown regardless of toggle state.';
$string['defaultshowsectionsummary'] = 'Show the section summary when collapsed';
$string['defaultshowsectionsummary_desc'] = 'States if the section summary will always be shown regardless of toggle state.';

// Show course summary.
$string['setshowcoursesummary'] = 'Show the course summary';
$string['setshowcoursesummary_help'] = 'States if the course summary will be shown.';
$string['defaultshowcoursesummary'] = 'Show the course summary';
$string['defaultshowcoursesummary_desc'] = 'States if the course summary will be shown.';

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

/*** Module Leaders ***/
// Select module leaders.
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

// Do not show date.
$string['donotshowdate'] = 'Do not show the date';
$string['donotshowdate_help'] = 'Do not show the date when using a weekly based structure and \'Use default section name\' has been un-ticked.';

// Capabilities.
$string['culcourse:changelayout'] = 'Change or reset the layout';
$string['culcourse:changecolour'] = 'Change or reset the colour';
$string['culcourse:changetogglealignment'] = 'Change or reset the toggle alignment';
$string['culcourse:changetoggleiconset'] = 'Change or reset the toggle icon set';

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
$string['roles'] = 'Roles:';



$string['aspirelists'] = 'Reading Lists';
$string['activities'] = 'Activities';
$string['config_connection_timeout'] = 'Connection timeout';
$string['config_connection_timeout_desc'] = 'Enter the maximum time in seconds to wait for a connection to the Reading Lists server (upper limit: 6).';
$string['config_connection_timeout_ex'] = '4';
$string['config_transfer_timeout'] = 'Transfer timeout';
$string['config_transfer_timeout_desc'] = 'Enter the maximum time in seconds to wait for data transfer from the Reading Lists server to complete, before displaying an error message (upper limit 16).';
$string['config_transfer_timeout_ex'] = '8';
$string['culcourse:viewphotoboard'] = 'View Photoboard';
$string['culcourse:viewallphotoboard'] = 'View all Photoboards';
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

