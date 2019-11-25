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
 * The submissions_exported event.
 *
 * @package    mod_peerassessment
 * @copyright  2015 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_peerassessment\event;

defined('MOODLE_INTERNAL') || die();
/**
 * The submissions_exported event class.
 *
 * @since     Moodle 2.8
 * @copyright 2015 Amanda Doughty
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class submissions_exported extends \core\event\base {

    protected function init() {
        // This is c(reate), r(ead), u(pdate), d(elete).
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    public static function get_name() {
        return get_string('eventsubmissions_exported', 'mod_peerassessment');
    }

    public function get_description() {
        return "The user with id '{$this->userid}' exported the submissions for the 'peerassessment'
        activity with course module id '{$this->contextinstanceid}'.";
    }

    public function get_url() {
        return new \moodle_url(
            '/mod/peerassessment/exportxls.php',
            array(
                'id' => $this->contextinstanceid
                )
            );
    }
}

