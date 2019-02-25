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
 * @package    block_culcourse_listing
 * @copyright  2019 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/yui',
        'core/pubsub', 'core_course/events', 'block_culcourse_listing/favourite', 
        'block_myoverview/main', 'block_starredcourses/main'],
    function($, Ajax, Templates, Notification, Str, Y, PubSub, CourseEvents, Favourite, 
        MyOverview, StarredCourses) {

    var CSS = {
            HIDE: 'hide',
            FAVOURITEADD: 'fa fa-star-o',
            FAVOURITEREMOVE: 'gold fa fa-star',
        };
    var SELECTORS = {
            COURSEBOXLIST: '.block_culcourse_listing .course_category_tree',
            COURSEBOXLISTCOURSEBOX: '.course_category_tree .culcoursebox',
            FAVOURITELIST: '.favourite_list',
            FAVOURITECOURSEBOX: '.favourite_list .culcoursebox',
            FAVOURITELINK: '.favouritelink',
            FAVOURITEICON: '.favouritelink i',
            FAVOURITECLEARBUTTON: '.block_culcourse_listing #clearfavourites',
            FAVOURITEREORDERBUTTON: '.block_culcourse_listing #reorderfavourites',
            FAVOURITEALERT: '.block_culcourse_listing .favourite_list span',
        };
    var URL = M.cfg.wwwroot + '/blocks/culcourse_listing/favourite_ajax.php';
    var APIURL = M.cfg.wwwroot + '/blocks/culcourse_listing/favouriteapi_ajax.php';
    var editrunning = false;

    var editfavourite = function (e) {
        e.preventDefault();

        if (editrunning) {
            return;
        }

        editrunning = true;
        var target = e.target;
        var link = e.target.get('parentNode');
        var href = link.get('href').split('?');
        var url = href[0];
        var querystring = href[1];        

        Y.use('base', 'anim', 'tabview', 'transition', 'querystring-parse', 'event-custom', function() {
            // returns an object with params as attributes
            var params = Y.QueryString.parse(querystring);

            $.ajax({
                url: URL,
                method: 'POST',
                data: params,
                context: self,
                success: function(response) {
                    editfavouritesuccess(response, params);
                },
                error: function(response) {

                },
                complete: function(response) {
                    editrunning = false;
                    Y.fire('culcourse-listing:update-favourites');

                    var roots = $('.block_myoverview .block-myoverview');
                    $.each(roots, function(id, node) {
                        MyOverview.init(node);
                    });

                    var roots = $('.block_starredcourses .block-starredcourses');
                    $.each(roots, function(id, node) {
                        StarredCourses.init(node);
                    });
                }
            }).fail(Notification.exception);
        });
    };

    var editfavouritesuccess = function (response, params) {
        Templates.render('block_culcourse_listing/coursebox', response)
            .then(function(html, js) {
                var newcourseboxnode = $(html);
                var newfavouritenode = $(html);

                if (params.action == 'add') {
                        var courseboxnode = $(SELECTORS.COURSEBOXLIST + ' [data-courseid="' + params.cid + '"]');                        

                        if (courseboxnode) {
                            courseboxnode.replaceWith(newcourseboxnode);
                        }

                        newfavouritenode.css('opacity', 0);
                        $(SELECTORS.FAVOURITELIST).append(newfavouritenode);

                        // Add all the listeners to the new node. - delegate should remove need for this?
                        // Keeping the YUI code to avoid complete rewrite.
                        var newfavourite = Y.one(SELECTORS.FAVOURITELIST + ' [data-courseid="' + params.cid + '"]');
                        var config = {node: newfavourite};
                        Favourite.initializer(config);

                        // There must be at least one favourite now, so show the favourite buttons
                        // if they are hidden and hide the 'no favourites' message.
                        if ($(SELECTORS.FAVOURITECOURSEBOX).length > 0) { 
                            $(SELECTORS.FAVOURITECLEARBUTTON).show().css('display', 'inline-block');;
                            $(SELECTORS.FAVOURITEREORDERBUTTON).show().css('display', 'inline-block');;
                            $(SELECTORS.FAVOURITEALERT).text('');
                        }

                        newfavouritenode.animate({
                            opacity: 1
                        }, 1000, function() {  
                            
                        });

                        return;

                } else {
                    Str.get_string('nofavourites', 'block_culcourse_listing').then(function(langstring) {
                        var courseboxnode = $(SELECTORS.COURSEBOXLIST + ' [data-courseid="' + params.cid + '"]');
                        var favouritenode = $(SELECTORS.FAVOURITELIST + ' [data-courseid="' + params.cid + '"]');

                        favouritenode.animate({
                            opacity: 0
                        }, 1000, function() {
                            this.remove();

                            if ($(SELECTORS.FAVOURITECOURSEBOX).length == 0) { 
                                $(SELECTORS.FAVOURITECLEARBUTTON).hide();
                                $(SELECTORS.FAVOURITEREORDERBUTTON).hide();
                                $(SELECTORS.FAVOURITEALERT).html('<span>' + langstring + '</span>');
                            }
                        });

                        if (courseboxnode) {
                            courseboxnode.replaceWith(newcourseboxnode);
                        }

                        return;
                    }).catch(Notification.exception);            
                }
            })
            .fail(Notification.exception);
    };

    var updateFavouriteViaApi = function() {
        // Find out what changed via the api.
        $.ajax({
            url: APIURL,
            method: 'POST',
            data: {sesskey: M.cfg.sesskey},
            context: self,
            success: function(response) {
                if (response.data) {
                    editfavouritesuccess(response.data, response);
                    return true;
                } else {
                    return false;
                }
            },
            error: function(response) {
                Notification.addNotification({
                    message: response.error,
                    type: 'error'
                });
            }
        }).fail(Notification.exception);

        
    };

    return {
        initializer: function() {
            var doc = Y.one(Y.config.doc);
            doc.delegate('click', editfavourite, SELECTORS.FAVOURITELINK, this);

            Y.publish('culcourse-listing:update-favourites', {
                broadcast:2
            })

            PubSub.subscribe(CourseEvents.favourited, function() {updateFavouriteViaApi();});
            PubSub.subscribe(CourseEvents.unfavorited, function() {updateFavouriteViaApi();});
        }
    };    
});
