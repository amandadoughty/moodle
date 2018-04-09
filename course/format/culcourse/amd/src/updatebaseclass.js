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
 * Helper functions for CUL Course Format
 *
 * @package    course/format
 * @subpackage cul
 * @copyright  2016 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
  * @module format_culcourse/updatebaseclass
  */
define(['jquery'], function($) {

     /**
      * Used CSS selectors
      * @access private
      */
    var SELECTORS = {
        UPDATEBUTTON: '#id_updatecourseformat',
        BASECLASSSELECT: '#id_baseclass',
        ANCESTOR: 'fieldset',
        FORM: 'form.mform'
        };


    return /** @alias module:format_culcourse/updatebaseclass */ {
        /**
         * Update the baseclass.
         * @access public
         */
        init : function() {
            var ancestor = $(SELECTORS.BASECLASSSELECT).closest(SELECTORS.ANCESTOR);
            var action = $(SELECTORS.FORM).attr('action');

            $(SELECTORS.BASECLASSSELECT).on('change', function() {
                $(SELECTORS.FORM).attr('action', action + '#' + ancestor.attr('id'));
                $(SELECTORS.UPDATEBUTTON).trigger('click');
            });
        }
    };
});