/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            // Global Search Form
            $('.nav-wrap .slidersearchform').on('click', function() {
                $(this).addClass('selected');
                $('.nav-wrap .slidersearchform input[type="text"]').focus();
            });

            $(document).on('click', function(e) {
                if ($(e.target).closest('.nav-wrap .slidersearchform').length === 0) {
                    $('.nav-wrap .slidersearchform').removeClass("selected");
                }
            });

        }
    }
});
/* jshint ignore:end */