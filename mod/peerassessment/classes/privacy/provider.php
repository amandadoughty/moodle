<?php
 
namespace mod_peerassessment\privacy;
use core_privacy\local\metadata\collection;
 
class provider implements 
        // This plugin does store personal user data.
        \core_privacy\local\metadata\provider {
 

    public static function get_metadata(collection $collection) : collection {
     
        $collection->add_database_table(
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
                'gradedby' => 'privacy:metadata:peerassessment_submission:gradedby',
                
             ],
            'privacy:metadata:peerassessment_submission'
        );

        $collection->add_database_table(
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
                'timecreated' => 'privacy:metadata:peerassessment_peers:timecreated',
                
             ],
            'privacy:metadata:peerassessment_peers'
        );
     
        return $collection;
    }


    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
 
        $sql = "SELECT c.id
                 FROM {context} c
           INNER JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextlevel
           INNER JOIN {modules} m ON m.id = cm.module AND m.name = :modname
           INNER JOIN {peerassessment} pa ON pa.id = cm.instance
            LEFT JOIN {peerassessment_submission} ps ON ps.assignment = pa.id
            
                WHERE (
                d.userid        = :peerassessmentuserid
                )
        ";
 
        $params = [
            'modname'           => 'peerassessment',
            'contextlevel'      => CONTEXT_MODULE,
            'peerassessmentuserid'  => $userid,
        ];
 
        $contextlist->add_from_sql($sql, $params);
 
        return $contextlist;
    }

}