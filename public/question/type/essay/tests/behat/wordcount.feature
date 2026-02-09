@qtype @qtype_essay @javascript
Feature: Test word count in an Essay question with plain text input
  As a student
  In order to meet word count settings
  I need to be able to see my word count as I type

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student0@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name | questiontext    | defaultmark | responseformat   |
      | Test questions   | essay | TF1  | First question  | 20          | plain            |
      | Test questions   | essay | TF2  | Second question | 20          | plain            |
      | Test questions   | essay | TF3  | Third question  | 20          | editorfilepicker |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber | grade |
      | quiz     | Quiz 1 | Quiz 1 description | C1     | quiz1    | 20    |
    And quiz "Quiz 1" contains the following questions:
      | question | page |
      | TF1      | 1    |
      | TF2      | 1    |
      | TF3      | 1    |

  Scenario: Essay question with Response format set to 'Plain text' shows word count during attempt
    Given I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    When I set the field "Answer text Question 1" to "Stamptown has a netflix special"
    Then I should see "5" in the "Question 1" "qtype_essay > plaintext wordcount"
    And I should see "0" in the "Question 2" "qtype_essay > plaintext wordcount"
    And I should see "0" in the "Question 3" "qtype_essay > editor wordcount"

  Scenario: Essay questions show word count during preview
    Given I am on the "Quiz 1" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I set the field "Answer text Question 1" to "Stamptown has a netflix special"
    And I set the field "Answer text Question 3" to "Stamptown is the greatest show on earth"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    When I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should see "5" in the "Answer text Question 1" "qtype_essay > preview plaintext wordcount"
    And I should see "0" in the "Answer text Question 2" "qtype_essay > preview plaintext wordcount"
    And I should see "7" in the "Answer text Question 3" "qtype_essay > preview editor wordcount"

    When I am on the "Quiz 1" "mod_quiz > View" page logged in as "teacher1"
    And I follow "Attempts: 1"
    And I click on "Review attempt" "link" in the "Student 1" "table_row"
    Then I should see "5" in the "Answer text Question 1" "qtype_essay > preview plaintext wordcount"
    And I should see "0" in the "Answer text Question 2" "qtype_essay > preview plaintext wordcount"
    And I should see "7" in the "Answer text Question 3" "qtype_essay > preview editor wordcount"
