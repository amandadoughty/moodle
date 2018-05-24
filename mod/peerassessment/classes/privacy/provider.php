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

}