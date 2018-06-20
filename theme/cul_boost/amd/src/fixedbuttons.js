/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            // Sticky Buttons on Course Page
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


                // Show/Hide Blocks
                $('.pagelayout-course').parent('html').toggleClass(window.localStorage.toggled);

                $('.favourite-btn').on('click', function() {
                    $("[data-toggle='popover']").popover('hide');
                });

                $('.toggleblocks-btn').on('click', function() {

                    if (window.localStorage.toggled != "hiddenblocks") {
                        $('.pagelayout-course').parent('html').toggleClass("hiddenblocks", true);
                        window.localStorage.toggled = "hiddenblocks";
                    } else {
                        $('.pagelayout-course').parent('html').toggleClass("hiddenblocks", false);
                        window.localStorage.toggled = "";
                    }

                });

            });

        }
    }
});
/* jshint ignore:end */