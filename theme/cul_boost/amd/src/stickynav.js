/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            // Sticky Menu
            $('.right-navbar').each(function() {

                var navbar = $(this);

                $('<div id="navbar-anchor"></div>').insertBefore(navbar);

                var position = $(window).scrollTop();

                function sticky_navbar() {

                    var anchorwidth = navbar.outerWidth();
                    var anchorheight = navbar.outerHeight();
                    var window_top = $(window).scrollTop();
                    var div_top = $('#navbar-anchor').offset().top;

                    var scroll = $(window).scrollTop();

                    if (scroll > position) {
                        var div_top = $('#navbar-anchor').offset().top;
                    } else {
                        var div_top = $('#navbar-anchor').offset().top;
                    }

                    position = scroll;

                    if (window_top > div_top) {
                        navbar.addClass('stick');
                        $('#navbar-anchor').css({
                            'height': anchorheight,
                            'width': anchorwidth
                        });
                    } else {
                        navbar.removeClass('stick');
                        $('#navbar-anchor').css('height', '0');
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