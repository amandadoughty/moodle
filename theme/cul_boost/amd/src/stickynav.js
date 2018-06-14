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
                    var navigation = $('.nav-wrap');
                    var navheight = navigation.outerHeight();
                    var footer = $('#settingsnav+.footer').outerHeight();

                    var div_top = $('#navbar-anchor').offset().top;
                    var fixed = '';

                    function ifnavreveal() {
                        if (navigation.hasClass('reveal')) {
                            div_top = $('#navbar-anchor').offset().top - navheight;
                            fixed = ' fixed';
                        } else {
                            navbar.removeClass('fixed');
                        }
                    }

                    function settingsheight() {
                        var settings = $('.block_tree.list');
                        var maxheight = $(window).innerHeight() - (anchorheight + 20) - footer;

                        if (navigation.hasClass('reveal')) {
                            var maxheight = $(window).innerHeight() - (anchorheight + navheight + 20) - footer;
                        }

                        settings.css('max-height', maxheight);
                    }

                    ifnavreveal();
                    settingsheight();

                    var scroll = $(window).scrollTop();

                    if (scroll > position) {
                        ifnavreveal();
                    } else {
                        ifnavreveal();
                    }

                    position = scroll;

                    if (window_top > div_top) {
                        navbar.addClass('stick' + fixed);
                        $('#navbar-anchor').css({
                            'height': anchorheight,
                            'width': anchorwidth
                        });
                    } else {
                        navbar.removeClass('stick' + fixed);
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