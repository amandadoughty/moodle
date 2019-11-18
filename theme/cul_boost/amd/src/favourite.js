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
    'core/url', 'core/yui', 'core/pubsub', 'core_course/events', 
    'block_myoverview/main', 'block_starredcourses/main'],
    function($, ajax, templates, notification, str, url, Y, PubSub, CourseEvents,
        MyOverview, StarredCourses) {

    "use strict";
    var editlink = null;
    
    var editfavourite = function (e) {
        e.preventDefault();

        var href = e.target.get('href').split('?');
        var url = href[0];
        var querystring = href[1];

        Y.use('node', 'querystring-parse', 'json-parse', function() {
            Y.io(M.cfg.wwwroot+'/theme/cul_boost/favourite_ajax.php', {
                method: 'POST',
                context: this,
                data: querystring,
                on: {
                    success: function(id, e) {
                        var data = Y.JSON.parse(e.responseText);
                        var link = editlink.one('a');
                        var newurl = url + '?' + querystring.replace(data.action, data.newaction);
                        link.setAttribute('data-content', data.text);
                        link.toggleClass('favourited');
                        link.set('href', newurl);
                    },
                    end: function() {
                        Y.fire('culcourse-listing:update-favourites');

                        var roots = $('.block_myoverview .block-myoverview');
                        $.each(roots, function(id, node) {
                            MyOverview.init(node);
                        });

                        roots = $('.block_starredcourses .block-starredcourses');
                        $.each(roots, function(id, node) {
                            StarredCourses.init(node);
                        });
                    }
                }
            });
        });
    };

    return {
        init: function () {            
            if (Y.one('#theme-cul_boost-removefromfavourites')) {
                editlink = Y.one('#theme-cul_boost-removefromfavourites');
                editlink.on('click', editfavourite, this);
            }
            else if (Y.one('#theme-cul_boost-addtofavourites')) {
                editlink = Y.one('#theme-cul_boost-addtofavourites');
                editlink.on('click', editfavourite, this);
            }

            Y.publish('culcourse-listing:update-favourites', {
                broadcast:2
            });
        }
    };
});
