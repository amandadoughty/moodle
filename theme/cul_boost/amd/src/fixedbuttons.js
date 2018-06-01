/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            // Sticky Menu
            $('.fixed-buttons').each(function() {

                var navbar = $(this);
                var anchor = $('.fixed-anchor');

                var position = $(window).scrollTop();

                function sticky_navbar() {

                    var anchorwidth = navbar.outerWidth();
                    var anchorheight = navbar.outerHeight();
                    var window_top = $(window).scrollTop();
                    var div_top = anchor.offset().top;

                    var scroll = $(window).scrollTop();

                    if (scroll > position) {
                        var div_top = anchor.offset().top;
                    } else {
                        var div_top = anchor.offset().top;
                    }

                    position = scroll;

                    if (window_top > div_top) {
                        navbar.addClass('stick');
                    } else {
                        navbar.removeClass('stick');
                    }
                }

                sticky_navbar();

                $(window).scroll(sticky_navbar);

                $(window).resize(function() {
                    sticky_navbar();
                });

            });

        }
    }
});
/* jshint ignore:end */