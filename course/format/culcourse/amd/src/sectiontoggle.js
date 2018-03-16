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
        SECTIONBODY: '.sectionbody'
        };

    var URL = config.wwwroot + '/course/format/culcourse/setuserpreference.php';


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
        var pref = $(e.currentTarget).data('preference-key');

        var data = {
            type: pref,
            value: 1,
            sesskey: config.sesskey,
        };

        var settings = {
            type: 'POST',
            dataType: 'json',
            data: data
        };

        $.ajax(URL, settings);
            // .fail(Notification.exception);
        

    };

    /**
     * Saves user preference.
     *
     * @access private
     * @method handleClose
     * @param {event} e
     */
    var handleClose = function(e){
        var pref = $(e.currentTarget).data('preference-key');

        var data = {
            type: pref,
            value: 0,
            sesskey: config.sesskey,
        };

        var settings = {
            type: 'POST',
            dataType: 'json',
            data: data
        };

        $.ajax(URL, settings);
    };

    return /** @alias module:format_culcourse/sectiontoggle */ {
        /**
         * Initialize sectiontogglemanager
         * @access public
         * @param {string} adminurl
         */
        init : function(userprefs) {
            var body = $('body');
            body.delegate(SELECTORS.OPENALLLINK, 'click', handleOpenAll);
            body.delegate(SELECTORS.CLOSEALLLINK, 'click', handleCloseAll);

            body.delegate(SELECTORS.SECTIONBODY, 'hide.bs.collapse', handleClose);
            body.delegate(SELECTORS.SECTIONBODY, 'show.bs.collapse', handleOpen);

            // Set up the sections based on user preferences.
            // var request = {
            //     methodname: 'core_user_get_user_preferences',
            //     args: {
            //         preferences: [
            //             {
            //                 type: 'block_myoverview_last_tab',
            //                 value: tabname
            //             }
            //         ]
            //     }
            // };

            // Ajax.call([request])[0]
            //     .fail(Notification.exception);

            // $(SELECTORS.SECTIONHEAD).addClass('collapsed');

            for(userpref in userprefs) {
                if(userpref.startsWith('format_culcourse_expanded')) {
                    var matches =  userpref.match(/\d+/);

                    if(matches) {
                        var sectionid = matches[0];
                        console.log(sectionid);
                    }

                    
                }
                
            }
        }
    };
});