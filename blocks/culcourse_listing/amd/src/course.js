define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/url', 'core/yui',
        'core/modal_factory', 'core/modal_events', 'core/key_codes'],
    function($, ajax, templates, notification, str, url, Y, ModalFactory, ModalEvents, KeyCodes) {

// YUI.add('moodle-block_culcourse_listing-course', function(Y) {

    // var COURSENAME = 'blocks_culcourse_listing_course';
    // var COURSE = function() {
    //     COURSE.superclass.constructor.apply(this, arguments);
    // };

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

    // Y.extend(COURSE, Y.Base, {

    var editrunning: false;

    return
        initializer: function() {
            // params.node.one(SELECTORS.FAVOURITELINK).detach();
            // params.node.one(SELECTORS.FAVOURITELINK).on('click', this.editfavourite, this);

            var doc = Y.one(Y.config.doc);
            doc.delegate('click', this.editfavourite, SELECTORS.FAVOURITELINK, this);

            Y.publish('culcourse-listing:update-favourites', {
                broadcast:2
            })
        },

    var editfavourite = function (e) {
        e.preventDefault();

        if (this.editrunning) {
            return;
        }

        this.editrunning = true;
        var target = e.target;
        var link = e.target.get('parentNode');
        var href = link.get('href').split('?');
        var url = href[0];
        var querystring = href[1];
        // returns an object with params as attributes
        var params = Y.QueryString.parse(querystring);

        Y.io(URL, {
            method: 'POST',
            data: querystring,
            context: this,
            on: {
                success: function(id, e) {
                    if (params.action == 'add') {
                        var courseboxnode = target.ancestor(SELECTORS.COURSEBOXLISTCOURSEBOX, true);
                        // Change the link, title and icon to reflect that the course can now be
                        // removed from favourites.
                        var newurl = url + '?' + querystring.replace('add', 'remove');
                        var title = M.util.get_string('favouriteremove', 'block_culcourse_listing');
                        courseboxnode.one(SELECTORS.FAVOURITELINK).set('href', newurl);
                        courseboxnode.one(SELECTORS.FAVOURITEICON).removeClass(CSS.FAVOURITEADD);
                        courseboxnode.one(SELECTORS.FAVOURITEICON).addClass(CSS.FAVOURITEREMOVE);
                        courseboxnode.one(SELECTORS.FAVOURITELINK).set('title', title);
                        // Create the new favourite node.
                        var newfavourite = courseboxnode.cloneNode(true);
                        newfavourite.setStyle('opacity', 0);
                        // Append the new node to the end of the favourites list.
                        Y.one(SELECTORS.FAVOURITELIST).append(newfavourite);

                        // Add all the listeners to the new node.
                        var config = {node: newfavourite};
                        M.blocks_culcourse_listing.init_favourite(config);

                        newfavourite.transition ({
                            duration: 2.0,
                            easing: 'ease-in',
                            opacity: 1.0
                        })

                        // There must be at least one favourite now, so show the favourite buttons
                        // if they are hidden and hide the 'no favourites' message,
                        if (!Y.all(SELECTORS.FAVOURITECOURSEBOX).isEmpty()) {
                            Y.one(SELECTORS.FAVOURITECLEARBUTTON).removeClass(CSS.HIDE);
                            Y.one(SELECTORS.FAVOURITECLEARBUTTON).show();
                            Y.one(SELECTORS.FAVOURITEREORDERBUTTON).removeClass(CSS.HIDE);
                            Y.one(SELECTORS.FAVOURITEREORDERBUTTON).show();
                            Y.one(SELECTORS.FAVOURITEALERT).setHTML('');
                        }

                    } else {
                        var courseboxnode = Y.one(SELECTORS.COURSEBOXLIST + ' [data-courseid="' + params.cid + '"]');
                        var favouritenode = Y.one(SELECTORS.FAVOURITELIST + ' [data-courseid="' + params.cid + '"]');

                        // remove from the favourite area
                        favouritenode.transition ({
                            duration: 1.0,
                            easing: 'ease-in',
                            opacity: 0
                        }, function() {
                            this.remove();

                            if (Y.all(SELECTORS.FAVOURITECOURSEBOX).isEmpty()) {
                                var alert = M.util.get_string('nofavourites', 'block_culcourse_listing');
                                Y.one(SELECTORS.FAVOURITECLEARBUTTON).hide();
                                Y.one(SELECTORS.FAVOURITEREORDERBUTTON).hide();
                                Y.one(SELECTORS.FAVOURITEALERT).setHTML('<span>' + alert + '</span>');
                            }

                        });

                        // change the link
                        var newurl = url + '?' + querystring.replace('remove','add');
                        var title = M.util.get_string('favouriteadd', 'block_culcourse_listing');

                        // The coursebox may not have been rendered yet.
                        if (courseboxnode) {
                            courseboxnode.one(SELECTORS.FAVOURITELINK).set('href', newurl);
                            courseboxnode.one(SELECTORS.FAVOURITEICON).removeClass(CSS.FAVOURITEREMOVE);
                            courseboxnode.one(SELECTORS.FAVOURITEICON).addClass(CSS.FAVOURITEADD);
                            courseboxnode.set('title', title);
                        }
                    }
                },
                end: function(id, e) {
                    this.editrunning = false;
                    Y.fire('culcourse-listing:update-favourites');

                    if (params.action == 'add') {
                        // Y.log('here');
                        // Y.fire('core_course:favourited');
                    } else {
                        // Y.fire('core_course:unfavourited');
                    }
                }
            }
        });
    };

  //   }, {
  //       NAME : COURSENAME,
  //       ATTRS : {
  //       }
  //   });
  //   M.blocks_culcourse_listing = M.blocks_culcourse_listing || {};
  //   M.blocks_culcourse_listing.course = COURSE || {};
  //   M.blocks_culcourse_listing.init_course = function(params) {
  //       return new COURSE(params);
  //   };
  // }, '@VERSION@', {
  //     requires:['base', 'anim', 'tabview', 'transition', 'querystring-parse', 'event-custom']
});
