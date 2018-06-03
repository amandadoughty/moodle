/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.viewmorelink').on('click', function() {
                $(this).parents('.dash-panel').toggleClass('reveal');
            });

        }
    }
});
/* jshint ignore:end */