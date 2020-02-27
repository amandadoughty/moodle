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
 * Dashboard toggle functions for CUL Course Format
 *
 * @package    course/format
 * @subpackage cul
 * @copyright  2016 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * @module local_culcourse_dashboard/sectiontoggle
 */
define(['jquery', 'core/ajax', 'core/config', 'core/notification'], function($, Ajax, config, Notification) {

     /**
      * Used CSS selectors
      * @access private
      */
    var SELECTORS = {
        TOGGLELINK: 'a#local_culcourse_dashboard_toggledashboard',
        DASHBOARD: '#local_culcourse_dashboard_dashboard'
        };

    var GETURL = config.wwwroot + '/local/culcourse_dashboard/getuserprefdashtoggle.php';
    var SETURL = config.wwwroot + '/local/culcourse_dashboard/setuserprefdashtoggle.php';
    var courseId;

    /**
     * Saves user preference.
     *
     * @access private
     * @method handleOpen
     * @param {event} e
     */
    var handleOpen = function(e){
        e.stopPropagation();

        if ($(this).is(e.target)) {

            var data = {
                courseid: courseId,
                value: true,
                // sesskey: config.sesskey,
            };

            var settings = {
                type: 'POST',
                dataType: 'json',
                data: data
            };

            $.ajax(SETURL, settings);
                // .fail(function (request, status, error) {
                //     Notification.exception(request);
                //     console.log(error);
                // });
        }
    };

    /**
     * Saves user preference.
     *
     * @access private
     * @method handleClose
     * @param {event} e
     */
    var handleClose = function(e){
        e.stopPropagation();

        if ($(this).is(e.target)) {
            var data = {
                courseid: courseId,
                value: false,
                // sesskey: config.sesskey,
            };

            var settings = {
                type: 'POST',
                dataType: 'json',
                data: data
            };

            $.ajax(SETURL, settings);
        }
    };

    return /** @alias module:local_culcourse_dashboard/dashtoggle */ {
        /**
         * Initialize dashtogglemanager
         * @access public
         * @param {int} courseid
         */
        init : function(courseid) {
            courseId = courseid;
            var body = $('body');

            body.delegate(SELECTORS.DASHBOARD, 'hide.bs.collapse', handleClose);
            body.delegate(SELECTORS.DASHBOARD, 'show.bs.collapse', handleOpen);

            // Get the userprefs. They are too large to pass to the function.
            var params = {
                courseid: courseid,
                // userid: userid,
                // sesskey: M.cfg.sesskey
            };
            $.post(GETURL, params, function() {})
                .done(function(data) {

                    // If expanded is set to false then hide the dashboard.
                    if (!data) {
                        // $(SELECTORS.DASHBOARD).collapse('hide');
                        $(SELECTORS.DASHBOARD).addClass('collapsed');
                        $(SELECTORS.DASHBOARD).removeClass('show');
                    }

                })
                .fail(function(jqXHR, status, error) {
                    Notification.exception(error);
                });
        }
    };
});