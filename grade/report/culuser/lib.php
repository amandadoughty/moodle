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
 * Definition of the grade_user_report class is defined
 *
 * @package gradereport_culuser
 * @copyright 2007 Nicolas Connault
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/grade/report/user/lib.php');
require_once($CFG->libdir.'/tablelib.php');

/**
 * Class providing an API for the user report building and displaying.
 * @uses grade_report
 * @package gradereport_culuser
 */
class grade_report_culuser extends grade_report_user {

    public function fill_table() {
        $this->fill_table_recursive($this->gtree->top_element);
        return true;
    }

    /**
     * Fill the table with data.
     *
     * @param $element - An array containing the table data for the current row.
     */
    private function fill_table_recursive(&$element) {
        global $DB, $CFG;

        $type = $element['type'];
        $depth = $element['depth'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;
        $element['userid'] = $this->user->id;
        $fullname = $this->gtree->get_element_header($element, true, true, true, true, true);
        $data = array();
        $gradeitemdata = array();
        $hidden = '';
        $excluded = '';
        $itemlevel = ($type == 'categoryitem' || $type == 'category' || $type == 'courseitem') ? $depth : ($depth + 1);
        $class = 'level' . $itemlevel . ' level' . ($itemlevel % 2 ? 'odd' : 'even');
        $classfeedback = '';

        // If this is a hidden grade category, hide it completely from the user
        if ($type == 'category' && $grade_object->is_hidden() && !$this->canviewhidden && (
                $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
                ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$grade_object->is_hiddenuntil()))) {
            return false;
        }

        if ($type == 'category') {
            $this->evenodd[$depth] = (($this->evenodd[$depth] + 1) % 2);
        }
        $alter = ($this->evenodd[$depth] == 0) ? 'even' : 'odd';

        /// Process those items that have scores associated
        if ($type == 'item' or $type == 'categoryitem' or $type == 'courseitem') {
            $header_row = "row_{$eid}_{$this->user->id}";
            $header_cat = "cat_{$grade_object->categoryid}_{$this->user->id}";

            if (! $grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->user->id))) {
                $grade_grade = new grade_grade();
                $grade_grade->userid = $this->user->id;
                $grade_grade->itemid = $grade_object->id;
            }

            $grade_grade->load_grade_item();

            /// Hidden Items
            if ($grade_grade->grade_item->is_hidden()) {
                $hidden = ' dimmed_text';
            }

            $hide = false;
            // If this is a hidden grade item, hide it completely from the user.
            if ($grade_grade->is_hidden() && !$this->canviewhidden && (
                    $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$grade_grade->is_hiddenuntil()))) {
                $hide = true;
            } else if (!empty($grade_object->itemmodule) && !empty($grade_object->iteminstance)) {
                // The grade object can be marked visible but still be hidden if
                // the student cannot see the activity due to conditional access
                // and it's set to be hidden entirely.
                $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
                if (!empty($instances[$grade_object->iteminstance])) {
                    $cm = $instances[$grade_object->iteminstance];
                    $gradeitemdata['cmid'] = $cm->id;
                    if (!$cm->uservisible) {
                        // If there is 'availableinfo' text then it is only greyed
                        // out and not entirely hidden.
                        if (!$cm->availableinfo) {
                            $hide = true;
                        }
                    }
                }
            }

            // Actual Grade - We need to calculate this whether the row is hidden or not.
            $gradeval = $grade_grade->finalgrade;
            $hint = $grade_grade->get_aggregation_hint();
            if (!$this->canviewhidden) {
                /// Virtual Grade (may be calculated excluding hidden items etc).
                $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($this->courseid,
                                                                             $grade_grade->grade_item,
                                                                             $gradeval);

                $gradeval = $adjustedgrade['grade'];

                // We temporarily adjust the view of this grade item - because the min and
                // max are affected by the hidden values in the aggregation.
                $grade_grade->grade_item->grademax = $adjustedgrade['grademax'];
                $grade_grade->grade_item->grademin = $adjustedgrade['grademin'];
                $hint['status'] = $adjustedgrade['aggregationstatus'];
                $hint['weight'] = $adjustedgrade['aggregationweight'];
            } else {
                // The max and min for an aggregation may be different to the grade_item.
                if (!is_null($gradeval)) {
                    $grade_grade->grade_item->grademax = $grade_grade->get_grade_max();
                    $grade_grade->grade_item->grademin = $grade_grade->get_grade_min();
                }
            }


            if (!$hide) {
                /// Excluded Item
                /**
                if ($grade_grade->is_excluded()) {
                    $fullname .= ' ['.get_string('excluded', 'grades').']';
                    $excluded = ' excluded';
                }
                **/

                /// Other class information
                $class .= $hidden . $excluded;
                if ($this->switch) { // alter style based on whether aggregation is first or last
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggt b2b" : " item b1b";
                } else {
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggb" : " item b1b";
                }
                if ($type == 'categoryitem' or $type == 'courseitem') {
                    $header_cat = "cat_{$grade_object->iteminstance}_{$this->user->id}";
                }

                /// Name
                $data['itemname']['content'] = $fullname;
                $data['itemname']['class'] = $class;
                $data['itemname']['colspan'] = ($this->maxdepth - $depth);
                $data['itemname']['celltype'] = 'th';
                $data['itemname']['id'] = $header_row;

                // Basic grade item information.
                $gradeitemdata['id'] = $grade_object->id;
                $gradeitemdata['itemtype'] = $grade_object->itemtype;
                $gradeitemdata['itemmodule'] = $grade_object->itemmodule;
                $gradeitemdata['iteminstance'] = $grade_object->iteminstance;
                $gradeitemdata['itemnumber'] = $grade_object->itemnumber;
                $gradeitemdata['categoryid'] = $grade_object->categoryid;
                $gradeitemdata['outcomeid'] = $grade_object->outcomeid;
                $gradeitemdata['scaleid'] = $grade_object->outcomeid;

                if ($this->showfeedback) {
                    // Copy $class before appending itemcenter as feedback should not be centered
                    $classfeedback = $class;
                }
                $class .= " itemcenter ";
                if ($this->showweight) {
                    $data['weight']['class'] = $class;
                    $data['weight']['content'] = '-';
                    $data['weight']['headers'] = "$header_cat $header_row weight";
                    // has a weight assigned, might be extra credit

                    // This obliterates the weight because it provides a more informative description.
                    if (is_numeric($hint['weight'])) {
                        $data['weight']['content'] = format_float($hint['weight'] * 100.0, 2) . ' %';
                        $gradeitemdata['weightraw'] = $hint['weight'];
                        $gradeitemdata['weightformatted'] = $data['weight']['content'];
                    }
                    if ($hint['status'] != 'used' && $hint['status'] != 'unknown') {
                        $data['weight']['content'] .= '<br>' . get_string('aggregationhint' . $hint['status'], 'grades');
                        $gradeitemdata['status'] = $hint['status'];
                    }
                }

                if ($this->showgrade) {
                    $gradeitemdata['graderaw'] = '';
                    $gradeitemdata['gradehiddenbydate'] = false;
                    $gradeitemdata['gradeneedsupdate'] = $grade_grade->grade_item->needsupdate;
                    $gradeitemdata['gradeishidden'] = $grade_grade->is_hidden();
                    $gradeitemdata['gradedatesubmitted'] = $grade_grade->get_datesubmitted();
                    $gradeitemdata['gradedategraded'] = $grade_grade->get_dategraded();

                    if ($grade_grade->grade_item->needsupdate) {
                        $data['grade']['class'] = $class.' gradingerror';
                        $data['grade']['content'] = get_string('error');
                    } else if (!empty($CFG->grade_hiddenasdate) and $grade_grade->get_datesubmitted() and !$this->canviewhidden and $grade_grade->is_hidden()
                           and !$grade_grade->grade_item->is_category_item() and !$grade_grade->grade_item->is_course_item()) {
                        // the problem here is that we do not have the time when grade value was modified, 'timemodified' is general modification date for grade_grades records
                        $class .= ' datesubmitted';
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = get_string('submittedon', 'grades', userdate($grade_grade->get_datesubmitted(), get_string('strftimedatetimeshort')));
                        $gradeitemdata['gradehiddenbydate'] = true;
                    } else if ($grade_grade->is_hidden()) {
                        $data['grade']['class'] = $class.' dimmed_text';
                        $data['grade']['content'] = '-';

                        if ($this->canviewhidden) {
                            $gradeitemdata['graderaw'] = $gradeval;
                            $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                                                                $grade_grade->grade_item,
                                                                                true);
                        }
                    } else {
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                                                            $grade_grade->grade_item,
                                                                            true);
                        $gradeitemdata['graderaw'] = $gradeval;
                    }
                    $data['grade']['headers'] = "$header_cat $header_row grade";
                    $gradeitemdata['gradeformatted'] = $data['grade']['content'];
                }

                // Range
                if ($this->showrange) {
                    $data['range']['class'] = $class;
                    $data['range']['content'] = $grade_grade->grade_item->get_formatted_range(GRADE_DISPLAY_TYPE_REAL, $this->rangedecimals);
                    $data['range']['headers'] = "$header_cat $header_row range";

                    $gradeitemdata['rangeformatted'] = $data['range']['content'];
                    $gradeitemdata['grademin'] = $grade_grade->grade_item->grademin;
                    $gradeitemdata['grademax'] = $grade_grade->grade_item->grademax;
                }

                // Percentage
                if ($this->showpercentage) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['percentage']['class'] = $class.' gradingerror';
                        $data['percentage']['content'] = get_string('error');
                    } else if ($grade_grade->is_hidden()) {
                        $data['percentage']['class'] = $class.' dimmed_text';
                        $data['percentage']['content'] = '-';
                        if ($this->canviewhidden) {
                            $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                        }
                    } else {
                        $data['percentage']['class'] = $class;
                        $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                    }
                    $data['percentage']['headers'] = "$header_cat $header_row percentage";
                    $gradeitemdata['percentageformatted'] = $data['percentage']['content'];
                }

                // Lettergrade
                if ($this->showlettergrade) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['lettergrade']['class'] = $class.' gradingerror';
                        $data['lettergrade']['content'] = get_string('error');
                    } else if ($grade_grade->is_hidden()) {
                        $data['lettergrade']['class'] = $class.' dimmed_text';
                        if (!$this->canviewhidden) {
                            $data['lettergrade']['content'] = '-';
                        } else {
                            $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                        }
                    } else {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                    }
                    $data['lettergrade']['headers'] = "$header_cat $header_row lettergrade";
                    $gradeitemdata['lettergradeformatted'] = $data['lettergrade']['content'];
                }

                // Rank
                if ($this->showrank) {
                    $gradeitemdata['rank'] = 0;
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['rank']['class'] = $class.' gradingerror';
                        $data['rank']['content'] = get_string('error');
                        } elseif ($grade_grade->is_hidden()) {
                            $data['rank']['class'] = $class.' dimmed_text';
                            $data['rank']['content'] = '-';
                    } else if (is_null($gradeval)) {
                        // no grade, no rank
                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = '-';

                    } else {
                        /// find the number of users with a higher grade
                        $sql = "SELECT COUNT(DISTINCT(userid))
                                  FROM {grade_grades}
                                 WHERE finalgrade > ?
                                       AND itemid = ?
                                       AND hidden = 0";
                        $rank = $DB->count_records_sql($sql, array($grade_grade->finalgrade, $grade_grade->grade_item->id)) + 1;

                        $data['rank']['class'] = $class;
                        $numusers = $this->get_numusers(false);
                        $data['rank']['content'] = "$rank/$numusers"; // Total course users.

                        $gradeitemdata['rank'] = $rank;
                        $gradeitemdata['numusers'] = $numusers;
                    }
                    $data['rank']['headers'] = "$header_cat $header_row rank";
                }

                // Average
                if ($this->showaverage) {
                    $gradeitemdata['averageformatted'] = '';

                    $data['average']['class'] = $class;
                    if (!empty($this->gtree->items[$eid]->avg)) {
                        $data['average']['content'] = $this->gtree->items[$eid]->avg;
                        $gradeitemdata['averageformatted'] = $this->gtree->items[$eid]->avg;
                    } else {
                        $data['average']['content'] = '-';
                    }
                    $data['average']['headers'] = "$header_cat $header_row average";
                }

                // Feedback
                if ($this->showfeedback) {
                    $gradeitemdata['feedback'] = '';
                    $gradeitemdata['feedbackformat'] = $grade_grade->feedbackformat;

                    if ($grade_grade->overridden > 0 AND ($type == 'categoryitem' OR $type == 'courseitem')) {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = get_string('overridden', 'grades').': ' . format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                        $gradeitemdata['feedback'] = $grade_grade->feedback;
                    } else if (!$this->canviewhidden and $grade_grade->is_hidden()) {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = '';
                    } else {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';

                        if (empty($grade_grade->feedback)) {
                            $data['feedback']['content'] = '';
                        } else {                        
                            $data['feedback']['content'] = format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                            $gradeitemdata['feedback'] = $grade_grade->feedback;
                        }

                        // At this point $data['feedback']['content'] will contain the feedback or an empty string.
                        // Now we check if there is a feedback function for this module.
                        $feedbackfunction = 'get' . ucfirst($grade_object->itemmodule) . 'Feedback';

                        if (method_exists($this, $feedbackfunction)){
                            $this->{$feedbackfunction}($data, $grade_object);
                        }
                    }

                    $data['feedback']['headers'] = "$header_cat $header_row feedback";
                }

                // Contribution to the course total column.
                if ($this->showcontributiontocoursetotal) {
                    $data['contributiontocoursetotal']['class'] = $class;
                    $data['contributiontocoursetotal']['content'] = '-';
                    $data['contributiontocoursetotal']['headers'] = "$header_cat $header_row contributiontocoursetotal";

                }

                $this->gradeitemsdata[] = $gradeitemdata;
            }

            // We collect the aggregation hints whether they are hidden or not.
            if ($this->showcontributiontocoursetotal) {
                $hint['grademax'] = $grade_grade->grade_item->grademax;
                $hint['grademin'] = $grade_grade->grade_item->grademin;
                $hint['grade'] = $gradeval;
                $parent = $grade_object->load_parent_category();
                if ($grade_object->is_category_item()) {
                    $parent = $parent->load_parent_category();
                }
                $hint['parent'] = $parent->load_grade_item()->id;
                $this->aggregationhints[$grade_grade->itemid] = $hint;
            }
        }

        /// Category
        if ($type == 'category') {
            $data['leader']['class'] = $class.' '.$alter."d$depth b1t b2b b1l";
            $data['leader']['rowspan'] = $element['rowspan'];

            if ($this->switch) { // alter style based on whether aggregation is first or last
               $data['itemname']['class'] = $class.' '.$alter."d$depth b1b b1t";
            } else {
               $data['itemname']['class'] = $class.' '.$alter."d$depth b2t";
            }
            $data['itemname']['colspan'] = ($this->maxdepth - $depth + count($this->tablecolumns) - 1);
            $data['itemname']['content'] = $fullname;
            $data['itemname']['celltype'] = 'th';
            $data['itemname']['id'] = "cat_{$grade_object->id}_{$this->user->id}";
        }

        /// Add this row to the overall system
        foreach ($data as $key => $celldata) {
            $data[$key]['class'] .= ' column-' . $key;
        }
        $this->tabledata[] = $data;

        /// Recursively iterate through all child elements
        if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->fill_table_recursive($element['children'][$key]);
            }
        }

        // Check we are showing this column, and we are looking at the root of the table.
        // This should be the very last thing this fill_table_recursive function does.
        if ($this->showcontributiontocoursetotal && ($type == 'category' && $depth == 1)) {
            // We should have collected all the hints by now - walk the tree again and build the contributions column.

            $this->fill_contributions_column($element);
        }
    }


    protected function getAssignFeedback(&$data, $grade_object) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        // It is so we first retrieve all the assignment modules in the course.
        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        // Now we use the iteminstance to retrieve the assignment module for this grade.
        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $course = get_course($this->courseid);
            $assign = new assign($context, $cm, $course);
            $feedbackplugins = $assign->get_feedback_plugins();
            $renderer = $assign->get_renderer();
            $config = get_config('assign');
            // Get the feedback plugin that is set to push comments to the gradebook. This is what populates
            // $grade_grade->feedback unless it is overridden.
            $gradebookfeedback = str_replace('assignfeedback_', '', $config->feedback_plugin_for_gradebook);
            // We need a stdClass with an id property from assign_grades that is for this $grade_object. 
            // It is needed to test $feedbackplugin->is_empty() for the remaining assignfeedback plugins.
            $params = array(
                'assignment' => $assign->get_instance()->id,  
                'userid' => $this->user->id
                );

            $grade = $DB->get_record('assign_grades', $params, '*');
            $grade = $DB->get_record('assign_grades', $params, '*');

            foreach($feedbackplugins as $feedbackplugin) {
                if ($feedbackplugin->is_enabled() &&
                    $feedbackplugin->is_visible() &&
                    $feedbackplugin->has_user_summary()                                            
                ){
                    $feedbacksubtitle = '<p class="feedbackpluginname">' . $feedbackplugin->get_name() . '</p>';

                    // Add the title of the default feedback type if the feedback is not empty.
                    if ($feedbackplugin->get_type() == $gradebookfeedback) {
                        if ($data['feedback']['content']) {
                            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
                        }
                    // Use the plugin function to output the feedback.
                    } elseif ($grade && !$feedbackplugin->is_empty($grade)) {
                        $data['feedback']['content'] .= $feedbacksubtitle;
                        // @TODO Should really just get files and do simplified rendering without tables
                        $data['feedback']['content'] .= $feedbackplugin->view($grade);
                    }
                }
            }

            // Get any rubric feedback.
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('rubric', 'gradereport_culuser') . '</p>';
            $rubrictext = $this->rubric_text($this->user->id, $this->courseid, $assign->get_instance()->id, 'assign');

            if ($rubrictext) {
                $data['feedback']['content'] .= $feedbacksubtitle .= $rubrictext;
            }

            // Get any marking guide feedback.
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('markingguide', 'gradereport_culuser') . '</p>';
            $rubrictext = $this->marking_guide_text($this->user->id, $this->courseid, $assign->get_instance()->id, 'assign');

            if ($rubrictext) {
                $data['feedback']['content'] .= $feedbacksubtitle .= $rubrictext;
            }
        }
    }

    protected function getKalvidassignFeedback(&$data, $grade_object) {
        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

        if ($data['feedback']['content']) {
            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
        }        
    }

    protected function getPeerassessmentFeedback(&$data, $grade_object) {
        // NB Overriding grades and feedback in the gradebbok shows changes in gradebook but not in
        // assignment.



        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

        if ($data['feedback']['content']) {
            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
        }

        // Feedback files.
        // $mygroup = peerassessment_get_mygroup($course);
        // $group = $DB->get_record('groups', array('id' => $mygroup), '*', MUST_EXIST);

        // peerassessment_feedback_files($context, $group);

        // Peer grades.
        // Looks like they can't see these. They just see the grades and feedback they gave others.


    }

    protected function getTurnitintooltwoFeedback(&$data, $grade_object) {

    }

    protected function getWorkshopFeedback(&$data, $grade_object) {
        global $DB, $CFG, $PAGE;

        require_once($CFG->dirroot . '/mod/workshop/locallib.php');

        // It is so we first retrieve all the workshop modules in the course.
        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        // Now we use the iteminstance to retrieve the workshop module for this grade.
        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $course = get_course($this->courseid);

            $params = array(
                'id' => $grade_object->iteminstance
                );

            $workshoprecord = $DB->get_record('workshop', $params, '*');
            $workshop = new workshop($workshoprecord, $cm, $course, $context);
            $strategy = $workshop->grading_strategy_instance();
            $strategyclass = get_class($strategy);
            $strategynamearray = explode('_', $strategyclass);
            $strategyname = $strategynamearray[1];

            $params = array(
                'workshopid' => $grade_object->iteminstance,  
                'authorid' => $this->user->id
                );

            if($submission = $DB->get_record('workshop_submissions', $params, '*')) {
                $assessments = $workshop->get_assessments_of_submission($submission->id);           

                if($grade_object->itemnumber == 0) {
                               
                    if($data['feedback']['content']) {
                        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';
                        $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
                    }

                    $overallfeedback = '';
                    $strategyfeedback = '';
                    $filefeedback = '';

                    // This gets overall feedback.
                    foreach($assessments as $assessment) {
                        $assessment = $workshop->prepare_assessment($assessment, null);                        
                        // Overall peer feedback.
                        $overallfeedback .= $this->overall_feedback($assessment);
                        // Strategy feedback.
                        $diminfo = $strategy->get_dimensions_info();
                        $strategyfunctionname = 'get_formatted_' . $strategyname . '_feedback';

                        if(method_exists($this, $strategyfunctionname)) {
                            $feedback = $this->$strategyfunctionname($assessment, $diminfo);
                            $strategyfeedback .= $feedback;
                        }

                        // File feedback.
                        $filefeedback .= $this->overall_feedback_files($assessment);
                    }

                    // Put it all together.
                    $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('peerfeedback', 'gradereport_culuser') . '</p>';
                    $data['feedback']['content'] .= $feedbacksubtitle . $overallfeedback;
                    $feedbacksubtitle = '<b>' . get_string($strategyname . 'title', 'gradereport_culuser') . '</b>';
                    $data['feedback']['content'] .= $feedbacksubtitle . $strategyfeedback ;

                    $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('files', 'gradereport_culuser') . '</p>';                    

                    if($filefeedback) {
                        $data['feedback']['content'] .= $feedbacksubtitle .= $filefeedback;
                    }

                } else {
                    // Reviewer feedback.
                    foreach($assessments as $assessment) {
                        $assessmentfeedback = new workshop_feedback_reviewer($assessment);
                        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

                        try {
                            $data['feedback']['content'] .= $feedbacksubtitle .= strip_tags($assessmentfeedback->get_content());
                        } catch(Exception $e) {
                            // No content. Error is thrown.
                        }
                    }
                }
            }
        }
    }


    /**
     * Get the Assign Rubric feedback
     * 
     * @param int $userid The id of the user who's feedback being viewed
     * @param int $courseid The course the Rubric is being checked for
     * @param int $iteminstance The instance of the module item 
     * @param int $itemmodule The module currently being queried
     * @return str the text for the Rubric
     */
    public function rubric_text($userid, $courseid, $iteminstance, $itemmodule) {
        global $DB;

        $sql = "SELECT DISTINCT rc.id, rc.description, rl.definition 
            FROM {gradingform_rubric_criteria} rc
            JOIN {gradingform_rubric_levels} rl
            ON rc.id = rl.criterionid
            JOIN {gradingform_rubric_fillings} rf
            ON rl.id = rf.levelid AND rc.id = rf.criterionid
            JOIN {grading_instances} gin
            ON rf.instanceid = gin.id
            JOIN {assign_grades} ag
            ON gin.itemid = ag.id
            JOIN {grade_items} gi
            ON ag.assignment = gi.iteminstance AND ag.userid = ?
            JOIN {grade_grades} gg
            ON gi.id = gg.itemid AND gi.itemmodule = ? 
            AND gi.courseid = ? AND gg.userid = ? AND gi.iteminstance = ? AND status = ?";

        $params = array($userid, $itemmodule, $courseid, $userid, $iteminstance, 1);
        $rubrics = $DB->get_recordset_sql($sql, $params);
        $out = '';

        if ($rubrics) {
            foreach ($rubrics as $rubric) {
                if ($rubric->description || $rubric->definition) {
                    $out .= "<strong>" . $rubric->description . ": </strong>" . $rubric->definition . "<br/>";
                }
            }
        }

        return $out;
    }

    /**
     * Get the Marking guide feedback
     * 
     * @param int $userid The id of the user who's feedback being viewed
     * @param int $courseid The course the Marking guide is being checked for
     * @param int $iteminstance The instance of the module item 
     * @param int $itemmodule The module currently being queried
     * @return str the text for the Marking guide
     */
    public function marking_guide_text($userid, $courseid, $iteminstance, $itemmodule) {
        global $DB;

        $sql = "SELECT DISTINCT gc.shortname,gf.remark 
            FROM {gradingform_guide_criteria} gc
            JOIN {gradingform_guide_fillings} gf
            ON gc.id = gf.criterionid
            JOIN {grading_instances} gin
            ON gf.instanceid = gin.id
            JOIN {assign_grades} ag
            ON gin.itemid = ag.id
            JOIN {grade_items} gi
            ON ag.assignment = gi.iteminstance AND ag.userid = ?
            JOIN {grade_grades} gg
            ON gi.id = gg.itemid AND gi.itemmodule = ? 
            AND gi.courseid = ? AND gg.userid = ? AND gi.iteminstance = ?";

        $params = array($userid, $itemmodule, $courseid, $userid, $iteminstance);
        $guides = $DB->get_recordset_sql($sql, $params);
        $out = '';

        if ($guides) {
            foreach ($guides as $guide) {
                if ($guide->shortname || $guide->remark) {
                    $out .= "<strong>" . $guide->shortname . ": </strong>" . $guide->remark . "<br/>";
                }
            }
        }

        return $out;
    }


    /**
     * Returns the list of current grades filled by the reviewer indexed by dimensionid
     *
     * @param workshop_assessment $assessment Assessment record
     * @return array [int dimensionid] => stdclass workshop_grades record
     */
    public function get_formatted_accumulative_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        $feedbacktitle  = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT dimensionid, ws.description, ws.grade, wg.grade as score, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_accumulative} ws
                ON wg.dimensionid = ws.id
                WHERE assessmentid = :assessmentid AND strategy = :strategy AND dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'accumulative'); // @TODO variable or function for each?
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            // $feedbacktitle .= '<br/><b>' . get_string('accumulativetitle', 'gradereport_culuser') . '</b>';
            
            foreach($records as $record) {
                if($record->description && $record->score) {
                    $feedback .= '<br/><b>' . strip_tags($record->description) . '</b>: ' . get_string('grade', 'gradereport_culuser') . round($record->score) . '/' . round($record->grade);
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '<br/>';
                }
            }
        }

        return $feedback;
    }

    /**
     * Returns the list of current grades filled by the reviewer indexed by dimensionid
     *
     * @param workshop_assessment $assessment Assessment record
     * @return array [int dimensionid] => stdclass workshop_grades record
     */
    public function get_formatted_comments_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT dimensionid, ws.description, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_comments} ws
                ON wg.dimensionid = ws.id
                WHERE assessmentid = :assessmentid AND strategy = :strategy AND dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'comments'); // @TODO variable or function for each?
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            $feedback .= "<b>" . get_string('commentstitle', 'gradereport_culuser') . "</b>";
            
            foreach($records as $record) {
                if($record->description && $record->score) {
                    $feedback .= '<br/><b>' . strip_tags($record->description);
                    $feedback .= '<br/><b>' . get_string('comment', 'report_myfeedback') . '</b>: ' . strip_tags($record->peercomment) . '<br/>';
                }
            }
        }

        return $feedback;
    }

    /**
     * Returns the list of current grades filled by the reviewer indexed by dimensionid
     *
     * @param workshop_assessment $assessment Assessment record
     * @return array [int dimensionid] => stdclass workshop_grades record
     */
    public function get_formatted_numerrors_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT dimensionid, ws.description, ws.grade0, ws.grade1, wg.grade, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_numerrors} ws
                ON wg.dimensionid = ws.id
                WHERE assessmentid = :assessmentid AND strategy = :strategy AND dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'numerrors'); // @TODO variable or function for each?
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            $feedback .= "<b>" . get_string('accumulativetitle', 'gradereport_culuser') . "</b>";
            
            foreach($records as $record) {
                if($record->description && $record->score) {
                    $feedback .= '<br/><b>' . strip_tags($record->description) . '</b>: ' . ($record->grade < 1.0 ? strip_tags($record->grade0) : strip_tags($record->grade1));
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '<br/>';
                }
            }
        }

        return $feedback;
    }

    /**
     * Returns the list of current grades filled by the reviewer indexed by dimensionid
     *
     * @param workshop_assessment $assessment Assessment record
     * @return array [int dimensionid] => stdclass workshop_grades record
     */
    public function get_formatted_rubric_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT dimensionid, ws.description, rl.definition, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_rubric} ws
                ON wg.dimensionid = ws.id
                LEFT JOIN {workshopform_rubric_levels} rl 
                ON rl.dimensionid = ws.id
                AND rl.grade = wg.grade                                
                WHERE assessmentid = :assessmentid AND strategy = :strategy AND dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'rubric'); // @TODO variable or function for each?
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            $feedback .= "<b>" . get_string('accumulativetitle', 'gradereport_culuser') . "</b>";
            
            foreach($records as $record) {
                if($record->description && $record->score) {
                    $feedback .= '<br/><b>' . strip_tags($record->description) . '</b>: ' . strip_tags($record->definition) ;
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '<br/>';
                }
            }
        }

        return $feedback;
    }

    /**
     * Renders the overall feedback for the author of the submission
     *
     * @param workshop_assessment $assessment
     * @return string HTML
     */
    public function overall_feedback(workshop_assessment $assessment) {
        global $OUTPUT;

        $content = $assessment->get_overall_feedback_content();

        if ($content === false) {
            return '';
        }

        $o = '';

        if (!is_null($content)) {
            $o .= $content;
        }

        return $o;
    }

    /**
     * Renders the overall feedback for the author of the submission
     *
     * @param workshop_assessment $assessment
     * @return string HTML
     */
    public function overall_feedback_files(workshop_assessment $assessment) {
        global $OUTPUT;

        $o = '';
        $attachments = $assessment->get_overall_feedback_attachments();

        if (!empty($attachments)) {
            $o .= $OUTPUT->container_start('attachments');
            $images = '';
            $files = '';

            foreach ($attachments as $attachment) {
                $icon = $OUTPUT->pix_icon(file_file_icon($attachment), get_mimetype_description($attachment),
                    'moodle', array('class' => 'icon'));
                $link = html_writer::link($attachment->fileurl, $icon.' '.substr($attachment->filepath.$attachment->filename, 1));

                if (file_mimetype_in_typegroup($attachment->mimetype, 'web_image')) {
                    $preview = html_writer::empty_tag('img', array('src' => $attachment->previewurl, 'alt' => '', 'class' => 'preview'));
                    $preview = html_writer::tag('a', $preview, array('href' => $attachment->fileurl));
                    $images .= $OUTPUT->container($preview);
                } else {
                    $files .= html_writer::tag('li', $link, array('class' => $attachment->mimetype));
                }
            }

            if ($images) {
                $images = $OUTPUT->container($images, 'images');
            }

            if ($files) {
                $files = html_writer::tag('ul', $files, array('class' => 'ygtvlnfiles'));
            }

            $o .= $images.$files;
            $o .= $OUTPUT->container_end();
        }

        if ($o === '') {
            return '';
        }

        return $o;
    }
}

function grade_report_culuser_settings_definition(&$mform) {
    global $CFG;

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('hide'),
                      1 => get_string('show'));

    if (empty($CFG->grade_report_culuser_showrank)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showrank', get_string('showrank', 'grades'), $options);
    $mform->addHelpButton('report_culuser_showrank', 'showrank', 'grades');

    if (empty($CFG->grade_report_culuser_showpercentage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showpercentage', get_string('showpercentage', 'grades'), $options);
    $mform->addHelpButton('report_culuser_showpercentage', 'showpercentage', 'grades');

    if (empty($CFG->grade_report_culuser_showgrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showgrade', get_string('showgrade', 'grades'), $options);

    if (empty($CFG->grade_report_culuser_showfeedback)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showfeedback', get_string('showfeedback', 'grades'), $options);

    if (empty($CFG->grade_report_culuser_showweight)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showweight', get_string('showweight', 'grades'), $options);

    if (empty($CFG->grade_report_culuser_showaverage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showaverage', get_string('showaverage', 'grades'), $options);
    $mform->addHelpButton('report_culuser_showaverage', 'showaverage', 'grades');

    if (empty($CFG->grade_report_culuser_showlettergrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showlettergrade', get_string('showlettergrade', 'grades'), $options);
    if (empty($CFG->grade_report_culuser_showcontributiontocoursetotal)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_culuser_showcontributiontocoursetotal]);
    }

    $mform->addElement('select', 'report_culuser_showcontributiontocoursetotal', get_string('showcontributiontocoursetotal', 'grades'), $options);
    $mform->addHelpButton('report_culuser_showcontributiontocoursetotal', 'showcontributiontocoursetotal', 'grades');

    if (empty($CFG->grade_report_culuser_showrange)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_culuser_showrange', get_string('showrange', 'grades'), $options);

    $options = array(0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
    if (! empty($CFG->grade_report_culuser_rangedecimals)) {
        $options[-1] = $options[$CFG->grade_report_culuser_rangedecimals];
    }
    $mform->addElement('select', 'report_culuser_rangedecimals', get_string('rangedecimals', 'grades'), $options);

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('shownohidden', 'grades'),
                      1 => get_string('showhiddenuntilonly', 'grades'),
                      2 => get_string('showallhidden', 'grades'));

    if (empty($CFG->grade_report_culuser_showhiddenitems)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_culuser_showhiddenitems]);
    }

    $mform->addElement('select', 'report_culuser_showhiddenitems', get_string('showhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_culuser_showhiddenitems', 'showhiddenitems', 'grades');

    //showtotalsifcontainhidden
    $options = array(-1 => get_string('default', 'grades'),
                      GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
                      GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
                      GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades') );

    if (empty($CFG->grade_report_culuser_showtotalsifcontainhidden)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_culuser_showtotalsifcontainhidden]);
    }

    $mform->addElement('select', 'report_culuser_showtotalsifcontainhidden', get_string('hidetotalifhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_culuser_showtotalsifcontainhidden', 'hidetotalifhiddenitems', 'grades');

}

/**
 * Profile report callback.
 *
 * @param object $course The course.
 * @param object $user The user.
 * @param boolean $viewasuser True when we are viewing this as the targetted user sees it.
 */
function grade_report_culuser_profilereport($course, $user, $viewasuser = false) {
    global $OUTPUT;
    if (!empty($course->showgrades)) {

        $context = context_course::instance($course->id);

        /// return tracking object
        $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'culuser', 'courseid'=>$course->id, 'userid'=>$user->id));
        // Create a report instance
        $report = new grade_report_usercul($course->id, $gpr, $context, $user->id, $viewasuser);

        // print the page
        echo '<div class="grade-report-user">'; // css fix to share styles with real report page
        if ($report->fill_table()) {
            echo $report->print_table(true);
        }
        echo '</div>';
    }
}

// /**
//  * Add nodes to myprofile page.
//  *
//  * @param \core_user\output\myprofile\tree $tree Tree object
//  * @param stdClass $user user object
//  * @param bool $iscurrentuser
//  * @param stdClass $course Course object
//  */
// function gradereport_culuser_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
//     global $CFG, $USER;
//     if (empty($course)) {
//         // We want to display these reports under the site context.
//         $course = get_fast_modinfo(SITEID)->get_course();
//     }
//     $usercontext = context_user::instance($user->id);
//     $anyreport = has_capability('moodle/user:viewuseractivitiesreport', $usercontext);

//     // Start capability checks.
//     if ($anyreport || ($course->showreports && $user->id == $USER->id)) {
//         // Add grade hardcoded grade report if necessary.
//         $gradeaccess = false;
//         $coursecontext = context_course::instance($course->id);
//         if (has_capability('moodle/grade:viewall', $coursecontext)) {
//             // Can view all course grades.
//             $gradeaccess = true;
//         } else if ($course->showgrades) {
//             if ($iscurrentuser && has_capability('moodle/grade:view', $coursecontext)) {
//                 // Can view own grades.
//                 $gradeaccess = true;
//             } else if (has_capability('moodle/grade:viewall', $usercontext)) {
//                 // Can view grades of this user - parent most probably.
//                 $gradeaccess = true;
//             } else if ($anyreport) {
//                 // Can view grades of this user - parent most probably.
//                 $gradeaccess = true;
//             }
//         }
//         if ($gradeaccess) {
//             $url = new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $course->id, 'user' => $user->id));
//             $node = new core_user\output\myprofile\node('reports', 'grade', get_string('grade'), null, $url);
//             $tree->add_node($node);
//         }
//     }
// }
