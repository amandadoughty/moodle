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
 * Photoboard search form
 *
 * @package    format_cul
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_cul\output;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * Admin settings search form
 *
 * @package    format_cul
 * @copyright  2018 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_cul_search_form extends moodleform {
    function definition () {
        $mform = $this->_form;
        $elements = [];
        $elements[] = $mform->createElement('hidden', 'id', $this->_customdata['courseid']);
        $elements[] = $mform->createElement('hidden', 'roleid', $this->_customdata['roleid']);
        $elements[] = $mform->createElement('hidden', 'mode', $this->_customdata['mode']);
        $elements[] = $mform->createElement('text', 'search', get_string('search', 'admin'));
        $elements[] = $mform->createElement('submit', 'submit', get_string('search'));
        $mform->addGroup($elements);
        $mform->setType('search', PARAM_RAW);
        $mform->setDefault('search', optional_param('search', '', PARAM_RAW));
    }
}


