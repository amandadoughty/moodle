<?php
// This file is part of The Bootstrap 3 Moodle theme
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
 * This file contains a renderer for the assignment class
 *
 * @package    theme_cul_boost
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/renderer.php');

/**
 * A custom renderer class that extends the plugin_renderer_base and is used by the assign module.
 *
 * @package theme_cul_boost
 */
class theme_cul_boost_mod_assign_renderer extends mod_assign_renderer {

    /**
     * Render a course index summary
     *
     * @param assign_course_index_summary $indexsummary
     * @return string
     */
    public function render_assign_course_index_summary(assign_course_index_summary $indexsummary) {
        global $COURSE, $USER;

        $o = '';

        $strplural = get_string('modulenameplural', 'assign');
        $strsectionname  = $indexsummary->courseformatname;
        $strduedate = get_string('duedate', 'assign');
        $strsubmission = get_string('submissionstatus', 'assign');
        $strgrade = get_string('grade');
        $strfeedback = get_string('feedback');
        $strsubmitted = get_string('numberofsubmittedassignments', 'assign');

        $table = new html_table();
        $table->data = [];
        $currentsection = '';

        foreach ($indexsummary->assignments as $info) {            
            $context = context_module::instance($info['cmid']);
            $course = get_course($COURSE->id);
            $modinfo = get_fast_modinfo($COURSE);
            $cm = $modinfo->get_cm($info['cmid']);
            $assign = new assign($context, $cm, $COURSE);
            $params = ['id' => $info['cmid']];
            $link = html_writer::link(new moodle_url('/mod/assign/view.php', $params),
                                      $info['cmname']);
            $due = $info['timedue'] ? userdate($info['timedue']) : '-';
            $printsection = '';

            if ($indexsummary->usesections) {
                if ($info['sectionname'] !== $currentsection) {
                    if ($info['sectionname']) {
                        $printsection = $info['sectionname'];
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $info['sectionname'];
                }
            }

            if (has_capability('mod/assign:grade', $context)) {
                $table->head = [$strplural, $strduedate, $strsubmitted];
                $table->align = ['left', 'left', 'right'];
                // $submitted = $info['submissionssubmittedcount'];
                $submitted = $assign->count_submissions_with_status(ASSIGN_SUBMISSION_STATUS_SUBMITTED);
                $row = [$link, $due, $submitted];
            } else {
                $table->head = [$strplural, $strduedate, $strsubmission, $strgrade, $strfeedback];
                $table->align = ['left', 'left', 'center', 'right', 'left'];                
                $submission = null;
                $params = array('assignment'=>$assign->get_instance()->id, 'userid'=>$USER->id);

                if ($assign->get_instance()->teamsubmission) {
                    $submission = $assign->get_group_submission($USER->id, 0, true, -1);
                } else {
                    $submission = $assign->get_user_submission($USER->id, true, -1);
                }
                // This is always set to the user submission status.
                $info['submissioninfo'] = get_string('submissionstatus_' . $submission->status, 'assign');
                $feedback = $this->getAssignFeedback($cm, $assign, $context);
                $row = [$link, $due, $info['submissioninfo'], $info['gradeinfo'], $feedback];
            }

            if ($indexsummary->usesections) {
                array_unshift($table->head, $strsectionname);
                array_unshift($table->align, 'left');
                array_unshift($row, $printsection);
            }

            $table->data[] = $row;
        }

        $o .= html_writer::table($table);

        return $o;
    }

    /**
     * Entry function to collect all the types of feedback for Assignment
     * 
     * @param int $cmid
     */
    protected function getAssignFeedback($cm, $assign, $context) {
        global $DB, $CFG, $COURSE, $USER;

        require_once($CFG->libdir . '/gradelib.php');

        $gradinginfo = grade_get_grades($COURSE->id, 'mod', 'assign', $cm->instance, $USER->id);        

        if (isset($gradinginfo->items[0]->grades[$USER->id]) &&
                !$gradinginfo->items[0]->grades[$USER->id]->hidden && !$assign->is_blind_marking()) {
        
            $feedbackplugins = $assign->get_feedback_plugins();
            $renderer = $assign->get_renderer();
            $config = get_config('assign');
            // Get the feedback plugin that is set to push comments to the gradebook. This is what populates
            // $grade_grade->feedback unless it is overridden.
            $gradebookfeedbacktype = str_replace('assignfeedback_', '', $config->feedback_plugin_for_gradebook);
            // We need a stdClass with an id property from assign_grades that is for this $grade_object. 
            // It is needed to test $feedbackplugin->is_empty() for the remaining assignfeedback plugins.
            $params = array(
                'assignment' => $assign->get_instance()->id,  
                'userid' => $USER->id
                );

            $grades = $DB->get_records('assign_grades', $params, 'attemptnumber DESC');
            $grade = array_pop($grades);
            $feedback = '';
            $gradebookfeedback = '';
            $assignfeedback = '';

            // Get gradebook feedback.
            $sql = "SELECT gg.*, gi.itemmodule, gi.iteminstance
                      FROM {grade_grades} gg
                      JOIN {grade_items} gi ON gi.id = gg.itemid
                     WHERE gi.itemmodule = 'assign'
                     AND gi.iteminstance = :assignid
                     AND gg.userid = :userid";

            $params = array('userid' => $USER->id, 'assignid' => $assign->get_instance()->id);
   ;
            if($recordset = $DB->get_records_sql($sql, $params)) {
                $record = array_pop($recordset);
                $gradebookfeedback = $record->feedback;
            }      

            foreach($feedbackplugins as $feedbackplugin) {
                if (
                    $feedbackplugin->is_enabled() &&
                    $feedbackplugin->is_visible() &&
                    $feedbackplugin->has_user_summary()                                   
                ){
                    $feedbacksubtitle = '<p class="feedbackpluginname">' . $feedbackplugin->get_name() . '</p>';
 
                    // Use the plugin function to output the feedback.
                    if($feedbackplugin->get_type() == $gradebookfeedbacktype) {                                               
                        $rawgradebookfeedback = strip_tags($gradebookfeedback);

                        if($rawgradebookfeedback) {
                            $feedback .= $feedbacksubtitle;

                            $gradebookfeedback = file_rewrite_pluginfile_urls(
                                $gradebookfeedback,
                                'pluginfile.php',
                                $context->id,
                                GRADE_FILE_COMPONENT,
                                GRADE_FEEDBACK_FILEAREA,
                                $record->id
                            );

                            $gradebookfeedback = format_text(
                                $gradebookfeedback,
                                FORMAT_HTML,
                                [
                                    'context' => $assign->get_context()
                                ]
                            );
                            
                            $feedback .= $gradebookfeedback;
                        } elseif($grade) {
                            $feedback .= $feedbackplugin->view($grade);
                        }
                    // Use the plugin function to output the feedback.
                    } elseif($grade && !$feedbackplugin->is_empty($grade)) {
                        if($feedbackplugin->get_name() == 'Feedback files') {
                                // Feedback files. We use our own funtion to format these as the 
                                // plugin produces verbose html.
                                if($files = $this->assign_get_feedback_files($grade, $context)) {
                                    $filefeedback = $this->get_formatted_feedback_files($files);                                
                                    $feedback .= $feedbacksubtitle .= $filefeedback;
                                }
                        } else {                            
                            $feedback .= $feedbacksubtitle;
                            $feedback .= $feedbackplugin->view($grade);
                        }
                    }                   
                }
            }

            // Get any rubric feedback.
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('rubric', 'theme_cul_boost') . '</p>';
            $rubrictext = $this->assign_get_rubric_feedback($USER->id, $COURSE->id, $assign->get_instance()->id, 'assign');

            if ($rubrictext) {
                $feedback .= $feedbacksubtitle .= $rubrictext;
            }

            // Get any marking guide feedback.
            $feedbacksubtitle = '<p class="feedbackpluginname">' . get_string('markingguide', 'theme_cul_boost') . '</p>';
            $rubrictext = $this->assign_get_marking_guide_feedback($USER->id, $COURSE->id, $assign->get_instance()->id, 'assign');

            if ($rubrictext) {
                $feedback .= $feedbacksubtitle .= $rubrictext;
            }

            // the assignment way? assign locallib #5240
            // If there is a visible grade, show the feedback.
            // $feedbackstatus = $assign->get_assign_feedback_status_renderable($user);
            // if ($feedbackstatus) {
            //     $feedback .= $assign->get_renderer()->render($feedbackstatus);
            // }




        } else {
            $feedback = '-';
        }  

        return $feedback;
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
    public function assign_get_rubric_feedback($userid, $courseid, $iteminstance, $itemmodule) {
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
    public function assign_get_marking_guide_feedback($userid, $courseid, $iteminstance, $itemmodule) {
        global $DB;

        $sql = "SELECT DISTINCT gc.shortname,gf.remark 
            FROM {gradingform_guide_criteria} gc
            JOIN {gradingform_guide_fillings} gf
            ON gc.id = gf.criterionid
            JOIN (
                SELECT gf.criterionid, max(gf.instanceid) instanceid
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
                AND gi.courseid = ? AND gg.userid = ? AND gi.iteminstance = ?
                GROUP BY gf.criterionid
            ) q
            ON gf.criterionid = q.criterionid AND gf.instanceid = q.instanceid";

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