/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.modal').each(function() {
                $(this).appendTo('body');
            });

        }
    }
});
/* jshint ignore:end */