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
 * Language strings
 *
 *
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'CUL Rollover';
$string['defaultoptions_header'] = '1. Default Options';
$string['nextdate'] = "Next available date";
$string['specificdate'] = "Specify a date";
$string['copy_everything'] = "Everything";
$string['copy_content'] = "Content only ";
$string['copy_unknown'] = "unknown ";
$string['merge'] = "Merge ";
$string['delete'] = "Delete first";
$string['groups'] = "Yes";
$string['nogroups'] = "No";
$string['rolestocopy'] = "Copy role assignments";
$string['norolestocopy'] = "No selectable roles! Has the admin allowed roles to be copied across?";
$string['visible'] = " Make Visible";
$string['notvisible'] = "Hide";
$string['visiblefrom'] = "Make visible on a given date";
$string['visibledate'] = "Set start date (module visibility) Day";
$string['migratedate'] = "Rollover date";
$string['selectcourses_header'] = "2. Select Modules";
$string['reviewoptions_header'] = "3. Review and Amend Settings";
$string['rolestoinform'] = "Which users group should be emailed with the rollover status";
$string['addmore'] = "Add another";
$string['automatedbackupschedule'] = "Automated Backup Schedule";
$string['successemail'] = 'Your course {$a->coursename} was successfully rolled over. <br> <a href="' .
$CFG->wwwroot . 
'/course/view.php?id={$a->courseid}">Click here to access the course</a>
<br/>
<br/>
* You will need to update Moodle assignment descriptions and due dates prior to the start of the module.
<br/>
* Turnitin and Moodle assignments with the Turnitin plugin enabled will not be rolled over and will have to be added to Moodle prior to the start of the module.
<br/>
* If you have selected to rollover groups you will need to add your students into groups before your activities go live.
<br/>
<br/>
To help you prepare your modules for the start of year/term refer to the Module Page Checklist: 
<a href="https://sleguidance.atlassian.net/wiki/display/Moodle/Module+Page+Checklist">
https://sleguidance.atlassian.net/wiki/display/Moodle/Module+Page+Checklist
</a>
';
$string['successemail_subject'] = 'Moodle Rollover complete for {$a->coursename}';
$string['successemail_subject_requestor'] = 'Moodle Rollover complete for {$a->coursename}';
$string['successemail_requestor'] = 'Your course {$a->coursename} was successfully rolled over. <br> <a href="' .
$CFG->wwwroot . 
'/course/view.php?id={$a->courseid}">Click here to access the course</a>
<br/>
<br/>
* You will need to update Moodle assignment descriptions and due dates prior to the start of the module.
<br/>
* Turnitin and Moodle assignments with the Turnitin plugin enabled will not be rolled over and will have to be added to Moodle prior to the start of the module.
<br/>
* If you have selected to rollover groups you will need to add your students into groups before your activities go live.
<br/>
<br/>
To help you prepare your modules for the start of year/term refer to the Module Page Checklist: 
<a href="https://sleguidance.atlassian.net/wiki/display/Moodle/Module+Page+Checklist">
https://sleguidance.atlassian.net/wiki/display/Moodle/Module+Page+Checklist
</a>
';
$string['visiblity'] = 'Course Visibility';
$string['whentorollover'] = 'When should rollover be carried out?';
$string['howtorollover'] = 'Merge or delete first existing content in destination module';
$string['whattoroolover'] = 'Rollover type';
$string['shouldgroupsbecopied'] = 'Copy groups and groupings';
$string['shouldcoursebevisible'] = 'Module visibility';
$string['source_courses'] = "Select Source Courses";
$string['destination_courses'] = "Select Destination Courses";
$string['reviewmessage'] = 'Destination modules that already have content (and therefore may have been rolled over already) are highlighted. Click the link to view a module in a new window. You can remove the rollover from the list using the Remove from queue link.';
$string['selectcoursesmessage'] = 'Modules are filtered according to current year and rollover period. You can only see modules that you are enroled on. Destination modules that already have a rollover pending will be shown in the destination list but you can not select them until the rollover has processed.';

// Course visibility Strings.
$string['hidecourse'] = 'Keep New Course Hidden';
$string['visiblecourse'] = 'Make New Course Visible Immediately';
$string['coursevisibleon'] ='Make Course Visible on:  ';

// Table Headers.
$string['source_header'] = "Source";
$string['dest_header'] = "Destination";
$string['type_header'] = "Type";
$string['migration_date'] = "Date";
$string['type'] = "Type";
$string['method'] = "Merge or Delete";
$string['group'] = "Groups";
$string['roles'] = "Enrolments";
$string['visibility'] = "Course Visibility";
$string['visiblefromdate'] = "Visibility Date";
$string['deleterow'] = "Remove from queue?";
$string['selectsourcecourses'] = "Source Modules";
$string['selectdestinationcourses'] = "Destination Modules";

// Action Buttons.
$string['choosecourses'] = 'Next -> Select Modules';
$string['review'] = 'Next -> Review and Amend';
$string['finish'] = 'Last  -> Submit to Queue';

// Index Page.
$string['addformigration'] = "Add to Rollover Queue";
$string['breadcrumb'] = 'Rollover Tool';
$string['date'] = "Date";
$string['existingrecords'] = 'Module Rollover Tool';
$string['norequests'] = "No Current Rollover Requests";
$string['status'] = "Status";
$string['title'] = 'Moodle: CUL Rollover Tool';
$string['user'] = "User";

// Tooltip Strings.
$string['tooltipwarning'] = 'There is existing content in this course! It might get overwritten!!';

// Settings.
$string['src_regex_identifier'] = "Specify Source Course RegEx Filter";
$string['dest_regex_identifier'] = "Specify Destination Course RegEx Filter";
$string['allowedroles'] = 'Select Roles';
$string['rollover_debugging'] = "Enable Rollover Debugging";
$string['rollover_debugging_desc'] = "Enable extra messages to be displayed in the cron log for the rollover";
$string['rollover_delay'] = "Delay.";
$string['rollover_delay_desc'] = "Set select box typing delay.";
$string['allowedroles_desc'] = 'Select roles which can be rolled forward';
$string['startat_helper'] ='What time should the rollover cron be allowed to run';
$string['stopat_helper'] = 'What time should the rollover cron be stopped?';
$string['src_regex_identifier_helper'] = 'Specify the Regular Expression to filter the course selection for Source Courses. No delimiters are required. For syntax see:
<a href ="http://dev.mysql.com/doc/refman/5.7/en/regexp.html" target="_blank">SQL Regex</a>';
$string['dest_regex_identifier_helper'] = 'Specify the Regular Expression to filter the course selection for Destination Courses. No delimiters are required. For syntax see:
<a href ="http://dev.mysql.com/doc/refman/5.7/en/regexp.html" target="_blank">SQL Regex</a>';
$string['allowedroles_helper'] = 'Select roles which can be rolled forward';
$string['delete_turnitin_v2'] = "Delete Turnitinv2 Assignments";
$string['delete_turnitin_v2_desc'] = "Delete Turnitin Version 2 Assignments from the Destination Course? - Please note that this cannot be undone!";
$string['alwaysbackupdestination'] = "Backup Destination Course?";
$string['backupdestination_desc'] = "Always backup destination course before a rollover takes place. this might increase processing times and significantly reduce performance!";

// Error Strings.
$string['nocoursestomigrate'] = "There are no selectable courses to rollover. Please check the admin settings and ensure that the course filters are correct";
$string['invalidcourseshortname'] = "The course shortname supplied is invalid! Please review the CSV upload file and retry";
$string['invalidcourseformigration'] = "The specified course is not a valid source course or a valid destination course. Please contact your administrator for further details";
$string['invalidextparam'] = 'The backup option {$a} is invalid';
$string['invaliddstcourseid'] = 'The source course id {$a} is invalid';
$string['invalidsrccourseid'] = 'The destination course id {$a} is invalid';
$string['emptycourseselection'] = "You have not selected any courses for rollover. Please select a course from the previous page";
$string['inserterror'] = "There was a problem adding one or more of your rollovers";
$string['backupprecheckerrors'] = 'There were errors in the backup precheck: {$a}';


// Help Icons Text.
$string['defaultoptions_helptext_help'] = "This sections allows you to set the default / bulk options for the courses you wish to rollover. These options can be modified later on";
$string['defaultoptions_helptext'] = "Default Options";
$string['whengroup_helptext_help'] = "You can choose to set the rollover to happen on a particular date, or on the next available date. Depending on the queue this might be the tomorrow.";
$string['whengroup_helptext'] = "Rollover Date";
$string['whatgroup_helptext_help'] = "Rollover has changed. There is no need to select a type. 
Rollover will copy all content (activities and resources, except Turnitin and Moodle assignments with the Turnitin Plugin enabled) 
from the Source to the Destination module. Blocks cannot be copied. 
Blocks are setup using the new CUL Collapsed Topics format which will be applied automatically when the module is created.";
$string['whatgroup_helptext'] = "Content Options";
$string['mergegroup_helptext_help'] = "Would you like to merge the content in the destination course or replace it with the course to be rolled over?";
$string['mergegroup_helptext'] = "Merge Options";
$string['groupsgroup_helptext_help'] = "Selecting Yes for this option will copy any groups or groupings from the source module 
to the destination. Note this does not include group members, just group names and groupings. 
You will need to add your students into groups before your activities go live.";
$string['groupsgroup_helptext'] = "Group Copy Options";
$string['roles_helptext_help'] = "Choose the roles that you would like copied across from the course being rolled over. You can select multiple courses at the same time.";
$string['roles_helptext'] = "Roles Copy Options";
$string['noroles_helptext_help'] = "Roles currently cannot be copied across. Please contact the admin team for further details.";
$string['noroles_helptext'] = "No roles!";
$string['visiblegroup_helptext_help'] = "You can select to make the course visible to students now, or hide it, or make it available on a certain date";
$string['visiblegroup_helptext'] = "Course Availability Options";
$string['rolestoinform_help'] = 'Select roles to inform when rollover complete';
$string['rolestoinform_help_help'] = 'Select roles to inform when rollover complete';

// Capability Strings.
$string['culrollover:delete'] = "Delete rollovers";
$string['culrollover:view'] = "View rollovers";

// Event strings.
$string['eventrollover_deleted'] = "Rollover delete";
$string['eventrollover_completed'] = "Rollover complete";
$string['eventrollover_failed'] = "Rollover failed";

// Task strings.
$string['rollovertask'] = "Rollover processing";






