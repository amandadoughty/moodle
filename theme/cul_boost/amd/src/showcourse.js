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
 * Show course function.
 *
 * @package    theme_cul_boost
 * @copyright  2018 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * @module theme_cul_boost/showcourse
 */
define(['jquery', 'core/ajax', 'core/config', 'core/notification', 'core/str'], function($, ajax, config, notification, str) {

     /**
      * Used CSS selectors
      * @access private
      */
    var SELECTORS = {
        SHOWCOURSE: '.showcourse .btn',
        H1MODULEHIDDEN: '.module-hidden',
        };

    var URL = config.wwwroot + '/theme/cul_boost/unhide_ajax.php';
    var courseId;

    /**
     * Makes course visible.
     *
     * @access private
     * @method handleClose
     * @param {event} e
     */
    var handleShow = function() {
        var data = {
            cid: courseId,
            sesskey: config.sesskey,
        };

        var settings = {
            type: 'POST',
            dataType: 'json',
            data: data
        };

        $.ajax(URL, settings).done(function(result) {
            if (result['updated']) {
                $(SELECTORS.H1MODULEHIDDEN).remove();
            }             
        });
    };

    /**
     * Displays the show confirmation to make course visible.
     *
     * @param {String} message confirmation message
     * @param {function} onconfirm function to execute on confirm
     */
    var confirmHandleShow = function(message, onconfirm) {
        str.get_strings([
            {key: 'confirm'},
            {key: 'yes'},
            {key: 'no'}
        ]).done(function(s) {
                notification.confirm(s[0], message, s[1], s[2], onconfirm);
            }
        );
    };

    return /** @alias theme_cul_boost/showcourse */ {
        /**
         * Initialize showcoursemanager
         * @access public
         * @param {int} courseid
         */
        init : function(courseid) {
            courseId = courseid;
            var body = $('body');

            body.delegate(SELECTORS.SHOWCOURSE, 'click keypress', function(e) {
                e.preventDefault();
 
                str.get_strings([
                    {key: 'confirmshowcourse', component: 'theme_cul_boost'}
                ]).done(function(s) {
                        confirmHandleShow(s[0], function() {
                            handleShow();
                        });
                    }
                );
            });            
        }
    };
});