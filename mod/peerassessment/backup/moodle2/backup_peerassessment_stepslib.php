<?php
// This file is part of a 3rd party created module for Moodle - http://moodle.org/
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
 * @package    mod
 * @subpackage peerassessment
 * @copyright  2013 LEARNING TECHNOLOGY SERVICES
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class backup_peerassessment_activity_structure_step extends backup_activity_structure_step
{

    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $peerassessment = new backup_nested_element('peerassessment', array('id'), array(
            'name', 'intro', 'introformat', 'timecreated',
            'timemodified', 'selfgrading', 'duedate', 'maxfiles',
            'fromdate', 'notifylatesubmissions', 'allowlatesubmissions', 'treat0asgrade', 'moderation', 'calculationtype', 'standard_deviation',
            'submissiongroupingid'));

        $peers = new backup_nested_element('peers');

        $peer = new backup_nested_element('peer', array('id'), array(
            'grade', 'groupid', 'gradedby', 'gradefor',
            'feedback', 'timecreated'));

        $submissions = new backup_nested_element('submissions');

        $submission = new backup_nested_element('submission', array('id'), array(
            'userid',
            'timecreated', 'timemodified', 'status', 'groupid', 'attemptnumber',
            'grade', 'groupaverage', 'feedbacktext', 'feedbackformat', 'timegraded',
            'gradedby'));

        // Build the tree.
        $peerassessment->add_child($peers);
        $peers->add_child($peer);

        $peerassessment->add_child($submissions);
        $submissions->add_child($submission);

        // Define sources.
        $peerassessment->set_source_table('peerassessment', array('id' => backup::VAR_ACTIVITYID));

        // All the rest of elements only happen if we are including user info.
        if ($userinfo) {

            $peer->set_source_sql('
            SELECT *
            FROM {peerassessment_peers}
            WHERE peerassessment = ?',
                array(backup::VAR_PARENTID));

            $submission->set_source_table('peerassessment_submission', array('assignment' => '../../id'));
        }

        // Define id annotations.
        $peerassessment->annotate_ids('grouping', 'submissiongroupingid');

        $peer->annotate_ids('user', 'gradedby');
        $peer->annotate_ids('user', 'gradefor');

        $submission->annotate_ids('user', 'userid');
        $submission->annotate_ids('user', 'gradedby');
        $submission->annotate_ids('group', 'groupid');

        // Define file annotations.
        $peerassessment->annotate_files('mod_peerassessment', 'intro', null);
        $submission->annotate_files('mod_peerassessment', 'submission', 'groupid');
        $submission->annotate_files('mod_peerassessment', 'feedback_files', 'groupid');

        // Return the root element (choice), wrapped into standard activity structure.
        return $this->prepare_activity_structure($peerassessment);

    }
}