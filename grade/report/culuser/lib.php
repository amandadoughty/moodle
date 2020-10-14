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

//showhiddenitems values
define("GRADE_REPORT_CULUSER_HIDE_HIDDEN", 0);
define("GRADE_REPORT_CULUSER_HIDE_UNTIL", 1);
define("GRADE_REPORT_CULUSER_SHOW_HIDDEN", 2);

define("GRADE_REPORT_CULUSER_VIEW_SELF", 1);
define("GRADE_REPORT_CULUSER_VIEW_USER", 2);

/**
 * Class providing an API for the user report building and displaying.
 * @uses grade_report
 * @package gradereport_culuser
 */
class grade_report_culuser extends grade_report_user {

        /**
     * The user.
     * @var object $user
     */
    public $user;

    /**
     * A flexitable to hold the data.
     * @var object $table
     */
    public $table;

    /**
     * An array of table headers
     * @var array
     */
    public $tableheaders = array();

    /**
     * An array of table columns
     * @var array
     */
    public $tablecolumns = array();

    /**
     * An array containing rows of data for the table.
     * @var type
     */
    public $tabledata = array();

    /**
     * An array containing the grade items data for external usage (web services, ajax, etc...)
     * @var array
     */
    public $gradeitemsdata = array();

    /**
     * The grade tree structure
     * @var grade_tree
     */
    public $gtree;

    /**
     * Flat structure similar to grade tree
     */
    public $gseq;

    /**
     * show student ranks
     */
    public $showrank;

    /**
     * show grade percentages
     */
    public $showpercentage;

    /**
     * Show range
     */
    public $showrange = true;

    /**
     * Show grades in the report, default true
     * @var bool
     */
    public $showgrade = true;

    /**
     * Decimal points to use for values in the report, default 2
     * @var int
     */
    public $decimals = 2;

    /**
     * The number of decimal places to round range to, default 0
     * @var int
     */
    public $rangedecimals = 0;

    /**
     * Show grade feedback in the report, default true
     * @var bool
     */
    public $showfeedback = true;

    /**
     * Show grade weighting in the report, default true.
     * @var bool
     */
    public $showweight = true;

    /**
     * Show letter grades in the report, default false
     * @var bool
     */
    public $showlettergrade = false;

    /**
     * Show the calculated contribution to the course total column.
     * @var bool
     */
    public $showcontributiontocoursetotal = true;

    /**
     * Show average grades in the report, default false.
     * @var false
     */
    public $showaverage = false;

    public $maxdepth;
    public $evenodd;

    public $canviewhidden;

    public $switch;

    /**
     * Show hidden items even when user does not have required cap
     */
    public $showhiddenitems;
    public $showtotalsifcontainhidden;

    public $baseurl;
    public $pbarurl;

    /**
     * The modinfo object to be used.
     *
     * @var course_modinfo
     */
    protected $modinfo = null;

    /**
     * View as user.
     *
     * When this is set to true, the visibility checks, and capability checks will be
     * applied to the user whose grades are being displayed. This is very useful when
     * a mentor/parent is viewing the report of their mentee because they need to have
     * access to the same information, but not more, not less.
     *
     * @var boolean
     */
    protected $viewasuser = false;

    /**
     * An array that collects the aggregationhints for every
     * grade_item. The hints contain grade, grademin, grademax
     * status, weight and parent.
     *
     * @var array
     */
    protected $aggregationhints = array();

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $userid The id of the user
     * @param bool $viewasuser Set this to true when the current user is a mentor/parent of the targetted user.
     */
    public function __construct($courseid, $gpr, $context, $userid, $viewasuser = null) {
        global $DB, $CFG;

        parent::__construct($courseid, $gpr, $context, $userid, $viewasuser);

        $this->showrank        = grade_get_setting($this->courseid, 'report_culuser_showrank', $CFG->grade_report_culuser_showrank);
        $this->showpercentage  = grade_get_setting($this->courseid, 'report_culuser_showpercentage', $CFG->grade_report_culuser_showpercentage);
        $this->showhiddenitems = grade_get_setting($this->courseid, 'report_culuser_showhiddenitems', $CFG->grade_report_culuser_showhiddenitems);
        $this->showtotalsifcontainhidden = array($this->courseid => grade_get_setting($this->courseid, 'report_culuser_showtotalsifcontainhidden', $CFG->grade_report_culuser_showtotalsifcontainhidden));

        $this->showgrade       = grade_get_setting($this->courseid, 'report_culuser_showgrade',       !empty($CFG->grade_report_culuser_showgrade));
        $this->showrange       = grade_get_setting($this->courseid, 'report_culuser_showrange',       !empty($CFG->grade_report_culuser_showrange));
        $this->showfeedback    = grade_get_setting($this->courseid, 'report_culuser_showfeedback',    !empty($CFG->grade_report_culuser_showfeedback));

        $this->showweight = grade_get_setting($this->courseid, 'report_culuser_showweight',
            !empty($CFG->grade_report_culuser_showweight));

        $this->showcontributiontocoursetotal = grade_get_setting($this->courseid, 'report_culuser_showcontributiontocoursetotal',
            !empty($CFG->grade_report_culuser_showcontributiontocoursetotal));

        $this->showlettergrade = grade_get_setting($this->courseid, 'report_culuser_showlettergrade', !empty($CFG->grade_report_culuser_showlettergrade));
        $this->showaverage     = grade_get_setting($this->courseid, 'report_culuser_showaverage',     !empty($CFG->grade_report_culuser_showaverage));

        $this->viewasuser = $viewasuser;

        // The default grade decimals is 2
        $defaultdecimals = 2;
        if (property_exists($CFG, 'grade_decimalpoints')) {
            $defaultdecimals = $CFG->grade_decimalpoints;
        }
        $this->decimals = grade_get_setting($this->courseid, 'decimalpoints', $defaultdecimals);

        // The default range decimals is 0
        $defaultrangedecimals = 0;
        if (property_exists($CFG, 'grade_report_culuser_rangedecimals')) {
            $defaultrangedecimals = $CFG->grade_report_culuser_rangedecimals;
        }
        $this->rangedecimals = grade_get_setting($this->courseid, 'report_culuser_rangedecimals', $defaultrangedecimals);

        $this->switch = grade_get_setting($this->courseid, 'aggregationposition', $CFG->grade_aggregationposition);

        // Grab the grade_tree for this course
        $this->gtree = new grade_tree($this->courseid, false, $this->switch, null, !$CFG->enableoutcomes);

        // Get the user (for full name).
        $this->user = $DB->get_record('user', array('id' => $userid));

        // What user are we viewing this as?
        $coursecontext = context_course::instance($this->courseid);
        if ($viewasuser) {
            $this->modinfo = new course_modinfo($this->course, $this->user->id);
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext, $this->user->id);
        } else {
            $this->modinfo = $this->gtree->modinfo;
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext);
        }

        // Determine the number of rows and indentation.
        $this->maxdepth = 1;
        $this->inject_rowspans($this->gtree->top_element);
        $this->maxdepth++; // Need to account for the lead column that spans all children.
        for ($i = 1; $i <= $this->maxdepth; $i++) {
            $this->evenodd[$i] = 0;
        }

        $this->tabledata = array();

        // base url for sorting by first/last name
        $this->baseurl = $CFG->wwwroot.'/grade/report?id='.$courseid.'&amp;userid='.$userid;
        $this->pbarurl = $this->baseurl;

        // no groups on this report - rank is from all course users
        $this->setup_table();

        //optionally calculate grade item averages
        $this->calculate_averages();
    }

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
                $this->showhiddenitems == GRADE_REPORT_CULUSER_HIDE_HIDDEN ||
                ($this->showhiddenitems == GRADE_REPORT_CULUSER_HIDE_UNTIL && !$grade_object->is_hiddenuntil()))) {
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
                    $this->showhiddenitems == GRADE_REPORT_CULUSER_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_CULUSER_HIDE_UNTIL && !$grade_grade->is_hiddenuntil()))) {
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
                $canviewall = has_capability('moodle/grade:viewall', $this->context);
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
                $gradeitemdata['itemname'] = $grade_object->itemname;
                $gradeitemdata['itemtype'] = $grade_object->itemtype;
                $gradeitemdata['itemmodule'] = $grade_object->itemmodule;
                $gradeitemdata['iteminstance'] = $grade_object->iteminstance;
                $gradeitemdata['itemnumber'] = $grade_object->itemnumber;
                $gradeitemdata['categoryid'] = $grade_object->categoryid;
                $gradeitemdata['outcomeid'] = $grade_object->outcomeid;
                $gradeitemdata['scaleid'] = $grade_object->outcomeid;
                $gradeitemdata['locked'] = $canviewall ? $grade_grade->grade_item->is_locked() : null;

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
                    $gradeitemdata['graderaw'] = null;
                    $gradeitemdata['gradehiddenbydate'] = false;
                    $gradeitemdata['gradeneedsupdate'] = $grade_grade->grade_item->needsupdate;
                    $gradeitemdata['gradeishidden'] = $grade_grade->is_hidden();
                    $gradeitemdata['gradedatesubmitted'] = $grade_grade->get_datesubmitted();
                    $gradeitemdata['gradedategraded'] = $grade_grade->get_dategraded();
                    $gradeitemdata['gradeislocked'] = $canviewall ? $grade_grade->is_locked() : null;
                    $gradeitemdata['gradeisoverridden'] = $canviewall ? $grade_grade->is_overridden() : null;

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

                    if ($grade_grade->feedback) {
                        $grade_grade->feedback = file_rewrite_pluginfile_urls(
                            $grade_grade->feedback,
                            'pluginfile.php',
                            $grade_grade->get_context()->id,
                            GRADE_FILE_COMPONENT,
                            GRADE_FEEDBACK_FILEAREA,
                            $grade_grade->id
                        );
                    }

                    if ($grade_grade->overridden > 0 AND ($type == 'categoryitem' OR $type == 'courseitem')) {
                        $data['feedback']['class'] = $classfeedback . ' feedbacktext';
                        $data['feedback']['content'] = get_string('overridden', 'grades') .
                            ': ' .
                            format_text(
                                $grade_grade->feedback,
                                $grade_grade->feedbackformat,
                                ['context' => $grade_grade->get_context()]
                            );
                            
                        $gradeitemdata['feedback'] = $grade_grade->feedback;
                    } else if (!$this->canviewhidden and $grade_grade->is_hidden()) {
                        $data['feedback']['class'] = $classfeedback . ' feedbacktext';
                        $data['feedback']['content'] = '&nbsp;';
                    } else {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';

                        if (empty($grade_grade->feedback)) {
                                $data['feedback']['content'] = '';
                        } else {
                            $data['feedback']['content'] = format_text($grade_grade->feedback, $grade_grade->feedbackformat,
                                ['context' => $grade_grade->get_context()]);
                            $gradeitemdata['feedback'] = $grade_grade->feedback;
                        }
                    
                        // At this point $data['feedback']['content'] will contain the feedback or an empty string.
                        // Now we check if there is a feedback function for this module.
                        $feedbackfunction = 'get_' . $grade_object->itemmodule . '_feedback';

                        if (method_exists($this, $feedbackfunction)){
                            $this->{$feedbackfunction}($data, $grade_object);
                        } else {
                            $this->get_mod_feedback($data, $grade_object);
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

    /**
     * Entry function to collect all the types of feedback for Assignment
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_assign_feedback(&$data, $grade_object) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/mod/assign/locallib.php');

        // We retrieve all the assign modules in the course.
        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        // Now we use the iteminstance to retrieve the assignment module for this grade.
        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $course = get_course($this->courseid);
            $assign = new assign($context, $cm, $course);

            $grade = $assign->get_user_grade($this->user->id, false);
            $gradingstatus = $assign->get_grading_status($this->user->id);
            $gradinginfo = grade_get_grades($this->courseid, 'mod', 'assign', $cm->instance, $this->user->id);

            $gradingitem = null;
            $gradebookgrade = null;

            if ($gradingitem = reset($gradinginfo->items)) {
                // $gradingitem = $gradinginfo->items[0];
                $gradebookgrade = $gradingitem->grades[$this->user->id];
            }

            // Check to see if all feedback plugins are empty.
            $emptyplugins = true;
            $feedbackplugins = $assign->get_feedback_plugins();

            if ($grade) {
                foreach ($feedbackplugins as $plugin) {
                    if ($plugin->is_visible() && $plugin->is_enabled()) {
                        if (!$plugin->is_empty($grade)) {
                            $emptyplugins = false;
                        }
                    }
                }
            }

            if ($assign->get_instance()->markingworkflow && $gradingstatus != ASSIGN_MARKING_WORKFLOW_STATE_RELEASED) {
                $emptyplugins = true; // Don't show feedback plugins until released either.
            } 

            $cangrade = has_capability('mod/assign:grade', $assign->get_context());

            $hasgrade = $assign->get_instance()->grade != GRADE_TYPE_NONE &&
                            !is_null($gradebookgrade) && !is_null($gradebookgrade->grade);

            $gradevisible = $cangrade || 
                $assign->get_instance()->grade == GRADE_TYPE_NONE ||
                (
                    !is_null($gradebookgrade) && 
                    !$gradebookgrade->hidden 
                    // && 
                    // !$assign->is_blind_marking()
                );

            // If there is a visible grade, show the summary.
            if (($hasgrade || !$emptyplugins) && $gradevisible) {
                $renderer = $assign->get_renderer();
                $config = get_config('assign');
                // Get the feedback plugin that is set to push comments to the gradebook. This is what populates
                // $grade_grade->feedback unless it is overridden.
                $gradebookfeedbacktype = str_replace('assignfeedback_', '', $config->feedback_plugin_for_gradebook);

                foreach($feedbackplugins as $feedbackplugin) {
                    if (
                        $feedbackplugin->is_enabled() &&
                        $feedbackplugin->is_visible() &&
                        $feedbackplugin->has_user_summary()                                   
                    ){
                        $feedbacksubtitle = '<p class="feedbackpluginname">' . $feedbackplugin->get_name() . '</p>';

                        // Add the title of the default feedback type if the feedback is not empty.
                        if ($feedbackplugin->get_type() == $gradebookfeedbacktype) {
                            if ($data['feedback']['content']) {
                                $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
                            }
                        // Use the plugin function to output the feedback.
                        } elseif ($grade && !$feedbackplugin->is_empty($grade)) {
                            if($feedbackplugin->get_name() == 'Feedback files') {
                                // Feedback files. We use our own funtion to format these as the 
                                // plugin produces verbose html.
                                if($files = $this->assign_get_feedback_files($grade, $context)) {
                                    $filefeedback = $this->get_formatted_feedback_files($files);
        
                                    $data['feedback']['content'] .= $feedbacksubtitle .= $filefeedback;
                                }
                            } else {
                                $data['feedback']['content'] .= $feedbacksubtitle;
                                $data['feedback']['content'] .= $feedbackplugin->view($grade);
                            }
                        }
                    }
                }
            
                if($hasgrade) {
                    // Get grading form feedback (marking guide or rubric).
                    $gradingmanager = get_grading_manager($assign->get_context(), 'mod_assign', 'submissions');

                    $activemethod = $gradingmanager->get_active_method();
                    $feedbackfn = 'assign_get_gradingform_' . $activemethod . '_feedback';
                    $fnexists = method_exists($this, $feedbackfn);

                    if($activemethod && $fnexists) {
                        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string($activemethod, 'gradingform_' . $activemethod) . '</p>';
                        $gradingformtext = $this->$feedbackfn($grade, $gradingmanager);

                        if ($gradingformtext) {
                            $data['feedback']['content'] .= $feedbacksubtitle .= $gradingformtext;
                        }
                    }
                }
            }
        }
    }

    /**
     * Function to format the comment feedback for mods that have no other types of
     * collectable feedback.
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_mod_feedback(&$data, $grade_object) {
        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

        if ($data['feedback']['content']) {
            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
        }        
    }

    /**
     * Entry function to collect all the types of feedback for Peerassessment
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_peerassessment_feedback(&$data, $grade_object) {
        // NB Overriding grades and feedback in the gradebbok shows changes in gradebook but not in
        // Peerassessment.
        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

        if ($data['feedback']['content']) {
            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
        }

        // Feedback files.
        if($files = $this->peerassesment_get_feedback_files($grade_object)) {
            $filefeedback = $this->get_formatted_feedback_files($files);
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('files', 'gradereport_culuser') . '</p>';
            $data['feedback']['content'] .= $feedbacksubtitle .= $filefeedback;
        }

        // Peer grades.
        // Looks like they can't see these. They just see the grades and feedback they gave others.
    }

    /**
     * Entry function to collect all the types of feedback for Peerwork
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_peerwork_feedback(&$data, $grade_object) {
        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

        if ($data['feedback']['content']) {
            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
        }

        // Feedback files.
        if($files = $this->peerwork_get_feedback_files($grade_object)) {
            $filefeedback = $this->get_formatted_feedback_files($files);
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('files', 'gradereport_culuser') . '</p>';
            $data['feedback']['content'] .= $feedbacksubtitle .= $filefeedback;
        }
    }    

    /**
     * Entry function to collect all the types of feedback for Workshop
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_turnitintooltwo_feedback(&$data, $grade_object) {
        global $DB, $CFG, $PAGE;

        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('comments', 'gradereport_culuser') . '</p>';

        if ($data['feedback']['content']) {
            $data['feedback']['content'] = $feedbacksubtitle .= $data['feedback']['content'];
        }

        // We retrieve all the tii modules in the course.
        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        // Now we use the iteminstance to retrieve the tii module for this grade.
        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $course = get_course($this->courseid);

            $params = array(
                'id' => $grade_object->iteminstance
                );

            $tiirecord = $DB->get_record('turnitintooltwo', $params, '*');

            $params = array(
                'turnitintooltwoid' => $grade_object->iteminstance,  
                'userid' => $this->user->id
                );

            $submissions = $DB->get_records('turnitintooltwo_submissions', $params);
            $grademarkfeedback = '';         

            // Link the submission to the plagiarism comparison report.
            // If grademark marking is enabled.
            if ($tiirecord->usegrademark == 1 && $submissions) {
                foreach($submissions as $submission) {
                    // Link the submission to the gradebook.
                    $grademarkfeedback .= "<a href=\"" . 
                        $CFG->wwwroot . 
                        "/mod/turnitintooltwo/view.php?id=" .
                        $cm->id . 
                        "&viewcontext=box&do=grademark&submissionid=" . 
                        $submission->submission_objectid .
                        "\" target=\"_blank\">" . 
                        $submission->submission_title .
                        "</a>";                    
                }
            }

            if($grademarkfeedback){
                $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('grademark', 'gradereport_culuser') . '</p>';

                $data['feedback']['content'] .= $feedbacksubtitle .= $grademarkfeedback;
            }
        }
    }

    /**
     * Entry function to collect all the types of feedback for Asssignment
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_workshop_feedback(&$data, $grade_object) {
        global $DB, $CFG, $PAGE;

        require_once($CFG->dirroot . '/mod/workshop/locallib.php');

        // We retrieve all the workshop modules in the course.
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

                    foreach($assessments as $assessment) {
                        $assessment = $workshop->prepare_assessment($assessment, null);
                        // Overall peer feedback.
                        $overallfeedback .= $this->workshop_get_overall_feedback($assessment);
                        // Strategy feedback.
                        $diminfo = $strategy->get_dimensions_info();
                        $strategyfunctionname = 'workshop_get_' . $strategyname . '_feedback';

                        if(method_exists($this, $strategyfunctionname)) {
                            $feedback = $this->$strategyfunctionname($assessment, $diminfo);
                            $strategyfeedback .= $feedback;
                        }

                        // File feedback.
                        if($files = $this->workshop_get_feedback_files($assessment, $context)) {
                            $filefeedback = $this->get_formatted_feedback_files($files);
                        }
                    }

                    // Put it all together.
                    $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('peerfeedback', 'gradereport_culuser') . '</p>';
                    $data['feedback']['content'] .= $feedbacksubtitle . $overallfeedback;
                    $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string($strategyname . 'title', 'gradereport_culuser') . '</p>';
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
     * Entry function to collect all the types of feedback for Forum
     * 
     * @param array $data
     * @param stdClass $grade_object
     */
    protected function get_forum_feedback(&$data, $grade_object) {
        global $DB, $CFG;

        // We retrieve all the forum modules in the course.
        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        // Now we use the iteminstance to retrieve the forum module for this grade.
        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $course = get_course($this->courseid);
            $forumgradeitem = mod_forum\grades\forum_gradeitem::load_from_context($context);

            // Get all the factories that are required.
            $vaultfactory = mod_forum\local\container::get_vault_factory();
            $forumvault = $vaultfactory->get_forum_vault();
            $forum = $forumvault->get_from_course_module_id((int) $context->instanceid);

            $params = [
                'forum' => $forum->get_id(),
                'itemnumber' => $forumgradeitem->get_grade_itemid(),
                'userid' => $this->user->id,
            ];

            $grade = $DB->get_record('forum_grades', $params);
            $gradinginfo = grade_get_grades($this->courseid, 'mod', 'forum', $cm->instance, $this->user->id);

            $gradingitem = null;
            $gradebookgrade = null;

            if ($gradingitem = reset($gradinginfo->items)) {
                $gradebookgrade = $gradingitem->grades[$this->user->id];
            }

            $cangrade = has_capability('mod/forum:grade', $forum->get_context());

            $hasgrade = $forumgradeitem->user_has_grade($this->user) &&
                !is_null($gradebookgrade) &&
                !is_null($gradebookgrade->grade);


            $gradevisible = $cangrade || 
                $forum->is_grading_enabled() ||
                (
                    !is_null($gradebookgrade) && 
                    !$gradebookgrade->hidden
                );

            // If there is a visible grade, show the summary.
            if ($hasgrade && $gradevisible) {
                if($hasgrade) {
                    // Get grading form feedback (marking guide or rubric).
                    $gradingmanager = get_grading_manager($context, 'mod_forum', 'forum');

                    $activemethod = $gradingmanager->get_active_method();
                    $feedbackfn = 'assign_get_gradingform_' . $activemethod . '_feedback';
                    $fnexists = method_exists($this, $feedbackfn);

                    if($activemethod && $fnexists) {
                        $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string($activemethod, 'gradingform_' . $activemethod) . '</p>';
                        $gradingformtext = $this->$feedbackfn($grade, $gradingmanager);

                        if ($gradingformtext) {
                            $data['feedback']['content'] .= $feedbacksubtitle .= $gradingformtext;
                        }
                    }
                }
            }
        }
    }    

    // Grading form functions

    /**
     * Get the Grading Form Marking Guide feedback
     * 
     * @param stdClass The grade record
     * @param obj grading_manager
     * @return str the text for the feedback
     */
    public function assign_get_gradingform_guide_feedback($grade, $gradingmanager) {
        global $DB;

        $controller = $gradingmanager->get_active_controller();
        $criteria = $controller->get_definition()->guide_criteria;
        $instances = $controller->get_active_instances($grade->id);
        $out = '';

        foreach ($instances as $instance) {
            $remarks = $instance->get_guide_filling()['criteria'];

            foreach ($remarks as $remark) {
                $criterionid = $remark['criterionid'];
                $shortname = $criteria[$criterionid]['shortname'];
                $remark = $remark['remark'];
                $out .= "<strong>" . $shortname . ": </strong>" . $remark . "<br/>";
            }
        }

        return $out;
    }

    /**
     * Get the Grading Form Rubric feedback
     * 
     * @param stdClass The grade record
     * @param obj grading_manager
     * @return str the text for the feedback
     */
    public function assign_get_gradingform_rubric_feedback($grade, $gradingmanager) {
        global $DB;

        $controller = $gradingmanager->get_active_controller();
        $criteria = $controller->get_definition()->rubric_criteria;
        $instances = $controller->get_active_instances($grade->id);
        $out = '';

        foreach ($instances as $instance) {
            $values = $instance->get_rubric_filling()['criteria'];

            foreach ($values as $value) {
                $criterionid = $value['criterionid'];
                $description = $criteria[$criterionid]['description'];
                $levelid = $value['levelid'];
                $definition = $criteria[$criterionid]['levels'][$levelid]['definition'];
                $remark = $value['remark'];
                $out .= "<strong>" . $description . ": </strong>" . $definition . "<br/>" . $remark . "<br/>";
            }
        }

        return $out;
    }

    /**
     * Gets the assign feedback files.
     *
     * @param stdClass $grade
     * @param context $context
     * 
     * @return array
     */
    public function assign_get_feedback_files($grade, $context) { // @TODO get all fileslike this.
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'assignfeedback_file', 'feedback_files', $grade->id, 'id', false);

        return $files;
    }     


    // Peerassessment functions

    /**
     * Gets the peerassessment feedback files.
     *
     * @param stdClass $grade_object
     * 
     * @return array
     */
    public function peerassesment_get_feedback_files($grade_object) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/lib/grouplib.php');

        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        $files = [];

        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $peerassessment = $DB->get_record('peerassessment', array('id' => $grade_object->iteminstance));
            $groupingid = $peerassessment->submissiongroupingid;

            try {
                $groups = groups_get_all_groups($this->courseid, $this->user->id, $groupingid);

                if(count($groups) == 1) {
                    $group = array_shift($groups);
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($context->id, 'mod_peerassessment', 'feedback_files', $group->id, 'sortorder', false);
                }
            } catch(Exception $e) {
                // Do nothing.
            }
        }        

        return $files;
    }

    // Peerwork functions

    /**
     * Gets the peerwork feedback files.
     *
     * @param stdClass $grade_object
     * 
     * @return array
     */
    public function peerwork_get_feedback_files($grade_object) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/lib/grouplib.php');

        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
        $files = [];

        if (!empty($instances[$grade_object->iteminstance])) {
            $cm = $instances[$grade_object->iteminstance];                              
            $context = context_module::instance($cm->id);
            $peerwork = $DB->get_record('peerwork', array('id' => $grade_object->iteminstance));
            // $groupingid = $peerwork->submissiongroupingid;
            $groupingid = $cm->groupingid;

            try {
                $groups = groups_get_all_groups($this->courseid, $this->user->id, $groupingid);

                if(count($groups) == 1) {
                    $group = array_shift($groups);
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($context->id, 'mod_peerwork', 'feedback_files', $group->id, 'sortorder', false);
                }
            } catch(Exception $e) {
                // Do nothing.
            }
        }        

        return $files;
    }     

    // Turnitin functions

    // Workshop functions

    /**
     * Returns accumulative feedback
     *
     * @param workshop_assessment $assessment
     * @param array $diminfo
     * 
     * @return string $feedback
     */
    public function workshop_get_accumulative_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        $feedbacktitle  = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT wg.dimensionid, ws.description, ws.grade, wg.grade as score, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_accumulative} ws
                ON wg.dimensionid = ws.id
                WHERE wg.assessmentid = :assessmentid AND wg.strategy = :strategy AND wg.dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'accumulative'); // 
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            foreach($records as $record) {
                if($record->description && $record->score) {
                    $feedback .= '<p><b>' . strip_tags($record->description) . '</b>: ' . get_string('grade', 'gradereport_culuser') . round($record->score) . '/' . round($record->grade);
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '</p>';
                }
            }
        }

        return $feedback;
    } 
    
    /**
     * Returns comments feedback
     *
     * @param workshop_assessment $assessment
     * @param array $diminfo
     * 
     * @return string $feedback
     */
    public function workshop_get_comments_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT wg.dimensionid, ws.description, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_comments} ws
                ON wg.dimensionid = ws.id 
                WHERE wg.assessmentid = :assessmentid AND wg.strategy = :strategy AND wg.dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'comments');
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            foreach($records as $record) {
                if($record->description) {
                    $feedback .= '<p><b>' . strip_tags($record->description)  . '</b>';
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '</p>';
                }
            }
        }
        return $feedback;
    }

    /**
     * Returns numerrors feedback
     *
     * @param workshop_assessment $assessment
     * @param array $diminfo
     * 
     * @return string $feedback
     */
    public function workshop_get_numerrors_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT wg.dimensionid, ws.description, ws.grade0, ws.grade1, wg.grade, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_numerrors} ws
                ON wg.dimensionid = ws.id
                WHERE wg.assessmentid = :assessmentid AND wg.strategy = :strategy AND wg.dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'numerrors');
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            foreach($records as $record) {
                if($record->grade) {
                    $feedback .= '<p><b>' . strip_tags($record->description) . '</b>: ' . ($record->grade < 1.0 ? strip_tags($record->grade0) : strip_tags($record->grade1));
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '</p>';
                }
            }
        }

        return $feedback;
    }

    /**
     * Returns rubric feedback
     *
     * @param workshop_assessment $assessment
     * @param array $diminfo
     * 
     * @return string $feedback
     */
    public function workshop_get_rubric_feedback($assessment, $diminfo) {
        global $DB;

        if (empty($diminfo)) {
            return array();
        }

        $feedback = '';
        list($dimsql, $dimparams) = $DB->get_in_or_equal(array_keys($diminfo), SQL_PARAMS_NAMED);
        // beware! the caller may rely on the returned array is indexed by dimensionid

        $sql = "SELECT wg.dimensionid, ws.description, rl.definition, wg.peercomment
                FROM {workshop_grades} wg
                LEFT JOIN {workshopform_rubric} ws
                ON wg.dimensionid = ws.id
                LEFT JOIN {workshopform_rubric_levels} rl 
                ON rl.dimensionid = ws.id
                AND rl.grade = wg.grade                                
                WHERE wg.assessmentid = :assessmentid AND wg.strategy = :strategy AND wg.dimensionid $dimsql";
        $params = array('assessmentid' => $assessment->id, 'strategy' => 'rubric');
        $params = array_merge($params, $dimparams);

        if($records = $DB->get_records_sql($sql, $params)) {
            foreach($records as $record) {
                if($record->description && $record->definition) {
                    $feedback .= '<p><b>' . strip_tags($record->description) . '</b>: ' . strip_tags($record->definition) ;
                    $feedback .= '<br/><b>' . get_string('comment', 'gradereport_culuser') . '</b>: ' . strip_tags($record->peercomment) . '<p/';
                }
            }
        }

        return $feedback;
    }

    /**
     * Renders the overall feedback for the author of the submission
     *
     * @param workshop_assessment $assessment
     * 
     * @return string $o
     */
    public function workshop_get_overall_feedback(workshop_assessment $assessment) {
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
     * Gets the workshop feedback files.
     *
     * @param workshop_assessment $assessment
     * @param context $context
     * 
     * @return array
     */
    public function workshop_get_feedback_files($assessment, $context) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_workshop', 'overallfeedback_attachment', $assessment->id);

        return $files;
    }     


    // Generic functions

   /**
     * Renders the html to display file feedback.
     *
     * @param array $files
     * 
     * @return string HTML
     */
    public function get_formatted_feedback_files($files) {
        global $OUTPUT;

        $o ='';        
        $imagehtml = '';
        $filehtml = '';
        $o .= $OUTPUT->container_start('attachments');

        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $icon = $OUTPUT->pix_icon(
                file_file_icon($file), 
                get_mimetype_description($file),
                'moodle', 
                array('class' => 'icon')
                );

            $fileurl = moodle_url::make_pluginfile_url(
                $file->get_contextid(), 
                $file->get_component(), 
                $file->get_filearea(), 
                $file->get_itemid(), 
                $file->get_filepath(), 
                $file->get_filename()
                );

            $previewurl = new moodle_url(
                moodle_url::make_pluginfile_url(
                    $file->get_contextid(), 
                    $file->get_component(), 
                    $file->get_filearea(), 
                    $file->get_itemid(), 
                    $file->get_filepath(), 
                    $file->get_filename(),
                    false
                    ), 
                array('preview' => 'bigthumb')
                );

            $link = html_writer::link($fileurl, $icon . ' ' . substr($file->get_filepath() . $file->get_filename(), 1));

            if (file_mimetype_in_typegroup($file->get_mimetype(), 'web_image')) {
                $preview = html_writer::empty_tag('img', array('src' => $previewurl, 'alt' => '', 'class' => 'preview'));
                $preview = html_writer::tag('a', $preview, array('href' => $fileurl));
                $imagehtml .= $OUTPUT->container($preview);
            } else {
                $filehtml .= html_writer::tag('li', $link, array('class' => $file->get_mimetype()));
            }
        }

        if ($imagehtml) {
            $imagehtml = $OUTPUT->container($imagehtml, 'images');
        }

        if ($filehtml) {
            $filehtml = html_writer::tag('ul', $filehtml, array('class' => 'ygtvlnfiles'));
        }

        $o .= $imagehtml . $filehtml;
        $o .= $OUTPUT->container_end();

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
        $report = new grade_report_culuser($course->id, $gpr, $context, $user->id, $viewasuser);

        // print the page
        echo '<div class="grade-report-user">'; // css fix to share styles with real report page
        if ($report->fill_table()) {
            echo $report->print_table(true);
        }
        echo '</div>';
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 */
function gradereport_culuser_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {

}
