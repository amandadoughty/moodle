/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            var navigation = $('.nav-wrap');

            if (navigation.length > 0) {

                // Insert navigation placeholder element
                $('<div id="navigation-anchor"></div>').insertAfter(navigation);

                var anchor = $('#navigation-anchor');
                var mainnav = $('.navbar');
                var bottom = mainnav.outerHeight();
                var position = $(window).scrollTop();

                // Sticky navigation
                function sticky_nav() {

                    var window_top = $(window).scrollTop();
                    var anchorpos = anchor.offset().top;
                    var mainnavpos = mainnav.offset().top + bottom;
                    var reveal = mainnav.offset().top + bottom + 100;
                    var anchorheight = navigation.outerHeight();

                    if (window_top > position) {
                        var anchorpos = anchor.offset().top;
                        var mainnavpos = mainnav.offset().top + bottom;
                        var reveal = mainnav.offset().top + bottom + 100;
                    } else {
                        var anchorpos = anchor.offset().top + anchor.outerHeight();
                        var mainnavpos = mainnav.offset().top + bottom;
                        var reveal = mainnav.offset().top + bottom + 100;
                    }

                    position = window_top;

                    if (window_top > anchorpos) {
                        navigation.addClass('stick');
                        anchor.css('height', anchorheight);
                    } else {
                        navigation.removeClass('stick');
                        anchor.css('height', 0);
                    }

                    if (window_top > mainnavpos) {
                        navigation.addClass('stuck');
                    } else {
                        navigation.removeClass('stuck');
                    }

                    if (window_top > reveal) {
                        navigation.addClass('reveal');
                        $('.right-navbar').css('transform', 'translateY(' + anchorheight + 'px)');
                    } else {
                        navigation.removeClass('reveal');
                        $('.right-navbar').css('transform', 'translateY(0)');
                    }

                }

                sticky_nav();

                $(window).scroll(sticky_nav);

                $(window).resize(function() {
                    sticky_nav();
                });
            }

        }
    }
});
/* jshint ignore:end */