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
 * Section collapse functions for CUL Course Format
 *
 * @package    course/format
 * @subpackage cul
 * @copyright  2016 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
 * @module format_culcourse/sectiontoggle
 */
define(['jquery', 'core/ajax', 'core/config', 'core/notification'], function($, Ajax, config, Notification) {

     /**
      * Used CSS selectors
      * @access private
      */
    var SELECTORS = {
        OPENALLLINK: 'a#toggles-all-opened',
        CLOSEALLLINK: 'a#toggles-all-closed',
        SECTIONHEAD: '.sectionhead',
        SECTIONBODY: '.sectionbody',
        TOGGLEHEAD: '#toggle-',
        TOGGLEBODY: '#togglesection-'
        };

    var GETURL = config.wwwroot + '/course/format/culcourse/getuserpreference.php';
    var SETURL = config.wwwroot + '/course/format/culcourse/setuserpreference.php';
    var courseId;

    /**
     * Opens all collapsed sections.
     *
     * @access private
     * @method handleOpenAll
     * @param {event} e
     */
    var handleOpenAll = function(e){
        e.preventDefault();
        $('ul.culcourse').find('.collapse').collapse('show');
    };

    /**
     * Closes all open sections.
     *
     * @access private
     * @method handleOpenAll
     * @param {event} e
     */
    var handleCloseAll = function(e){
        e.preventDefault();
        $('ul.culcourse').find('.collapse').collapse('hide');
    };

    /**
     * Saves user preference.
     *
     * @access private
     * @method handleOpen
     * @param {event} e
     */
    var handleOpen = function(e){
        var sectionid = $(e.currentTarget).data('preference-key');

        var data = {
            courseid: courseId,
            sectionid: sectionid,
            value: 1,
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
    };

    /**
     * Saves user preference.
     *
     * @access private
     * @method handleClose
     * @param {event} e
     */
    var handleClose = function(e){
        var sectionid = $(e.currentTarget).data('preference-key');

        var data = {
            courseid: courseId,
            sectionid: sectionid,
            value: 0,
            // sesskey: config.sesskey,
        };

        var settings = {
            type: 'POST',
            dataType: 'json',
            data: data
        };

        $.ajax(SETURL, settings);
    };

    return /** @alias module:format_culcourse/sectiontoggle */ {
        /**
         * Initialize sectiontogglemanager
         * @access public
         * @param {int} courseid
         */
        init : function(courseid) {
            courseId = courseid;
            var body = $('body');
            body.delegate(SELECTORS.OPENALLLINK, 'click', handleOpenAll);
            body.delegate(SELECTORS.CLOSEALLLINK, 'click', handleCloseAll);

            body.delegate(SELECTORS.SECTIONBODY, 'hide.bs.collapse', handleClose);
            body.delegate(SELECTORS.SECTIONBODY, 'show.bs.collapse', handleOpen);
            // Add the classes for expanding all of the sections. This is the
            // default.
            $(SELECTORS.SECTIONBODY).addClass('collapse in');

            // Get the userprefs. They are too large to pass to the function.
            var params = {
                courseid: courseid,
                // userid: userid,
                // sesskey: M.cfg.sesskey
            };
            $.post(GETURL, params, function() {})
                .done(function(data) {
                    var sectiontoggles = data;

                    for (var sectionid in sectiontoggles) {
                        // If expanded is set to false then change the classes to
                        // collapse the section.
                        if (sectiontoggles[sectionid] == 0) {
                            $(SELECTORS.TOGGLEHEAD + sectionid).addClass('collapsed');
                            $(SELECTORS.TOGGLEBODY + sectionid).removeClass('in');
                        }
                    }
                })
                .fail(function(jqXHR, status, error) {
                    Notification.exception(error);
                });
        }
    };
});