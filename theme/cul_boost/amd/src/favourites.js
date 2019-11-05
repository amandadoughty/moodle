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
 * A javascript module to ...
 *
 * @package    theme_cul_boost
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 
    'core/url', 'core/yui', 'core/pubsub', 'core_course/events'],
    function($, ajax, templates, Notification, str, url, Y, PubSub, CourseEvents) {

    "use strict";
    // @TODO Replace all the YUI in theme_cul_boost and block_culcourse_listing.
    var URL = M.cfg.wwwroot + '/theme/cul_boost/favourites_ajax.php';

    var updateFavourites = function () {
        $.ajax({
            url: URL,
            success: function(response) {
                var favourites = $('#theme_cul_boost_myfavourites');                

                if (favourites) {
                    favourites.remove();
                }

                if (response) {
                    // TODO use fragment.
                    var newnode = $.parseHTML(response);
                    var existingnode = $('.navbar .nav-wrap .nav-inner >a.nav-item');                    
                    existingnode.after(newnode);
                }
            },
            error: function() {

            },
            complete: function() {
            }
        }).fail(Notification.exception);
    };

    return {
        init: function () {
            Y.use('node', function() {
                Y.Global.on('culcourse-listing:update-favourites', updateFavourites);
            });

            PubSub.subscribe(CourseEvents.favourited, function() {updateFavourites();});
            PubSub.subscribe(CourseEvents.unfavorited, function() {updateFavourites();});
        }
    };
});