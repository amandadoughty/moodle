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
 * Lang strings for culcourse_listing block
 *
 * @package    block_culcourse_listing
 * @copyright  2014 onwards Amanda Doughty (amanda.doughty.1@city.ac.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'CUL Course listing';
$string['admindescdate'] = 'Filtering by course start date';
$string['admindescfilters'] = 'Filtering';
$string['admindescgeneral'] = 'General';
$string['admindescregex'] = 'Filtering by regex';
$string['admintitlefilters'] = 'Filter dates';
$string['adminheaderdate'] = 'Filtering by course start date';
$string['adminheaderfilters'] = 'Filtering';
$string['adminheadergeneral'] = 'General';
$string['adminheaderregex'] = 'Filtering by regex';
$string['addmore'] = 'Add another';
$string['all'] = 'ALL';
$string['allcourses'] = 'Modules';
$string['and'] = ' and ';
$string['clearfavourites'] = 'Clear favourites';
$string['clearfavouritescheck'] = 'Are you sure you want to clear your favourites?';
$string['collapseall'] = 'Collapse All Course Lists';
$string['courseoverview'] = 'CUL Course Listing';
$string['culcourse_listing:addinstance'] = 'Add a CUL Course Listing block';
$string['culcourse_listing:myaddinstance'] = 'Add a CUL Course listing block to my moodle';
$string['date'] = 'date';
$string['daterangename'] = 'Name of term';
$string['daterangetype'] = 'Type of term';
$string['daterangestart'] = 'Start date';
$string['daterangeend'] = 'End date';
$string['displayname'] = 'Course display name';
$string['displayname_help'] = 'the course field to use as the course display name';
$string['editcategory'] = 'Edit this category';
$string['editperiod'] = 'Add/Edit Terms';
$string['editperiods'] = 'Manage terms';
$string['expandall'] = 'Expand All Course Lists';
$string['divalert'] = 'Selected Modules: {$a} only';
$string['favouriteaction'] = 'favourite{$a}';
$string['favouriteadd'] = 'Add to favourites';
$string['favouriteremove'] = 'Remove from favourites';
$string['favourites'] = 'Favourites';
$string['filterbyyear'] = 'Filter by Year';
$string['filterbyyear_help'] = 'Include option to filter by year';
$string['filterbyperiod'] = 'Filter by Term';
$string['filterbyperiod_help'] = 'Include option to filter by term';
$string['filterfield'] = 'Filter field';
$string['filterfield_help'] = 'The course field to search when the regex filter method is set.';
$string['filterglue'] = 'Seperator';
$string['filterglue_help'] = 'The seperator (if any) to split the chosen regex course field into parts';
$string['filtertype'] = 'Filter method';
$string['filtertype_help'] = 'Filter years and terms can be set by regex which is used to search a course field, or by comparing the course start date to the years/periods set in: <link>';
$string['filterperiodregex'] = 'Term regex';
$string['filterperiodregex_help'] = 'The regex to use to match the term eg PRD1';
$string['filteryearregex'] = 'Year regex';
$string['filteryearregex_help'] = 'The regex to use to match the year eg 2014-15';
$string['modules'] = 'Modules';
$string['move'] = 'Move';
$string['movecoursehere'] = 'Move course here';
$string['navculcourselisting'] = 'CUL Course Listing';
$string['navfilters'] = 'Filter dates';
$string['nofavourites'] = 'Add favourites to a menu by clicking the grey star <i class="fa fa-star-o"></i> next to the module in your list of Modules below';
$string['period'] = 'Select by Term';
$string['periodtype'] = 'Term';
$string['PRD1'] = 'Term 1';
$string['PRD2'] = 'Term 2';
$string['PRD3'] = 'Term 3';
$string['regex'] = 'regex';
$string['reorderfavourites'] = 'Reset order';
$string['reorderfavouritescheck'] = 'Are you sure you want to reorder your favourites?';
$string['save'] = 'Save';
$string['setdateranges_header'] = 'Set Filter Dates';
$string['year'] = 'Select by Year';
$string['yeartype'] = 'Year';

/***** PRIVACY API *****/
$string['privacy:metadata:preference:culcourse_listing_course_favourites'] = 'The list of courses you have marked as favourites.';
$string['privacy:request:preference:culcourse_listing_course_favourites'] = 'Your favourite courses are "{$a->favourites}"';
$string['privacy:metadata:preference:culcourse_listing_filter_year'] = 'The year you have filtered your courses by.';
$string['privacy:request:preference:culcourse_listing_filter_year'] = 'You have filtered your courses by the year "{$a->filteryear}".';
$string['privacy:metadata:preference:culcourse_listing_filter_period'] = 'The period you have filtered your courses by.';
$string['privacy:request:preference:culcourse_listing_filter_period'] = 'You have filtered your courses by the period "{$a->filterperiod}"';
