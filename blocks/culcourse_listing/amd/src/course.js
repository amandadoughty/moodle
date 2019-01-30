define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/url', 'core/yui',
        'core/key_codes', 'core/pubsub', 'core_course/events', 'block_culcourse_listing/favourite','block_myoverview/view'],
    function($, ajax, templates, notification, str, url, Y, KeyCodes, PubSub, CourseEvents, Favourite, MyOverview) {

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
            // From block_myoverview
            ACTION_ADD_FAVOURITE: '[data-action="add-favourite"]',
            ACTION_REMOVE_FAVOURITE: '[data-action="remove-favourite"]',
        };
    var URL = M.cfg.wwwroot + '/blocks/culcourse_listing/favourite_ajax.php';

    // Y.extend(COURSE, Y.Base, {

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

            Y.io(URL, {
                method: 'POST',
                data: querystring,
                context: this,
                on: {
                    success: editfavouritesuccess(e, params, querystring),
                    end: function(id, e) {
                        editrunning = false;
                        Y.fire('culcourse-listing:update-favourites');

                        if (params.action == 'add') {
                            PubSub.publish(CourseEvents.favourited);
                            // Simulate click MyOverview.SELECTORS.ACTION_ADD_FAVOURITE.
                            $('[data-course-id = "' + params.cid + '"]' + SELECTORS.ACTION_ADD_FAVOURITE).trigger('click');
                        } else {
                            PubSub.publish(CourseEvents.unfavorited);
                            // Simulate click MyOverview.SELECTORS.ACTION_REMOVE_FAVOURITE.
                            $('[data-course-id = "' + params.cid + '"]' + SELECTORS.ACTION_REMOVE_FAVOURITE).trigger('click');
                        }
                    }
                }
            });
        });
    };

    var editfavouritesuccess = function (e, params, querystring) {
         if (params.action == 'add') {
            str.get_string('favouriteremove', 'block_culcourse_listing').then(function(langString) {
                var courseboxnode = e.target.ancestor(SELECTORS.COURSEBOXLISTCOURSEBOX, true);                
                // Change the link, title and icon to reflect that the course can now be
                // removed from favourites.
                var newurl = url + '?' + querystring.replace('add', 'remove');
                courseboxnode.one(SELECTORS.FAVOURITELINK).set('href', newurl);
                courseboxnode.one(SELECTORS.FAVOURITEICON).removeClass(CSS.FAVOURITEADD);
                courseboxnode.one(SELECTORS.FAVOURITEICON).addClass(CSS.FAVOURITEREMOVE);
                courseboxnode.one(SELECTORS.FAVOURITELINK).set('title', langString);
                return courseboxnode;
            }).done(function(courseboxnode) {
                // Create the new favourite node.
                var newfavourite = courseboxnode.cloneNode(true);
                newfavourite.setStyle('opacity', 0);
                // Append the new node to the end of the favourites list.
                Y.one(SELECTORS.FAVOURITELIST).append(newfavourite);

                // Add all the listeners to the new node.
                var config = {node: newfavourite};
                Favourite.initializer(config);

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

                return;
            }).catch(Notification.exception);

        } else {
            var keys = [
                {
                    key: 'nofavourites',
                    component: 'block_culcourse_listing'
                },
                {
                    key: 'favouriteadd',
                    component: 'block_culcourse_listing'
                },
            ];

            str.get_strings(keys).then(function(langStrings) {
                var courseboxnode = Y.one(SELECTORS.COURSEBOXLIST + ' [data-courseid="' + params.cid + '"]');
                var favouritenode = Y.one(SELECTORS.FAVOURITELIST + ' [data-courseid="' + params.cid + '"]');

                // remove from the favourite area
                favouritenode.transition ({
                    duration: 1.0,
                    easing: 'ease-in',
                    opacity: 0
                }, function() {
                    this.remove();
                });

                if (Y.all(SELECTORS.FAVOURITECOURSEBOX).isEmpty()) { 
                    Y.one(SELECTORS.FAVOURITECLEARBUTTON).hide();
                    Y.one(SELECTORS.FAVOURITEREORDERBUTTON).hide();
                    Y.one(SELECTORS.FAVOURITEALERT).setHTML('<span>' + langString[0] + '</span>');
                }
                // change the link
                var newurl = url + '?' + querystring.replace('remove','add');
                courseboxnode.one(SELECTORS.FAVOURITELINK).set('href', newurl);
                courseboxnode.one(SELECTORS.FAVOURITEICON).removeClass(CSS.FAVOURITEREMOVE);
                courseboxnode.one(SELECTORS.FAVOURITEICON).addClass(CSS.FAVOURITEADD);
                courseboxnode.set('title', langString[1]);

                return;
            }).catch(Notification.exception);            
        }

    }

    return {
        initializer: function() {
            // params.node.one(SELECTORS.FAVOURITELINK).detach();
            // params.node.one(SELECTORS.FAVOURITELINK).on('click', this.editfavourite, this);

            var doc = Y.one(Y.config.doc);
            doc.delegate('click', editfavourite, SELECTORS.FAVOURITELINK, this);

            Y.publish('culcourse-listing:update-favourites', {
                broadcast:2
            })
        }
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
