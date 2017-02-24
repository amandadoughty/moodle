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
                require_once($CFG->dirroot . '/mod/assign/locallib.php');

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
        global $DB;

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

    protected function getWorkshopFeedback(&$data, $grade_object) {
        global $DB;

        // It is so we first retrieve all the workshop modules in the course.
        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        // Now we use the iteminstance to retrieve the workshop module for this grade.
        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $course = get_course($this->courseid);
            // $workshop = new workshop($context, $cm, $course);
            $params = array(
                'workshopid' => $grade_object->iteminstance,  
                'authorid' => $this->user->id
                );

            $submission = $DB->get_record('workshop_submissions', $params, '*');
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

            if ($data['feedback']['content']) {
                $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
            }

            if($submission) {
                // Get any workshop feedback.
                $workshoptext = $this->has_workshop_feedback($this->user->id, $submission->id, $grade_object->iteminstance, $this->courseid, $grade_object->itemnumber);

                $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('workshop', 'gradereport_culuser') . '</p>';

                if ($workshoptext) {
                    $data['feedback']['content'] .= $feedbacksubtitle .= $workshoptext;
                }
            }
        }

    }


    /**
     * Get the Rubric feedback
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
     * Checks whether or not there is any workshop feedback file either from peers or tutor
     * 
     * @param int $userid The user id
     * @param int $subid The workshop submission id
     * @return boolean true if there is a feedback file and false if there ain't
     */
    public function has_workshop_feedback_file($userid, $subid) {
        global $DB;
        // Is there any feedback file?
        $sql = "SELECT DISTINCT max(wa.id) as id, wa.feedbackauthorattachment
                FROM {workshop_assessments} wa 
                JOIN {workshop_submissions} ws ON wa.submissionid=ws.id 
                AND ws.authorid=? AND ws.id=? and ws.example = 0";
        $params = array($userid, $subid);
        $feedbackfile = $DB->get_record_sql($sql, $params);
        if ($feedbackfile) {
            if ($feedbackfile->feedbackauthorattachment != 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gets and returns any workshop feedback
     * 
     * @global stdClass $DB The database object
     * @global stdClass $CFG The global config
     * @param int $userid The user id
     * @param int $subid The workshop submission id
     * @param int $assignid The workshop id
     * @param int $cid The course id
     * @param int $itemnumber The grade_item itemnumber
     * @return string All the feedback information
     */
    public function has_workshop_feedback($userid, $subid, $assignid, $cid, $itemnumber) {
        global $DB, $CFG;
        $feedback = '';

        //Get the other feedback that comes when graded so will have a grade id otherwise it is not unique
        $peer = "SELECT DISTINCT wg.id, wg.peercomment, wa.reviewerid, wa.feedbackreviewer, w.conclusion
        FROM {workshop} w
        JOIN {workshop_submissions} ws ON ws.workshopid=w.id AND w.course=? AND w.useexamples=0      
        JOIN {workshop_assessments} wa ON wa.submissionid=ws.id AND ws.authorid=?
        AND ws.workshopid=? AND ws.example=0 AND wa.submissionid=?
        LEFT JOIN {workshop_grades} wg ON wg.assessmentid=wa.id AND wa.submissionid=?";
        $arr = array($cid, $userid, $assignid, $subid, $subid);

        if ($assess = $DB->get_recordset_sql($peer, $arr)) {
            if ($itemnumber == 1) {
                foreach ($assess as $a) {
                    if ($a->feedbackreviewer && strlen($a->feedbackreviewer) > 0) {
                        $feedback = (strip_tags($a->feedbackreviewer) ? "<b>" . get_string('tutorfeedback', 'report_myfeedback') . "</b><br/>" . strip_tags($a->feedbackreviewer) : '');
                    }
                }
                return $feedback;
            }
        }

        if ($itemnumber != 1) {
            //get the feedback from author as this does not necessarily mean they are graded
            $auth = "SELECT DISTINCT wa.id, wa.feedbackauthor, wa.reviewerid
            FROM {workshop} w
            JOIN {workshop_submissions} ws ON ws.workshopid=w.id AND w.course=? AND w.useexamples=0      
            JOIN {workshop_assessments} wa ON wa.submissionid=ws.id AND ws.authorid=?
            AND ws.workshopid=? AND ws.example=0 AND wa.submissionid=?";
            $par = array($cid, $userid, $assignid, $subid);
            $self = $pfeed = false;
            if ($asse = $DB->get_records_sql($auth, $par)) {
                foreach ($asse as $cub) {
                    if ($cub->feedbackauthor && $cub->reviewerid != $userid) {
                        $pfeed = true;
                    }
                }
                if ($pfeed) {
                    $feedback .= strip_tags($feedback) ? '<br/>' : '';
                    $feedback .= '<b>' . get_string('peerfeedback', 'report_myfeedback') . '</b>';
                }
                foreach ($asse as $as) {
                    if ($as->feedbackauthor && $as->reviewerid != $userid) {
                        $feedback .= (strip_tags($as->feedbackauthor) ? '<br/>' . strip_tags($as->feedbackauthor) : '');
                    }
                }
                foreach ($asse as $cub1) {
                    if ($cub1->feedbackauthor && $cub1->reviewerid == $userid) {
                        $self = true;
                    }
                }
                if ($self) {
                    $feedback .= strip_tags($feedback) ? '<br/>' : '';
                    $feedback .= '<b>' . get_string('selfassessment', 'report_myfeedback') . '</b>';
                }
                foreach ($asse as $as1) {
                    if ($as1->feedbackauthor && $as1->reviewerid == $userid) {
                        $feedback .= (strip_tags($as1->feedbackauthor) ? '<br/>' . strip_tags($as1->feedbackauthor) : '');
                    }
                }
            }
        }

        //get comments strategy type
        $sql_c = "SELECT wg.id as gradeid, wa.reviewerid, a.description, peercomment
          FROM {workshopform_accumulative} a
          JOIN {workshop_grades} wg ON wg.dimensionid=a.id AND wg.strategy = 'comments'
          JOIN {workshop_assessments} wa ON wg.assessmentid = wa.id AND wa.submissionid = ?
          JOIN {workshop_submissions} ws ON wa.submissionid = ws.id
          AND ws.workshopid = ? AND ws.example = 0 AND ws.authorid = ?
          ORDER BY wa.reviewerid";
        $params_c = array($subid, $assignid, $userid);
        $c = 0;
        if ($commentscheck = $DB->get_records_sql($sql_c, $params_c)) {
            foreach ($commentscheck as $com) {
                if (strip_tags($com->description)) {
                    $c = 1;
                }
            }
            if ($c) {
                $feedback .= strip_tags($feedback) ? '<br/>' : '';
                $feedback .= "<br/><b>" . get_string('comments', 'report_myfeedback') . "</b>";
            }
            foreach ($commentscheck as $ts) {
                $feedback .= strip_tags($ts->description) ? "<br/><b>" . $ts->description : '';
                $feedback .= strip_tags($ts->description) ? "<br/><b>" . get_string('comment', 'report_myfeedback') . "</b>: " . strip_tags($ts->peercomment) . "<br/>" : '';
            }
        }

        //get accumulative strategy type
        $sql_a = "SELECT wg.id as gradeid, wa.reviewerid, a.description, wg.grade as score, a.grade, peercomment
          FROM {workshopform_accumulative} a
          JOIN {workshop_grades} wg ON wg.dimensionid=a.id AND wg.strategy='accumulative'
          JOIN {workshop_assessments} wa ON wg.assessmentid=wa.id AND wa.submissionid=?
          JOIN {workshop_submissions} ws ON wa.submissionid=ws.id
          AND ws.workshopid=? AND ws.example=0 AND ws.authorid = ?
          ORDER BY wa.reviewerid";
        $params_a = array($subid, $assignid, $userid);
        $a = 0;
        if ($accumulativecheck = $DB->get_records_sql($sql_a, $params_a)) {
            foreach ($accumulativecheck as $acc) {
                if (strip_tags($acc->description && $acc->score)) {
                    $a = 1;
                }
            }
            if ($a) {
                $feedback .= strip_tags($feedback) ? '<br/>' : '';
                $feedback .= "<br/><b>" . get_string('accumulativetitle', 'report_myfeedback') . "</b>";
            }
            foreach ($accumulativecheck as $tiv) {
                $feedback .= strip_tags($acc->description && $acc->score) ? "<br/><b>" . strip_tags($tiv->description) . "</b>: " . get_string('grade', 'report_myfeedback') . round($tiv->score) . "/" . round($tiv->grade) : '';
                $feedback .= strip_tags($acc->description && $acc->score) ? "<br/><b>" . get_string('comment', 'report_myfeedback') . "</b>: " . strip_tags($tiv->peercomment) . "<br/>" : '';
            }
        }

        //get the rubrics strategy type
        $sql = "SELECT wg.id as gradeid, wa.reviewerid, r.description, l.definition, peercomment
          FROM {workshopform_rubric} r
          LEFT JOIN {workshopform_rubric_levels} l ON (l.dimensionid = r.id) AND r.workshopid=?
          JOIN {workshop_grades} wg ON wg.dimensionid=r.id AND l.grade=wg.grade and wg.strategy='rubric'
          JOIN {workshop_assessments} wa ON wg.assessmentid=wa.id AND wa.submissionid=?
          JOIN {workshop_submissions} ws ON wa.submissionid=ws.id
          AND ws.workshopid=? AND ws.example=0 AND ws.authorid = ?
          ORDER BY wa.reviewerid";
        $params = array($assignid, $subid, $assignid, $userid);
        $r = 0;
        if ($rubriccheck = $DB->get_records_sql($sql, $params)) {
            foreach ($rubriccheck as $rub) {
                if (strip_tags($rub->description && $rub->definition)) {
                    $r = 1;
                }
            }
            if ($r) {
                $feedback .= strip_tags($feedback) ? '<br/>' : '';
                $feedback .= "<br/><span style=\"font-weight:bold;\"><img src=\"" .
                        $CFG->wwwroot . "/report/myfeedback/pix/rubric.png\">" . get_string('rubrictext', 'report_myfeedback') . "</span>";
            }
            foreach ($rubriccheck as $rec) {
                $feedback .= strip_tags($rec->description && $rec->definition) ? "<br/><b>" . strip_tags($rec->description) . "</b>: " . strip_tags($rec->definition) : '';
                $feedback .= strip_tags($rec->peercomment) ? "<br/><b>" . get_string('comment', 'report_myfeedback') . "</b>: " . strip_tags($rec->peercomment) . "<br/>" : '';
            }
        }

        //get the numerrors strategy type
        $sql_n = "SELECT wg.id as gradeid, wa.reviewerid, n.description, wg.grade, n.grade0, n.grade1, peercomment
          FROM {workshopform_numerrors} n
          JOIN {workshop_grades} wg ON wg.dimensionid=n.id AND wg.strategy='numerrors'
          JOIN {workshop_assessments} wa ON wg.assessmentid=wa.id AND wa.submissionid=?
          JOIN {workshop_submissions} ws ON wa.submissionid=ws.id
          AND ws.workshopid=? AND ws.example=0 AND ws.authorid = ?
          ORDER BY wa.reviewerid";
        $params_n = array($subid, $assignid, $userid);
        $n = 0;
        if ($numerrorcheck = $DB->get_records_sql($sql_n, $params_n)) {
            foreach ($numerrorcheck as $num) {
                if ($num->gradeid) {
                    $n = 1;
                }
            }
            if ($n) {
                $feedback .= strip_tags($feedback) ? '<br/>' : '';
                $feedback .= "<br/><b>" . get_string('numerrortitle', 'report_myfeedback') . "</b>";
            }
            foreach ($numerrorcheck as $err) {
                $feedback .= $err->gradeid ? "<br/><b>" . strip_tags($err->description) . "</b>: " . ($err->grade < 1.0 ? strip_tags($err->grade0) : strip_tags($err->grade1)) : '';
                $feedback .= $err->gradeid ? "<br/><b>" . get_string('comment', 'report_myfeedback') . "</b>: " . strip_tags($err->peercomment) . "<br/>" : '';
            }
        }

        return $feedback;
    }











    // /**
    //  * This function is called after the table has been built and the aggregationhints
    //  * have been collected. We need this info to walk up the list of parents of each
    //  * grade_item.
    //  *
    //  * @param $element - An array containing the table data for the current row.
    //  */
    // public function fill_contributions_column($element) {

    //     // Recursively iterate through all child elements.
    //     if (isset($element['children'])) {
    //         foreach ($element['children'] as $key=>$child) {
    //             $this->fill_contributions_column($element['children'][$key]);
    //         }
    //     } else if ($element['type'] == 'item') {
    //         // This is a grade item (We don't do this for categories or we would double count).
    //         $grade_object = $element['object'];
    //         $itemid = $grade_object->id;

    //         // Ignore anything with no hint - e.g. a hidden row.
    //         if (isset($this->aggregationhints[$itemid])) {

    //             // Normalise the gradeval.
    //             $gradecat = $grade_object->load_parent_category();
    //             if ($gradecat->aggregation == GRADE_AGGREGATE_SUM) {
    //                 // Natural aggregation/Sum of grades does not consider the mingrade, cannot traditionnally normalise it.
    //                 $graderange = $this->aggregationhints[$itemid]['grademax'];

    //                 if ($graderange != 0) {
    //                     $gradeval = $this->aggregationhints[$itemid]['grade'] / $graderange;
    //                 } else {
    //                     $gradeval = 0;
    //                 }
    //             } else {
    //                 $gradeval = grade_grade::standardise_score($this->aggregationhints[$itemid]['grade'],
    //                     $this->aggregationhints[$itemid]['grademin'], $this->aggregationhints[$itemid]['grademax'], 0, 1);
    //             }

    //             // Multiply the normalised value by the weight
    //             // of all the categories higher in the tree.
    //             $parent = null;
    //             do {
    //                 if (!is_null($this->aggregationhints[$itemid]['weight'])) {
    //                     $gradeval *= $this->aggregationhints[$itemid]['weight'];
    //                 } else if (empty($parent)) {
    //                     // If we are in the first loop, and the weight is null, then we cannot calculate the contribution.
    //                     $gradeval = null;
    //                     break;
    //                 }

    //                 // The second part of this if is to prevent infinite loops
    //                 // in case of crazy data.
    //                 if (isset($this->aggregationhints[$itemid]['parent']) &&
    //                         $this->aggregationhints[$itemid]['parent'] != $itemid) {
    //                     $parent = $this->aggregationhints[$itemid]['parent'];
    //                     $itemid = $parent;
    //                 } else {
    //                     // We are at the top of the tree.
    //                     $parent = false;
    //                 }
    //             } while ($parent);

    //             // Finally multiply by the course grademax.
    //             if (!is_null($gradeval)) {
    //                 // Convert to percent.
    //                 $gradeval *= 100;
    //             }

    //             // Now we need to loop through the "built" table data and update the
    //             // contributions column for the current row.
    //             $header_row = "row_{$grade_object->id}_{$this->user->id}";
    //             foreach ($this->tabledata as $key => $row) {
    //                 if (isset($row['itemname']) && ($row['itemname']['id'] == $header_row)) {
    //                     // Found it - update the column.
    //                     $content = '-';
    //                     if (!is_null($gradeval)) {
    //                         $decimals = $grade_object->get_decimals();
    //                         $content = format_float($gradeval, $decimals, true) . ' %';
    //                     }
    //                     $this->tabledata[$key]['contributiontocoursetotal']['content'] = $content;
    //                     break;
    //                 }
    //             }
    //         }
    //     }
    // }

    // /**
    //  * Prints or returns the HTML from the flexitable.
    //  * @param bool $return Whether or not to return the data instead of printing it directly.
    //  * @return string
    //  */
    // public function print_table($return=false) {
    //      $maxspan = $this->maxdepth;

    //     /// Build table structure
    //     $html = "
    //         <table cellspacing='0'
    //                cellpadding='0'
    //                summary='" . s($this->get_lang_string('tablesummary', 'gradereport_culuser')) . "'
    //                class='boxaligncenter generaltable user-grade'>
    //         <thead>
    //             <tr>
    //                 <th id='".$this->tablecolumns[0]."' class=\"header column-{$this->tablecolumns[0]}\" colspan='$maxspan'>".$this->tableheaders[0]."</th>\n";

    //     for ($i = 1; $i < count($this->tableheaders); $i++) {
    //         $html .= "<th id='".$this->tablecolumns[$i]."' class=\"header column-{$this->tablecolumns[$i]}\">".$this->tableheaders[$i]."</th>\n";
    //     }

    //     $html .= "
    //             </tr>
    //         </thead>
    //         <tbody>\n";

    //     /// Print out the table data
    //     for ($i = 0; $i < count($this->tabledata); $i++) {
    //         $html .= "<tr>\n";
    //         if (isset($this->tabledata[$i]['leader'])) {
    //             $rowspan = $this->tabledata[$i]['leader']['rowspan'];
    //             $class = $this->tabledata[$i]['leader']['class'];
    //             $html .= "<td class='$class' rowspan='$rowspan'></td>\n";
    //         }
    //         for ($j = 0; $j < count($this->tablecolumns); $j++) {
    //             $name = $this->tablecolumns[$j];
    //             $class = (isset($this->tabledata[$i][$name]['class'])) ? $this->tabledata[$i][$name]['class'] : '';
    //             $colspan = (isset($this->tabledata[$i][$name]['colspan'])) ? "colspan='".$this->tabledata[$i][$name]['colspan']."'" : '';
    //             $content = (isset($this->tabledata[$i][$name]['content'])) ? $this->tabledata[$i][$name]['content'] : null;
    //             $celltype = (isset($this->tabledata[$i][$name]['celltype'])) ? $this->tabledata[$i][$name]['celltype'] : 'td';
    //             $id = (isset($this->tabledata[$i][$name]['id'])) ? "id='{$this->tabledata[$i][$name]['id']}'" : '';
    //             $headers = (isset($this->tabledata[$i][$name]['headers'])) ? "headers='{$this->tabledata[$i][$name]['headers']}'" : '';
    //             if (isset($content)) {
    //                 $html .= "<$celltype $id $headers class='$class' $colspan>$content</$celltype>\n";
    //             }
    //         }
    //         $html .= "</tr>\n";
    //     }

    //     $html .= "</tbody></table>";

    //     if ($return) {
    //         return $html;
    //     } else {
    //         echo $html;
    //     }
    // }

    // *
    //  * Processes the data sent by the form (grades and feedbacks).
    //  * @var array $data
    //  * @return bool Success or Failure (array of errors).
     
    // function process_data($data) {
    // }
    // function process_action($target, $action) {
    // }

    /**
     * Builds the grade item averages.
     */
    // function calculate_averages() {
    //     global $USER, $DB, $CFG;

    //     if ($this->showaverage) {
    //         // This settings are actually grader report settings (not user report)
    //         // however we're using them as having two separate but identical settings the
    //         // user would have to keep in sync would be annoying.
    //         $averagesdisplaytype   = $this->get_pref('averagesdisplaytype');
    //         $averagesdecimalpoints = $this->get_pref('averagesdecimalpoints');
    //         $meanselection         = $this->get_pref('meanselection');
    //         $shownumberofgrades    = $this->get_pref('shownumberofgrades');

    //         $avghtml = '';
    //         $groupsql = $this->groupsql;
    //         $groupwheresql = $this->groupwheresql;
    //         $totalcount = $this->get_numusers(false);

    //         // We want to query both the current context and parent contexts.
    //         list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

    //         // Limit to users with a gradeable role ie students.
    //         list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

    //         // Limit to users with an active enrolment.
    //         $coursecontext = $this->context->get_course_context(true);
    //         $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
    //         $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
    //         $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);
    //         list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context, '', 0, $showonlyactiveenrol);

    //         $params = array_merge($this->groupwheresql_params, $gradebookrolesparams, $enrolledparams, $relatedctxparams);
    //         $params['courseid'] = $this->courseid;

    //         // find sums of all grade items in course
    //         $sql = "SELECT gg.itemid, SUM(gg.finalgrade) AS sum
    //                   FROM {grade_items} gi
    //                   JOIN {grade_grades} gg ON gg.itemid = gi.id
    //                   JOIN {user} u ON u.id = gg.userid
    //                   JOIN ($enrolledsql) je ON je.id = gg.userid
    //                   JOIN (
    //                                SELECT DISTINCT ra.userid
    //                                  FROM {role_assignments} ra
    //                                 WHERE ra.roleid $gradebookrolessql
    //                                   AND ra.contextid $relatedctxsql
    //                        ) rainner ON rainner.userid = u.id
    //                   $groupsql
    //                  WHERE gi.courseid = :courseid
    //                    AND u.deleted = 0
    //                    AND gg.finalgrade IS NOT NULL
    //                    AND gg.hidden = 0
    //                    $groupwheresql
    //               GROUP BY gg.itemid";

    //         $sum_array = array();
    //         $sums = $DB->get_recordset_sql($sql, $params);
    //         foreach ($sums as $itemid => $csum) {
    //             $sum_array[$itemid] = $csum->sum;
    //         }
    //         $sums->close();

    //         $columncount=0;

    //         // Empty grades must be evaluated as grademin, NOT always 0
    //         // This query returns a count of ungraded grades (NULL finalgrade OR no matching record in grade_grades table)
    //         // No join condition when joining grade_items and user to get a grade item row for every user
    //         // Then left join with grade_grades and look for rows with null final grade (which includes grade items with no grade_grade)
    //         $sql = "SELECT gi.id, COUNT(u.id) AS count
    //                   FROM {grade_items} gi
    //                   JOIN {user} u ON u.deleted = 0
    //                   JOIN ($enrolledsql) je ON je.id = u.id
    //                   JOIN (
    //                            SELECT DISTINCT ra.userid
    //                              FROM {role_assignments} ra
    //                             WHERE ra.roleid $gradebookrolessql
    //                               AND ra.contextid $relatedctxsql
    //                        ) rainner ON rainner.userid = u.id
    //                   LEFT JOIN {grade_grades} gg
    //                          ON (gg.itemid = gi.id AND gg.userid = u.id AND gg.finalgrade IS NOT NULL AND gg.hidden = 0)
    //                   $groupsql
    //                  WHERE gi.courseid = :courseid
    //                        AND gg.finalgrade IS NULL
    //                        $groupwheresql
    //               GROUP BY gi.id";

    //         $ungraded_counts = $DB->get_records_sql($sql, $params);

    //         foreach ($this->gtree->items as $itemid=>$unused) {
    //             if (!empty($this->gtree->items[$itemid]->avg)) {
    //                 continue;
    //             }
    //             $item = $this->gtree->items[$itemid];

    //             if ($item->needsupdate) {
    //                 $avghtml .= '<td class="cell c' . $columncount++.'"><span class="gradingerror">'.get_string('error').'</span></td>';
    //                 continue;
    //             }

    //             if (empty($sum_array[$item->id])) {
    //                 $sum_array[$item->id] = 0;
    //             }

    //             if (empty($ungraded_counts[$itemid])) {
    //                 $ungraded_count = 0;
    //             } else {
    //                 $ungraded_count = $ungraded_counts[$itemid]->count;
    //             }

    //             //do they want the averages to include all grade items
    //             if ($meanselection == GRADE_REPORT_MEAN_GRADED) {
    //                 $mean_count = $totalcount - $ungraded_count;
    //             } else { // Bump up the sum by the number of ungraded items * grademin
    //                 $sum_array[$item->id] += ($ungraded_count * $item->grademin);
    //                 $mean_count = $totalcount;
    //             }

    //             // Determine which display type to use for this average
    //             if (!empty($USER->gradeediting) && $USER->gradeediting[$this->courseid]) {
    //                 $displaytype = GRADE_DISPLAY_TYPE_REAL;

    //             } else if ($averagesdisplaytype == GRADE_REPORT_PREFERENCE_INHERIT) { // no ==0 here, please resave the report and user preferences
    //                 $displaytype = $item->get_displaytype();

    //             } else {
    //                 $displaytype = $averagesdisplaytype;
    //             }

    //             // Override grade_item setting if a display preference (not inherit) was set for the averages
    //             if ($averagesdecimalpoints == GRADE_REPORT_PREFERENCE_INHERIT) {
    //                 $decimalpoints = $item->get_decimals();
    //             } else {
    //                 $decimalpoints = $averagesdecimalpoints;
    //             }

    //             if (empty($sum_array[$item->id]) || $mean_count == 0) {
    //                 $this->gtree->items[$itemid]->avg = '-';
    //             } else {
    //                 $sum = $sum_array[$item->id];
    //                 $avgradeval = $sum/$mean_count;
    //                 $gradehtml = grade_format_gradevalue($avgradeval, $item, true, $displaytype, $decimalpoints);

    //                 $numberofgrades = '';
    //                 if ($shownumberofgrades) {
    //                     $numberofgrades = " ($mean_count)";
    //                 }

    //                 $this->gtree->items[$itemid]->avg = $gradehtml.$numberofgrades;
    //             }
    //         }
    //     }
    // }

    // /**
    //  * Trigger the grade_report_viewed event
    //  *
    //  * @since Moodle 2.9
    //  */
    // public function viewed() {
    //     $event = \gradereport_culuser\event\grade_report_viewed::create(
    //         array(
    //             'context' => $this->context,
    //             'courseid' => $this->courseid,
    //             'relateduserid' => $this->user->id,
    //         )
    //     );
    //     $event->trigger();
    // }
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
