

define(['jquery', 'core/str', 'core/yui', 'core/pubsub', 'core_course/events'],
    function($,  str, Y, PubSub, CourseEvents) {
        /**
         * 
         *
         *
         */
        var publishEvent = function() {
            var data = {publishedHere: true};
            PubSub.publish(CourseEvents.favourited, data);
        };

        var updateFavourites = function(data) {
            // Avoid circular reference!
            if (typeof data !== 'undefined' && data.publishedHere) {
                Y.log('abort');
                return;
            }

            // Get the favourites.
            // Get user preference.
            // Check what has changed.          
            // Update user preference.
            // Fire YUI event.
            Y.use('moodle-block_culcourse_listing-course', function() {
                
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
            PubSub.subscribe(CourseEvents.unfavourited, updateFavourites);
        }

        return {
            init: init
        };



    });