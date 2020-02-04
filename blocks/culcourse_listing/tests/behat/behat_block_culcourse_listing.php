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

use Behat\Gherkin\Node\TableNode as TableNode;
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
            new behat_component_named_selector('Add favourite to favourites', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']/a[@title='Add to favourites']"]),
            new behat_component_named_selector('Remove favourite from favourites', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']/a[@title='Remove from favourites']"]),
            // A favourite link star icon in the favourites list.
            new behat_component_named_selector('Favourite empty star', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']//i[@class='icon fa fa-star-o']"]),
            new behat_component_named_selector('Favourite gold star', ["//div[contains(@class,'favourites')]//div[@data-shortname=%locator%]//div[@class='favouritelink']//i[@class='icon gold fa fa-star']"]),
        ];


   }

}
