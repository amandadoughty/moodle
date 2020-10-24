@mod @mod_peerwork
Feature: Assignment submissions
    Background:
        Given the following "courses" exist:
            | fullname | shortname | category | groupmode |
            | Course 1 | C1 | 0 | 1 |
        And the following "users" exist:
            | username | firstname | lastname | email |
            | teacher1 | Teacher | 1 | teacher1@example.com |
            | student0 | Student | 0 | student0@example.com |
            | student1 | Student | 1 | student1@example.com |
            | student2 | Student | 2 | student2@example.com |
            | student3 | Student | 3 | student3@example.com |
        And the following "course enrolments" exist:
            | user | course | role |
            | teacher1 | C1 | editingteacher |
            | student0 | C1 | student |
            | student1 | C1 | student |
            | student2 | C1 | student |
            | student3 | C1 | student |
        And the following "groups" exist:
            | name | course | idnumber |
            | Group 1 | C1 | G1 |
        And the following "group members" exist:
            | user | group |
            | student0 | G1 |
            | student1 | G1 |
            | student2 | G1 |
            | student3 | G1 |
        And the following config values are set as admin:
        | availablescales | 2 | peerworkcalculator_rebasedpa |
        And I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And I add a "Peer Assessment" to section "1" and I fill the form with:
            | Peer assessment | Test peerwork name |
            | Description | Test peerwork description |
            | Peer grades visibility | Hidden from students |
            | Require justification | Disabled |
            | Criteria 1 description | Criteria 1 |
            | Criteria 1 scoring type | Default competence scale |
        And I log out

    @javascript
    Scenario: Students do not give justification when set to 'Disabled'
        Given I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Test peerwork name"
        And I press "Add submission"
        Then "Justifications" "link" should not exist
        And I log out

    @javascript
    Scenario: Students must give justification when set to 'Hidden from students'
        And I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And I follow "Test peerwork name"
        And I navigate to "Edit settings" in current page administration
        And I set the following fields to these values:
            | Require justification | Hidden from students |
        And I press "Save and display"
        And I log out
        And I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Test peerwork name"
        And I press "Add submission"
        Then "Justification" "link" should exist
        And I should see "Note: your comments will be hidden from your peers and only visible to teaching staff."
        And I log out

    @javascript
    Scenario: Students must give justification when set to 'Visible anonymous'
        And I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And I follow "Test peerwork name"
        And I navigate to "Edit settings" in current page administration
        And I set the following fields to these values:
            | Require justification | Visible anonymous |
        And I press "Save and display"
        And I log out
        And I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Test peerwork name"
        And I press "Add submission"
        Then "Justification" "link" should exist
        And I should see "Note: your comments will be visible to your peers but anonymised, your username will not be shown next to comments you leave."
        And I log out

    @javascript
    Scenario: Students must give justification when set to 'Visible with usernames'
        And I log in as "teacher1"
        And I am on "Course 1" course homepage with editing mode on
        And I follow "Test peerwork name"
        And I navigate to "Edit settings" in current page administration
        And I set the following fields to these values:
            | Require justification | Visible with usernames |
        And I press "Save and display"
        And I log out
        And I log in as "student1"
        And I am on "Course 1" course homepage
        And I follow "Test peerwork name"
        And I press "Add submission"
        Then "Justification" "link" should exist
        And I should see "Note: your comments and your username will be visible to your peers."
        And I log out





