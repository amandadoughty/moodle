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
 * Steps definitions related with the CUL Course Listing Block.
 *
 * @package    block_culcourse_listing
 * @category   test
 * @copyright  2020 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException;
/**
 * Cul Course Listing-related steps definitions.
 *
 * @package    block_culcourse_listing
 * @category   test
 * @copyright  2020 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_culcourse_listing extends behat_base {
    /**
     * Return the list of partial named selectors.
     * 
     * IMPORTANT! Run Behat installation to register them.
     *
     * @return array
     */
    public static function get_partial_named_selectors(): array {
        return [
            // The course list.
            new behat_component_named_selector('Course', ["//div[contains(@class,'course_category_tree')]//div[@data-shortname=%locator%]"]),
            // A favourite link title in the course list.
            new behat_component_named_selector('Add course to favourites', ["//div[contains(@class,'course_category_tree')]//div[@data-shortname=%locator%]//div[@class='favouritelink']/a[@title='Add to favourites']"]),
            new behat_component_named_selector('Remove course from favourites', ["//div[contains(@class,'course_category_tree')]//div[@data-shortname=%locator%]//div[@class='favouritelink']/a[@title='Remove from favourites']"]),
            // A favourite link star icon in the course list.
            new behat_component_named_selector('Course empty star', ["//div[contains(@class,'course_category_tree')]//div[@data-shortname=%locator%]//div[@class='favouritelink']//i[@class='icon fa fa-star-o']"]),
            new behat_component_named_selector('Course gold star', ["//div[contains(@class,'course_category_tree')]//div[@data-shortname=%locator%]//div[@class='favouritelink']//i[@class='icon gold fa fa-star']"]),
            // The favourites list.
            new behat_component_named_selector('Favourite', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]"]),
            // A favourite link title in the favourites list.
            // new behat_component_named_selector('Add favourite to favourites', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']/a[@title='Add to favourites']"]),
            new behat_component_named_selector('Remove favourite from favourites', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']/a[@title='Remove from favourites']"]),
            // A favourite link star icon in the favourites list.
            // new behat_component_named_selector('Favourite empty star', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']//i[@class='icon fa fa-star-o']"]),
            new behat_component_named_selector('Favourite gold star', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']//i[@class='icon gold fa fa-star']"]),
        ];


   }

    /**
     * Return the list of  named selectors.
     * 
     * IMPORTANT! Run Behat installation to register them.
     *
     * @return array
     */
    public static function get_exact_named_selectors(): array {
        return [
            // A message saying how to add favourites when there are none
            // contains an empty star.
            new behat_component_named_selector('No favourites', ["//div[contains(@class,'favourites')]//span//i[@class='icon fa fa-star-o']"]),
        ];
   }

    /**
     * Checks that a course within the recently accessed courses 
     * block is starred.
     *
     * @Given /^course in recently accessed block should be starred "(?P<idnumber_string>(?:[^"]|\\")*)"$/
     * @param string $idnumber
     */
    public function course_in_recently_accessed_block_should_be_starred($idnumber) {
        $id = $this->get_course_id($idnumber);
        $exception = new ExpectationException('The course '.$idnumber.' is not starred.', $this->getSession());
        $selector = sprintf('.block-recentlyaccessedcourses span[data-course-id="%d"][data-region="favourite-icon"] span[aria-hidden="false"]', $id);
        $this->find('css', $selector, $exception);
    }

    /**
     * Checks that a course within the recently accessed courses 
     * block is not starred.
     *
     * @Given /^course in recently accessed block should not be starred "(?P<idnumber_string>(?:[^"]|\\")*)"$/
     * @param string $idnumber
     */
    public function course_in_recently_accessed_block_should_not_be_starred($idnumber) {
        $id = $this->get_course_id($idnumber);
        $exception = new ExpectationException('The course '.$idnumber.' is not starred.', $this->getSession());
        $selector = sprintf('.block-recentlyaccessedcourses span[data-course-id="%d"][data-region="favourite-icon"] span[aria-hidden="true"]', $id);

        $this->find('css', $selector, false);
    }

    /**
     * Checks that a course within the my overview 
     * block is starred.
     *
     * @Given /^course in my overview block should be starred "(?P<idnumber_string>(?:[^"]|\\")*)"$/
     * @param string $idnumber
     */
    public function course_in_my_overview_block_should_be_starred($idnumber) {
        $id = $this->get_course_id($idnumber);
        $exception = new ExpectationException('The course '.$idnumber.' is not starred.', $this->getSession());
        $selector = sprintf('.block-myoverview span[data-course-id="%d"][data-region="favourite-icon"] span[aria-hidden="false"]', $id);
        $this->find('css', $selector, $exception);
    }

    /**
     * Checks that a course within the my overview 
     * block is not starred.
     *
     * @Given /^course in my overview block should not be starred "(?P<idnumber_string>(?:[^"]|\\")*)"$/
     * @param string $idnumber
     */
    public function course_in_my_overview_block_should_not_be_starred($idnumber) {
        $id = $this->get_course_id($idnumber);
        $exception = new ExpectationException('The course '.$idnumber.' is not starred.', $this->getSession());
        $selector = sprintf('.block-myoverview span[data-course-id="%d"][data-region="favourite-icon"] span[aria-hidden="true"]', $id);

        $this->find('css', $selector, $exception);
        
        // try {
        //     $this->find('css', $selector, false);
        //     throw new ExpectationException('The course '.$idnumber.' is starred when it should not be.', $this->getSession());
        // } catch (ElementNotFoundException $e) {
        //     // This is good, the menu item should not be there.
        // }
    }    

    /**
     * Returns the id of the course with the given idnumber.
     *
     * Please note that this function requires the course to exist. If it does not exist an ExpectationException is thrown.
     *
     * @param string $idnumber
     * @return string
     * @throws ExpectationException
     */
    protected function get_course_id($idnumber) {
        global $DB;
        try {
            return $DB->get_field('course', 'id', array('idnumber' => $idnumber), MUST_EXIST);
        } catch (dml_missing_record_exception $ex) {
            throw new ExpectationException(sprintf("There is no course in the database with the idnumber '%s'", $idnumber));
        }
    }

}
