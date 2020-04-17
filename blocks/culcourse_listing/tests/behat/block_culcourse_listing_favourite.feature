@block @block_culcourse_listing @javascript
Feature: Test that students can favourite a course

    Background:
        Given the following "users" exist:
            | username | firstname | lastname | email                | idnumber |
            | student1 | Student   | X        | student1@example.com | S1       |
        And the following "courses" exist:
            | fullname | shortname | idnumber | category |
            | Course 1 | C1        | C1       | 0        |
            | Course 2 | C2        | C2       | 0        |
            | Course 3 | C3        | C3       | 0        |
            | Course 4 | C4        | C4       | 0        |
            | Course 5 | C5        | C5       | 0        |
            | Course 5 | C6        | C6       | 0        |
            | Course 5 | C7        | C7       | 0        |
            | Course 5 | C8        | C8       | 0        |
            | Course 5 | C9        | C9       | 0        |
        And the following "course enrolments" exist:
            | user | course | role |
            | student1 | C1 | student |
            | student1 | C2 | student |
            | student1 | C3 | student |
            | student1 | C4 | student |
            | student1 | C5 | student |
            | student1 | C6 | student |
            | student1 | C7 | student |
            | student1 | C8 | student |
            | student1 | C9 | student |
        And I log in as "admin"
        And I navigate to "Appearance > Default Dashboard page" in site administration
        And I press "Blocks editing on"
        And I add the "CUL Course listing" block if not present
        And I add the "Course overview" block if not present
        And I add the "Starred courses" block if not present
        And I press "Blocks editing off"
        And I press "Reset Dashboard for all users"
        And I log out
        And I log in as "student1"
        And I click on ".favouritelink" "css_element" in the "C3" "block_culcourse_listing > Course"
        And I click on ".favouritelink" "css_element" in the "C1" "block_culcourse_listing > Course"
        And I click on ".favouritelink" "css_element" in the "C2" "block_culcourse_listing > Course"
        And I log out     

    Scenario: Favourite a course in the course list
        Given I log in as "student1"
        When I click on ".favouritelink" "css_element" in the "C4" "block_culcourse_listing > Course"    
        Then "C4" "block_culcourse_listing > Add course to favourites" should not exist
        And "C4" "block_culcourse_listing > Course empty star" should not exist
        And "C4" "block_culcourse_listing > Remove course from favourites" should exist
        And "C4" "block_culcourse_listing > Course gold star" should exist
        And "C4" "block_culcourse_listing > Add favourite to favourites" should not exist
        And "C4" "block_culcourse_listing > Favourite empty star" should not exist
        And "C4" "block_culcourse_listing > Remove favourite from favourites" should exist
        And "C4" "block_culcourse_listing > Favourite gold star" should exist

    Scenario: Unfavourite a course in the course list
        Given I log in as "student1"
        When I click on ".favouritelink" "css_element" in the "C3" "block_culcourse_listing > Course"    
        Then "C3" "block_culcourse_listing > Remove course from favourites" should not exist
        And "C3" "block_culcourse_listing > Course gold star" should not exist
        And "C3" "block_culcourse_listing > Add course to favourites" should exist
        And "C3" "block_culcourse_listing > Course empty star" should exist
        And "C3" "block_culcourse_listing > Remove favourite from favourites" should not exist
        And "C3" "block_culcourse_listing > Favourite gold star" should not exist
        
    Scenario: Unfavourite a course in the favourite list
        Given I log in as "student1"
        When I click on ".favouritelink" "css_element" in the "C3" "block_culcourse_listing > Favourite"    
        Then "C3" "block_culcourse_listing > Remove course from favourites" should not exist
        And "C3" "block_culcourse_listing > Course gold star" should not exist
        And "C3" "block_culcourse_listing > Add course to favourites" should exist
        And "C3" "block_culcourse_listing > Course empty star" should exist
        And "C3" "block_culcourse_listing > Remove favourite from favourites" should not exist
        And "C3" "block_culcourse_listing > Favourite gold star" should not exist

    Scenario: Clear favourites
        Given I log in as "student1"
        And I press "Clear favourites"
        And I press "Yes"
        Then I should see "Add favourites to a menu by clicking the grey star"

    Scenario: Reorder favourites
        Given I log in as "student1"
        And I press "Reset order"
        And I press "Yes"
        Then "C1" "link" should appear before "C2" "link" in the ".favourite_list" "css_element"
        And "C2" "link" should appear before "C3" "link" in the ".favourite_list" "css_element"

    Scenario: Favourite a course in the module list to update My Overview, Recently Accessed Courses and Starred Courses
        Given I log in as "student1"
        And I am on "Course 4" course homepage
        And I follow "Dashboard" in the user menu
        When I click on ".favouritelink" "css_element" in the "C4" "block_culcourse_listing > Course"    
        Then I should see "Course 4" in the "Starred courses" "block"
        And course in recently accessed block should be starred "C4"
        And course in my overview block should be starred "C4"

    Scenario: Unfavourite a course in the favourite list to update My Overview, Recently Accessed Courses and Starred Courses
        Given I log in as "student1"
        And I am on "Course 3" course homepage
        And I follow "Dashboard" in the user menu
        When I click on ".favouritelink" "css_element" in the "C3" "block_culcourse_listing > Favourite"
        Then I should not see "Course 3" in the "Starred courses" "block"
        And course in recently accessed block should not be starred "C3"
        And course in my overview block should not be starred "C3"

    Scenario: Favourite a course in My Overview and update CUL Course Listing
        Given I log in as "student1"
        When I click on ".coursemenubtn" "css_element" in the "//div[@data-region='myoverview']//div[@class='card dashboard-card' and contains(.,'Course 4')]" "xpath_element"
        And I click on "Star this course" "link" in the "//div[@data-region='myoverview']//div[@class='card dashboard-card' and contains(.,'Course 4')]" "xpath_element"
        Then "C4" "block_culcourse_listing > Add course to favourites" should not exist
        And "C4" "block_culcourse_listing > Course empty star" should not exist
        And "C4" "block_culcourse_listing > Remove course from favourites" should exist
        And "C4" "block_culcourse_listing > Course gold star" should exist
        And "C4" "block_culcourse_listing > Add favourite to favourites" should not exist
        And "C4" "block_culcourse_listing > Favourite empty star" should not exist
        And "C4" "block_culcourse_listing > Remove favourite from favourites" should exist
        And "C4" "block_culcourse_listing > Favourite gold star" should exist

    Scenario: Unfavourite a course in My Overview and update CUL Course Listing
        Given I log in as "student1"
        When I click on ".coursemenubtn" "css_element" in the "//div[@data-region='myoverview']//div[@class='card dashboard-card' and contains(.,'Course 2')]" "xpath_element"
        And I click on "Unstar this course" "link" in the "//div[@data-region='myoverview']//div[@class='card dashboard-card' and contains(.,'Course 2')]" "xpath_element"
        Then "C2" "block_culcourse_listing > Remove course from favourites" should not exist
        And "C2" "block_culcourse_listing > Course gold star" should not exist
        And "C2" "block_culcourse_listing > Add course to favourites" should exist
        And "C2" "block_culcourse_listing > Course empty star" should exist
        And "C2" "block_culcourse_listing > Remove favourite from favourites" should not exist
        And "C2" "block_culcourse_listing > Favourite gold star" should not exist

    

    
        



        







        