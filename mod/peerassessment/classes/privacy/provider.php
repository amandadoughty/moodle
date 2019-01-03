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
 * Privacy Subsystem implementation for mod_peerassessment.
 *
 * @package    mod_peerassessment
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_peerassessment\privacy;

use \core_privacy\local\request\userlist;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\approved_userlist;
use \core_privacy\local\request\deletion_criteria;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\helper;
use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\transform;

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of the privacy subsystem plugin provider for the peerassessment activity module.
 *
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    // This plugin has data.
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,

    // This plugin is capable of determining which users have data within it.
    \core_privacy\local\request\core_userlist_provider
{

    // use subcontext_info;

    /**
     * Returns meta data about this system.
     *
     * @param   collection     $items The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items) : collection {
        // The 'peerassessment' table does not store any specific user data.

        // The 'peerassessment_submission' table contains information about user submissons.
        $items->add_database_table(
            'peerassessment_submission',
             [
                'userid' => 'privacy:metadata:peerassessment_submission:userid',
                'assignment' => 'privacy:metadata:peerassessment_submission:assignment',
                'timecreated' => 'privacy:metadata:peerassessment_submission:timecreated',
                'timemodified' => 'privacy:metadata:peerassessment_submission:timemodified',
                'status' => 'privacy:metadata:peerassessment_submission:status',
                'groupid' => 'privacy:metadata:peerassessment_submission:groupid',
                'attemptnumber' => 'privacy:metadata:peerassessment_submission:attemptnumber',
                'grade' => 'privacy:metadata:peerassessment_submission:grade',            
                'finalgrade' => 'privacy:metadata:peerassessment_submission:finalgrade',
                'feedbacktext' => 'privacy:metadata:peerassessment_submission:feedbacktext',
                'feedbackformat' => 'privacy:metadata:peerassessment_submission:feedbackformat',
                'timegraded' => 'privacy:metadata:peerassessment_submission:timegraded',
                'gradedby' => 'privacy:metadata:peerassessment_submission:gradedby'                
             ],
            'privacy:metadata:peerassessment_submission'
        );

        // The 'peerassessment_peers' table contains information about user peer grades and feedback.
        $items->add_database_table(
            'peerassessment_peers',
             [
                'id' => 'privacy:metadata:peerassessment_peers:userid',
                'peerassessment' => 'privacy:metadata:peerassessment_peers:peerassessment',
                'groupid' => 'privacy:metadata:peerassessment_peers:groupid',
                'grade' => 'privacy:metadata:peerassessment_peers:grade',
                'groupid' => 'privacy:metadata:peerassessment_peers:groupid',            
                'gradedby' => 'privacy:metadata:peerassessment_peers:gradedby',
                'gradefor' => 'privacy:metadata:peerassessment_peers:gradefor',
                'feedback' => 'privacy:metadata:peerassessment_peers:feedback',
                'timecreated' => 'privacy:metadata:peerassessment_peers:timecreated'                
             ],
            'privacy:metadata:peerassessment_peers'
        );

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * In the case of peerassessment, that is any peerassessment where the user has submitted or their own assignment
     * or graded their peers.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {
        // Fetch all peerassessment submissions, and peerassessment grades and feedback.
        $sql = "SELECT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {peerassessment} p ON p.id = cm.instance
             LEFT JOIN {peerassessment_submission} ps ON ps.assignment = p.id
             LEFT JOIN {peerassessment_peers} pp ON pp.peerassessment = p.id
                 WHERE (
                    ps.userid = :userid OR
                    ps.gradedby = :ps_gradedby OR
                    pp.gradedby = :pp_gradedby OR
                    pp.gradefor = :gradefor
                )
        ";
        $params = [
            'modname'  => 'peerassessment',
            'contextlevel' => CONTEXT_MODULE,
            'userid' => $userid,
            'ps_gradedby' => $userid,
            'pp_gradedby'  => $userid,
            'gradefor'  => $userid,
        ];

        $contextlist = new \core_privacy\local\request\contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        $params = [
            'instanceid'    => $context->instanceid,
            'modulename'    => 'peerassessment',
        ];

        // Submission authors.
        $sql = "SELECT ps.userid
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {peerassessment} p ON p.id = cm.instance
                  JOIN {peerassessment_submissions} ps ON ps.assignment = p.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Grader.
        $sql = "SELECT ps.gradedby
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {peerassessment} p ON p.id = cm.instance
                  JOIN {peerassessment_submissions} ps ON ps.assignment = p.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('gradedby', $sql, $params);

        // Grader.
        $sql = "SELECT pp.gradefor
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {peerassessment} p ON p.id = cm.instance
                  JOIN {peerassessment_peers} pp ON pp.peerassessment = p.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('gradefor', $sql, $params);

        // Graded user.
        $sql = "SELECT pp.gradedby
                  FROM {course_modules} cm
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {peerassessment} p ON p.id = cm.instance
                  JOIN {peerassessment_peers} pp ON pp.peerassessment = p.id
                 WHERE cm.id = :instanceid";
        $userlist->add_from_sql('gradedby', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            // Check that the context is a module context.
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $user = $contextlist->get_user();
            $peerassessmentdata = helper::get_context_data($context, $user);
            helper::export_context_files($context, $user);

            writer::with_context($context)->export_data([], $peerassessmentdata);
            list($context, $course, $cm) = get_context_info_array($context->id);
            $peerassessment = $DB->get_record('peerassessment', ['id' => $cm->instance], '*', MUST_EXIST);

            // I need to find out if I'm a student or a teacher.
            if ($userids = self::get_graded_users($user->id, $peerassessment)) {
                // Return teacher info.
                $currentpath = [get_string('privacy:studentpath', 'mod_peerassessment')];

                foreach ($userids as $studentuserid) {
                    $studentpath = array_merge($currentpath, [$studentuserid->id]);
                    static::export_submission($peerassessment, $studentuserid, $context, $studentpath, true);
                }
            }

            static::export_submission($peerassessment, $user, $context, []);
         }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context                 $context   The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // global $DB;

        // // Check that this is a context_module.
        // if (!$context instanceof \context_module) {
        //     return;
        // }

        // // Get the course module.
        // if (!$cm = get_coursemodule_from_id('peerassessment', $context->instanceid)) {
        //     return;
        // }

        // $peerassessmentid = $cm->instance;

        // $DB->delete_records('peerassessment_track_prefs', ['peerassessmentid' => $peerassessmentid]);
        // $DB->delete_records('peerassessment_subscriptions', ['peerassessment' => $peerassessmentid]);
        // $DB->delete_records('peerassessment_read', ['peerassessmentid' => $peerassessmentid]);
        // $DB->delete_records('peerassessment_digests', ['peerassessment' => $peerassessmentid]);

        // // Delete all discussion items.
        // $DB->delete_records_select(
        //     'peerassessment_queue',
        //     "discussionid IN (SELECT id FROM {peerassessment_discussions} WHERE peerassessment = :peerassessment)",
        //     [
        //         'peerassessment' => $peerassessmentid,
        //     ]
        // );

        // $DB->delete_records_select(
        //     'peerassessment_posts',
        //     "discussion IN (SELECT id FROM {peerassessment_discussions} WHERE peerassessment = :peerassessment)",
        //     [
        //         'peerassessment' => $peerassessmentid,
        //     ]
        // );

        // $DB->delete_records('peerassessment_discussion_subs', ['peerassessment' => $peerassessmentid]);
        // $DB->delete_records('peerassessment_discussions', ['peerassessment' => $peerassessmentid]);

        // // Delete all files from the posts.
        // $fs = get_file_storage();
        // $fs->delete_area_files($context->id, 'mod_peerassessment', 'post');
        // $fs->delete_area_files($context->id, 'mod_peerassessment', 'attachment');

        // // Delete all ratings in the context.
        // \core_rating\privacy\provider::delete_ratings($context, 'mod_peerassessment', 'post');

        // // Delete all Tags.
        // \core_tag\privacy\provider::delete_item_tags($context, 'mod_peerassessment', 'peerassessment_posts');
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * Removing assessments of submissions is not trivial. Removing one user's data can easily affect
     * other users' grades and feedback. So we replace the non-essential contents with a "deleted" message,
     * but keep the actual info in place. The argument is that one's right for privacy should not overweight others'
     * right for accessing their own personal data and be evaluated on their basis.     
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        // global $DB;

        // list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);
        // $user = $contextlist->get_user();

        // // Replace personal data in feedback from peers - feedback is seen as belonging to the recipient.
        // $sql = "SELECT wa.id AS assessmentid
        //           FROM {course_modules} cm
        //           JOIN {modules} m ON cm.module = m.id AND m.name = :module
        //           JOIN {context} ctx ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
        //           JOIN {peerassessment} p ON cm.instance = p.id
        //           JOIN {peerassessment_peers} pp ON pp.peerassessment = p.id AND ps.gradefor = :userid
        //          WHERE ctx.id {$contextsql}";

        // $params = $contextparams + [
        //     'module' => 'peerassessment',
        //     'contextlevel' => CONTEXT_MODULE,
        //     'gradefor' => $user->id,
        // ];

        // $assessmentids = $DB->get_fieldset_sql($sql, $params);

        // if ($assessmentids) {
        //     list($assessmentidsql, $assessmentidparams) = $DB->get_in_or_equal($assessmentids, SQL_PARAMS_NAMED);

        //     $DB->set_field_select('peerassessment_peers', 'feedback', get_string('privacy:request:delete:content',
        //         'mod_peerassessment'), "id $assessmentidsql", $assessmentidparams);
        // }        
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        // global $DB;

        // $context = $userlist->get_context();
        // $cm = $DB->get_record('course_modules', ['id' => $context->instanceid]);
        // $peerassessment = $DB->get_record('peerassessment', ['id' => $cm->instance]);

        // list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        // $params = array_merge(['peerassessmentid' => $peerassessment->id], $userinparams);

        // $DB->delete_records_select('peerassessment_track_prefs', "peerassessmentid = :peerassessmentid AND userid {$userinsql}", $params);
        // $DB->delete_records_select('peerassessment_subscriptions', "peerassessment = :peerassessmentid AND userid {$userinsql}", $params);
        // $DB->delete_records_select('peerassessment_read', "peerassessmentid = :peerassessmentid AND userid {$userinsql}", $params);
        // $DB->delete_records_select(
        //     'peerassessment_queue',
        //     "userid {$userinsql} AND discussionid IN (SELECT id FROM {peerassessment_discussions} WHERE peerassessment = :peerassessmentid)",
        //     $params
        // );
        // $DB->delete_records_select('peerassessment_discussion_subs', "peerassessment = :peerassessmentid AND userid {$userinsql}", $params);

        // // Do not delete discussion or peerassessment posts.
        // // Instead update them to reflect that the content has been deleted.
        // $postsql = "userid {$userinsql} AND discussion IN (SELECT id FROM {peerassessment_discussions} WHERE peerassessment = :peerassessmentid)";
        // $postidsql = "SELECT fp.id FROM {peerassessment_posts} fp WHERE {$postsql}";

        // // Update the subject.
        // $DB->set_field_select('peerassessment_posts', 'subject', '', $postsql, $params);

        // // Update the subject and its format.
        // $DB->set_field_select('peerassessment_posts', 'message', '', $postsql, $params);
        // $DB->set_field_select('peerassessment_posts', 'messageformat', FORMAT_PLAIN, $postsql, $params);

        // // Mark the post as deleted.
        // $DB->set_field_select('peerassessment_posts', 'deleted', 1, $postsql, $params);

        // // Note: Do _not_ delete ratings of other users. Only delete ratings on the users own posts.
        // // Ratings are aggregate fields and deleting the rating of this post will have an effect on the rating
        // // of any post.
        // \core_rating\privacy\provider::delete_ratings_select($context, 'mod_peerassessment', 'post', "IN ($postidsql)", $params);

        // // Delete all Tags.
        // \core_tag\privacy\provider::delete_item_tags_select($context, 'mod_peerassessment', 'peerassessment_posts', "IN ($postidsql)", $params);

        // // Delete all files from the posts.
        // $fs = get_file_storage();
        // $fs->delete_area_files_select($context->id, 'mod_peerassessment', 'post', "IN ($postidsql)", $params);
        // $fs->delete_area_files_select($context->id, 'mod_peerassessment', 'attachment', "IN ($postidsql)", $params);
    }


    /**
     * Find out if this user has graded any users.
     *
     * @param  int $userid The user ID (potential teacher).
     * @param  peerassessment $peerassessment The peerassessment object.
     * @return array If successful an array of objects with userids that this user graded, otherwise false.
     */
    protected static function get_graded_users(int $userid, \stdClass $peerassessment) {
        $params = ['grader' => $userid, 'peerassessmentid' => $peerassessment->id];

        $sql = "SELECT DISTINCT userid AS id
                  FROM {peerassessment_submission}
                 WHERE gradedby = :grader AND assignment = :peerassessmentid";

        // @TODO 
        $useridlist = new \mod_assign\privacy\useridlist($userid, $peerassessment->id);
        $useridlist->add_from_sql($sql, $params);
        $userids = $useridlist->get_userids();

        return ($userids) ? $userids : false;
    }

    /**
     * Exports peerassessmentment submission data for a user.
     *
     * @param  \stdClass $peerassessment The peerassessmentment object
     * @param  \stdClass $user The user object
     * @param  \context_module $context The context
     * @param  array $path The path for exporting data
     * @param  bool|boolean $exportforteacher A flag for if this is exporting data as a teacher.
     */
    protected static function export_submission(\stdClass $peerassessment, \stdClass $user, \context_module $context, array $path,
            bool $exportforteacher = false) {
        global $DB;

        // Note: peerassessment_submission.attemptnumber is never used.
        $submissions = $DB->get_records('peerassessment_submission', ['assignment' => $peerassessment->id, 'userid' => $user->id]);
        $peergrades = $DB->get_records('peerassessment_peers', ['peerassessment' => $peerassessment->id, 'gradefor' => $user->id]);
        $graded = $DB->get_records('peerassessment_peers', ['peerassessment' => $peerassessment->id, 'gradedby' => $user->id]); 
        $teacher = ($exportforteacher) ? $user : null;


        foreach ($submissions as $submission) {
            $submissionpath = array_merge($path,
                    [get_string('privacy:submissionpath', 'mod_peerassessment', $submission->id)]);

            if (!isset($teacher)) {
                self::export_submission_data($submission, $context, $submissionpath);
            }

            if ($grade) {
                self::export_grade_data($submission, $context, $submissionpath);
            }

            foreach ($peergrades as $peergrade) {
                self::export_peer_grade_data($peergrade, $context, $submissionpath);
            }

            foreach ($graded as $grade) {
                self::export_peer_graded_data($grade, $context, $submissionpath);
            }
        }
    }

    /**
     * Formats and then exports the user's grade data.
     *
     * @param  \stdClass $grade The peerassessment grade object
     * @param  \context $context The context object
     * @param  array $currentpath Current directory path that we are exporting to.
     */
    protected static function export_grade_data(\stdClass $grade, \context $context, array $currentpath) {
        $gradedata = (object)[
            'timegraded' => transform::datetime($grade->timecreated),
            'gradedby' => transform::user($grade->grader),
            'grade' => $grade->grade,
            'groupaverage' => $grade->groupaverage,
            'individualaverage' => $grade->individualaverage,
            'finalgrade' => $grade->finalgrade,
            'feedbacktext' => $grade->feedbacktext
        ];
        writer::with_context($context)
                ->export_data(array_merge($currentpath, [get_string('privacy:gradepath', 'mod_peerassessment')]), $gradedata);
    }

    /**
     * Formats and then exports the user's peer grade data.
     *
     * @param  \stdClass $grade The peerassessment grade object
     * @param  \context $context The context object
     * @param  array $currentpath Current directory path that we are exporting to.
     */
    protected static function export_peer_grade_data(\stdClass $grade, \context $context, array $currentpath) {
        $gradedata = (object)[
            'timecreated' => transform::datetime($grade->timecreated),
            'gradedby' => transform::user($grade->gradedby),
            'grade' => $grade->grade,
            'feedback' => $grade->feedback
        ];
        writer::with_context($context)
                ->export_data(array_merge($currentpath, [get_string('privacy:gradepath', 'mod_peerassessment')]), $gradedata);
    }

    /**
     * Formats and then exports the user's peer graded data.
     *
     * @param  \stdClass $grade The peerassessment grade object
     * @param  \context $context The context object
     * @param  array $currentpath Current directory path that we are exporting to.
     */
    protected static function export_peer_graded_data(\stdClass $grade, \context $context, array $currentpath) {
        $gradedata = (object)[
            'timecreated' => transform::datetime($grade->timecreated),
            'gradefor' => transform::user($grade->gradefor),
            'grade' => $grade->grade,
            'feedback' => $grade->feedback
        ];
        writer::with_context($context)
                ->export_data(array_merge($currentpath, [get_string('privacy:gradepath', 'mod_peerassessment')]), $gradedata);
    }    

    /**
     * Formats and then exports the user's submission data.
     *
     * @param  \stdClass $submission The peerassessment submission object
     * @param  \context $context The context object
     * @param  array $currentpath Current directory path that we are exporting to.
     */
    protected static function export_submission_data(\stdClass $submission, \context $context, array $currentpath) {
        $submissiondata = (object)[
            'timecreated' => transform::datetime($submission->timecreated),
            'timemodified' => transform::datetime($submission->timemodified),
            'status' => get_string('submissionstatus_' . $submission->status, 'mod_peerassessment'),
            'groupid' => $submission->groupid
        ];
        writer::with_context($context)
                ->export_data(array_merge($currentpath, [get_string('privacy:submissionpath', 'mod_peerassessment')]), $submissiondata);
    }
}


