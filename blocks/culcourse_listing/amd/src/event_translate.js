

define(['jquery', 'core/config', 'core/str', 'core/notification', 'core/yui', 
    'core/pubsub', 'core_course/events', 'block_myoverview/main'],
    function($, Config, str, Notification, Y, PubSub, CourseEvents, Main) {
        var url = M.cfg.wwwroot + '/blocks/culcourse_listing/favouriteapi_ajax.php'
        /**
         * 
         *
         *
         */
        var publishEvent = function() {
            var data = {publishedHere: true};
            //  core_course_set_favourite_courses
            PubSub.publish(CourseEvents.favourited, data);
        };

        /**
         * 
         *
         *
         */
        var updateFavourites = function(data) {
            // Avoid circular reference!
            if (typeof data !== 'undefined' && data.publishedHere) {
                Y.log('abort');
                return;
            }

            // var settings = {
            //     data: {sesskey: Config.sesskey}
            // };

            var data = {
                sesskey: Config.sesskey
            };
            var settings = {
                type: 'POST',
                dataType: 'json',
                data: data
            };

            $.ajax(
                url,
                settings
                ).done(function(data) {
                    // Fire YUI event.
                    Y.use('moodle-block_culcourse_listing-course', function() {
                        // SIMULATE CLICK?????

                        Y.fire('culcourse-listing:update-favourites');
                    });
                });                            
        };

        var init = function() {
            /**
             * 
             *
             *
             */
            Y.use('moodle-block_culcourse_listing-course', function() {
                Y.Global.on('culcourse-listing:update-favourites', function(data){
                    publishEvent();
                });
            });

            PubSub.subscribe(CourseEvents.favourited, updateFavourites);
            PubSub.subscribe(CourseEvents.unfavorited, updateFavourites);
        }

        return {
            init: init
        };
    });