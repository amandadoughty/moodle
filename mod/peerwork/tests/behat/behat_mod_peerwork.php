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
 * Steps definitions for peerwork activity.
 *
 * @package   mod_peerwork
 * @category  test
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');
require_once(__DIR__ . '/../../../../lib/tests/behat/behat_general.php');
require_once(__DIR__ . '/../../../../lib/tests/behat/behat_forms.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Peerwork activity definitions.
 *
 * @package   mod_peerwork
 * @category  test
 * @copyright 2020 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_peerwork extends behat_base {
    /**
     * Sets the grade for the specified peer in the specified criteria.
     *
     * @When /^I give "(?P<peer_string>[^"]*)" grade "(?P<grade_string>[^"]*)" for criteria "(?P<criteria_string>[^"]*)"$/
     *
     * @param string $peer
     * @param string $grade
     * @param string $criteria
     * @return array
     */
    public function i_give_grade_for_criteria($peer, $grade, $criteria) {
        $node = $this->find('xpath', "//div[contains(@class,'mod_peerwork_criteriaheader') and contains(., '"  . $criteria . "')]");
        $criterionid = $node->getParent()->getAttribute('data-criterionid');
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "grade_idx_{$criterionid}[{$studentid}]";
        $fieldxpath = "//input[@name='" . $fieldlocator . "' and type='radio' and @value='" . $grade . "']";

        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$fieldxpath, $grade]);
    }

    /**
     * Sets the justification for the specified peer in the specified criteria.
     *
     * @When /^I give "(?P<peer_string>[^"]*)" justification "(?P<justification_string>[^"]*)" for criteria "(?P<criteria_string>[^"]*)"$/
     *
     * @param string $peer
     * @param string $justification
     * @param string $criteria
     * @return array
     */
    public function i_give_justification_for_criteria($peer, $justification, $criteria) {
        $node = $this->find('xpath', "//div[contains(@class,'mod_peerwork_criteriaheader') and contains(., '"  . $criteria . "')]");
        $criterionid = $node->getParent()->getAttribute('data-criterionid');
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "justification_{$criterionid}[{$studentid}]";
        $fieldxpath = "//textarea[@name='" . $fieldlocator . "']";

        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$fieldxpath, $justification]);
    }

    /**
     * Enables overrde of the grade for the specified peer in the specified criteria.
     *
     * @When /^I enable overriden "(?P<peer_string>[^"]*)" grade for criteria "(?P<criteria_string>[^"]*)"$/
     *
     * @param string $peer
     * @param string $criteria
     * @return array
     */
    public function i_enable_overriden_grade_for_criteria($peer, $criteria) {
        $criterionid = $this->get_criteria_id($criteria);
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "overridden_idx_{$criterionid}[{$studentid}]";
        $fieldxpath = "//input[@name='" . $fieldlocator . "']";

        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$fieldxpath, 1]);
    }

    /**
     * Overrides the grade for the specified peer in the specified criteria.
     *
     * @When /^I override "(?P<peer_string>[^"]*)" grade for criteria "(?P<criteria_string>[^"]*)" with "(?P<grade_string>[^"]*)" "(?P<comment_string>[^"]*)"$/
     *
     * @param string $peer
     * @param string $grade
     * @param string $criteria
     * @param string $comment
     * @return array
     */
    public function i_override_grade_for_criteria_with($peer, $criteria, $grade, $comments) {
        $criterionid = $this->get_criteria_id($criteria);
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "gradeoverride_idx_{$criterionid}[{$studentid}]";
        $fieldxpath = "//select[@name='" . $fieldlocator . "']";

        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$fieldxpath, $grade]);

        $fieldlocator = "comments_idx_{$criterionid}[{$studentid}]";
        $fieldxpath = "//textarea[@name='" . $fieldlocator . "']";

        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$fieldxpath, $comments]);
    }

    /**
     * Sets the revised grade for a student.
     *
     * @When /^I give "(?P<peer_string>[^"]*)" revised grade "(?P<grade_string>[^"]*)"$/
     *
     * @param string $peer
     * @param string $grade
     * @return array
     */
    public function i_give_revised_grade($peer, $grade) {
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "id_grade_$studentid";
        $fieldxpath = "//input[@id='" . $fieldlocator . "']";

        $this->execute('behat_forms::i_set_the_field_with_xpath_to', [$fieldxpath, $grade]);
    }

    /**
     * Checks that a peer grade field is disabled.
     *
     * @When /^"(?P<criteria_string>[^"]*)" "(?P<peer_string>[^"]*)" rating should be disabled$/
     *
     * @param string $criteria
     * @param string $peer
     * @return array
     */
    public function rating_should_be_disabled($criteria, $peer) {
        $node = $this->find('xpath', "//div[contains(@class,'mod_peerwork_criteriaheader') and contains(., '"  . $criteria . "')]");
        $criterionid = $node->getParent()->getAttribute('data-criterionid');
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "grade_idx_{$criterionid}[{$studentid}]";

        $this->execute('behat_general::the_element_should_be_disabled', [$fieldlocator, 'field']);
    }

    /**
     * Checks that a peer grade field is enabled.
     *
     * @When /^"(?P<criteria_string>[^"]*)" "(?P<peer_string>[^"]*)" rating should be enabled$/
     *
     * @param string $criteria
     * @param string $peer
     * @return array
     */
    public function rating_should_be_enabled($criteria, $peer) {
        $node = $this->find('xpath', "//div[contains(@class,'mod_peerwork_criteriaheader') and contains(., '"  . $criteria . "')]");
        $criterionid = $node->getParent()->getAttribute('data-criterionid');
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "grade_idx_{$criterionid}[{$studentid}]";

        $this->execute('behat_general::the_element_should_be_enabled', [$fieldlocator, 'field']);
    }

    /**
     * Checks that a criteria justification field is disabled.
     *
     * @When /^criteria "(?P<criteria_string>[^"]*)" "(?P<peer_string>[^"]*)" justification should be disabled$/
     *
     * @param string $criteria
     * @param string $peer
     * @return array
     */
    public function criteria_justification_should_be_disabled($criteria, $peer) {
        $node = $this->find('xpath', "//div[contains(@class,'mod_peerwork_criteriaheader') and contains(., '"  . $criteria . "')]");
        $criterionid = $node->getParent()->getAttribute('data-criterionid');
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "justification_{$criterionid}[{$studentid}]";

        $this->execute('behat_general::the_element_should_be_disabled', [$fieldlocator, 'field']);
    }

    /**
     * Checks that a peer justification field is disabled.
     *
     * @When /^peer "(?P<peer_string>[^"]*)" justification should be disabled$/
     *
     * @param string $peer
     * @return array
     */
    public function peer_justification_should_be_disabled($peer) {
        $studentid = $this->get_student_id($peer);
        $fieldlocator = "justifications[{$studentid}]";

        $this->execute('behat_general::the_element_should_be_disabled', [$fieldlocator, 'field']);
    }

    /**
     * Returns the id of the student with the given username.
     *
     * Please note that this function requires the student to exist. If it does not exist an ExpectationException is thrown.
     *
     * @param string $username
     * @return string
     * @throws ExpectationException
     */
    protected function get_student_id($username) {
        global $DB;
        try {
            return $DB->get_field('user', 'id', ['username' => $username], MUST_EXIST);
        } catch (dml_missing_record_exception $ex) {
            throw new ExpectationException(sprintf("There is no student in the database with the username '%s'", $username));
        }
    }

    /**
     * Returns the id of the criteria with the given name.
     *
     * Please note that this function requires the criteria to exist. If it does not exist an ExpectationException is thrown.
     *
     * @param string $description
     * @return string
     * @throws ExpectationException
     */
    protected function get_criteria_id($description) {
        global $DB;
        try {
            $sql = "SELECT id
                    FROM {peerwork_criteria}
                    WHERE " . $DB->sql_compare_text('description') . " = " . $DB->sql_compare_text(':description');
            $record = $DB->get_records_sql($sql, ['description' => $description]);

            // Function array_key_first() for PHP > 7.3.
            return current(array_keys($record));
        } catch (dml_missing_record_exception $ex) {
            throw new Exception(sprintf("There is no criteria in the database with the description '%s'", $name));
        }
    }
}