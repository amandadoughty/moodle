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

define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/url', 'core/yui',
        'core/modal_factory', 'core/modal_events', 'core/key_codes', 'block_culcourse_listing/favourite',
        'block_myoverview/main', 'block_starredcourses/main'],
    function($, ajax, templates, notification, str, url, Y, ModalFactory, ModalEvents, KeyCodes, Favourite,
        MyOverview, StarredCourses) {

    var CSS = {
            BUTTONALERT: 'btn-danger',
            FAVOURITEADD: 'fa fa-star-o',
            FAVOURITEREMOVE: 'gold fa fa-star',
        };
    var SELECTORS = {
        COURSEBOXLIST: '.block_culcourse_listing  .course_category_tree',

        COURSEBOX: '.culcoursebox',
        COURSEBOXSPINNERLOCATION: '.coursename a',
        COURSEBOXLISTCOURSEBOX: '.course_category_tree .culcoursebox',
        FAVOURITELIST: '.favourite_list',
        FAVOURITECOURSEBOX: '.favourite_list .culcoursebox',
        FAVOURITEICON: '.favouritelink i',
        FAVOURITECLEARBUTTON: '.block_culcourse_listing #clearfavourites',
        FAVOURITECLEARINPUT: '.block_culcourse_listing #clearfavourites [type=submit]',
        FAVOURITEREORDERBUTTON: '.block_culcourse_listing #reorderfavourites',
        FAVOURITEREORDERINPUT: '.block_culcourse_listing #reorderfavourites [type=submit]',
    };
    var URLREORDER = M.cfg.wwwroot + '/blocks/culcourse_listing/reorderfavourites_ajax.php';
    var URLCLEAR = M.cfg.wwwroot + '/blocks/culcourse_listing/clearfavourites_ajax.php';

    var setupfavourites = function() {
        var courselist = Y.all(SELECTORS.FAVOURITECOURSEBOX);

        courselist.each(function(node){
            var config = {node: node};
            Favourite.initializer(config);
        });
    };

    var reorder = function(e) {
        e.preventDefault();

        var params = {
            sesskey : M.cfg.sesskey
        };
        var args = {};
        args.message = M.util.get_string('reorderfavouritescheck', 'block_culcourse_listing');

        args.callback = function(e){
            Y.one(SELECTORS.FAVOURITELIST).transition ({
                 duration: 2.0,
                 easing: 'ease-in',
                 opacity: 0
            })

            Y.io(URLREORDER, {
                data: build_querystring(params),
                on: {
                    success: function(id, e) {
                        Y.one(SELECTORS.FAVOURITELIST).replace(Y.Node.create(e.responseText).setStyle('opacity', 0));
                        setupfavourites();

                        Y.one(SELECTORS.FAVOURITELIST).transition ({
                             duration: 2.0,
                             easing: 'ease-in',
                             opacity: 1
                        })
                    },
                    failure: function(id, e) {
                        // Not currently used.
                    },
                    end: function(id, e) {
                        var favids = [];
                        Y.all(SELECTORS.FAVOURITECOURSEBOX).each(function(node) {
                            favids.push(node.getData('courseid'));
                        });
                        Y.fire('culcourse-listing:update-favourites', {
                            favourites: favids
                        });
                    }
                }
            });
        }

        M.util.show_confirm_dialog(e, args);
    };

    var clear = function(e) {
        e.preventDefault();
        var params = {
            sesskey : M.cfg.sesskey
        };
        var args = {};
        args.message = M.util.get_string('clearfavouritescheck', 'block_culcourse_listing');
        args.scope = this;

        args.callback = function(e){
            Y.io(URLCLEAR, {
                context: this,
                data: build_querystring(params),
                on: {
                    success: function(id) {
                        // Remove all courses from the favourite area.
                        Y.all(SELECTORS.FAVOURITECOURSEBOX).remove();
                        // Change the category course styles and links.
                        var favouriteiconlist = Y.all(SELECTORS.FAVOURITEICON);
                        favouriteiconlist.each(function(node) {
                            // Change the link, title and icon to reflect that the course can now be
                            // removed from favourites.
                            var link = node.get('parentNode');
                            var href = link.get('href').split('?');
                            var url = href[0];
                            var querystring = href[1];
                            var newurl = url + '?' + querystring.replace('remove', 'add');
                            var title = M.util.get_string('favouriteadd', 'block_culcourse_listing');
                            link.set('href', newurl);
                            link.set('title', title);
                            node.removeClass(CSS.FAVOURITEREMOVE);
                            node.addClass(CSS.FAVOURITEADD);
                        });
                        Y.all(SELECTORS.COURSEBOXSPINNERLOCATION).transition ({
                             duration: 2.0,
                             easing: 'ease-in',
                             opacity: 1.0
                        })
                        $(SELECTORS.FAVOURITECLEARBUTTON).hide();
                        $(SELECTORS.FAVOURITEREORDERBUTTON).hide();
                        $(SELECTORS.FAVOURITELIST).html(
                            '<span>' +
                            M.util.get_string('nofavourites', 'block_culcourse_listing') +
                            '</span>'
                            );
                    },
                    failure: function(id, e) {
                        // Not currently used.
                    },
                    end: function(id, e) {
                        var favids = [];
                        Y.fire('culcourse-listing:update-favourites', {
                            favourites: favids
                        });

                        var roots = $('.block_myoverview .block-myoverview');
                        $.each(roots, function(id, node) {
                            MyOverview.init(node);
                        });

                        var roots = $('.block_starredcourses .block-starredcourses');
                        $.each(roots, function(id, node) {
                            StarredCourses.init(node);
                        });
                    }
                }
            });
        }

        M.util.show_confirm_dialog(e, args);
    };

    return {
        initializer: function(params) {
            setupfavourites();
            // Moodle has no way of adding class to single_button object.
            Y.one(SELECTORS.FAVOURITEREORDERINPUT).addClass(CSS.BUTTONALERT);
            Y.one(SELECTORS.FAVOURITECLEARINPUT).addClass(CSS.BUTTONALERT);
            Y.one(SELECTORS.FAVOURITEREORDERBUTTON).on('click', reorder, this);
            Y.one(SELECTORS.FAVOURITECLEARBUTTON).on('click', clear, this);

            Y.publish('culcourse-listing:update-favourites', {
                broadcast:2
            })
        }
    };    
});
