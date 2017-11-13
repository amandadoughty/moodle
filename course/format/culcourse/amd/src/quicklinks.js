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
 * @subpackage culcourse
 * @copyright  2016 Amanda Doughty <amanda.doughty.1@city.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

/**
  * @module format_culcourse/quicklinks
  */
define(['jquery','core/notification'], function($, notification) {

	 /**
      * Used CSS selectors
      * @access private
      */
    var SELECTORS = {
        QUICKLINK: 'a.dash-link',
        EDITLINK: 'a.quicklinkedit',
        };
    var adminurl;
 
    /**
     * Perform the UI changes after server change
     *
     * @access private
     * @method changeVisibility
     * @param {jQuery node} link
     * @param {int} courseid
     * @param {string} name
     * @param {int} value
     */
    var changeVisibility = function(link, courseid, name, value) {
        var params = {
            courseid: courseid,
            name: name,
            value: value,
            sesskey: M.cfg.sesskey
        };
        $.post(adminurl, params, function() {})
        .done(function(data) {
            try {
            	var span = link.parent();
            	link.replaceWith(data);
            	
            	if(span.hasClass('linkhidden')) {
            		span.removeClass('linkhidden');
            	} else {
            		span.addClass('linkhidden');
            	}
            }
            catch(err) {
                notification.exception(err);
            }
        })
        .fail(function(jqXHR, status, error) {
            notification.exception(error);
        });
    };

    var getUrlParameter = function (url, name) {
	    return (RegExp(name + '=' + '(.+?)(&|$)').exec(url)||[,null])[1];
	}

    /**
     * Prompts user when removing permission
     *
     * @access private
     * @method handleRemoveRole
     * @param {event} e
     */
    var handleToggleVisibility = function(e){
        e.preventDefault();

        var link = $(e.currentTarget);
        var url = (link.attr('href'));
    	var courseid = getUrlParameter(url, 'courseid');
    	var name = getUrlParameter(url, 'name');
    	var value = getUrlParameter(url, 'value');

    	changeVisibility(link, courseid, name, value);

    };    

    return /** @alias module:core/permissionmanager */ {
        /**
         * Initialize quicklinksmanager
         * @access public
         * @param {string} adminurl
         */
        initialize : function(args) {
            adminurl = args.adminurl;
            var body = $('body');
            body.delegate(SELECTORS.EDITLINK, 'click', handleToggleVisibility);
        }
    };
});