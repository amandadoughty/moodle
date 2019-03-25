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
                
                var rightnav = $('.right-navbar').outerHeight();

                if ($('.right-navbar').length == 0) {
                    rightnav = 0;
                }
                
                var bottom = mainnav.outerHeight();
                var position = $(window).scrollTop();

                // Sticky navigation
                function sticky_nav() {

                    var window_top = $(window).scrollTop();
                    var anchorpos = anchor.offset().top;
                    var mainnavpos = mainnav.offset().top + bottom;
                    var reveal = mainnav.offset().top + bottom + rightnav + 100;
                    var anchorheight = navigation.outerHeight();
                    var fixedbuttons = $('.fixed-buttons');

                    if (window_top > position) {
                        var anchorpos = anchor.offset().top;
                        var mainnavpos = mainnav.offset().top + bottom;
                        var reveal = mainnav.offset().top + bottom + rightnav + 100;
                    } else {
                        var anchorpos = anchor.offset().top + anchor.outerHeight();
                        var mainnavpos = mainnav.offset().top + bottom;
                        var reveal = mainnav.offset().top + bottom + rightnav + 100;
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
                        fixedbuttons.addClass('push');
                    } else {
                        navigation.removeClass('reveal');
                        fixedbuttons.removeClass('push');
                    }

                }

                sticky_nav();

                $(window).scroll(sticky_nav);

                $(window).resize(function() {
                    sticky_nav();
                });

                // Scroll to top of page
                $('a[href="#top"]').click(function() {
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                    return false;
                });
            }

        }
    }
});
/* jshint ignore:end */