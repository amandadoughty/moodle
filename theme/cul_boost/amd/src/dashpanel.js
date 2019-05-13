/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.viewmorelink .viewmore-inner').on('click', function() {
                $(this).parents('.dash-panel').toggleClass('reveal');
            });

        }
    }
});
/* jshint ignore:end */