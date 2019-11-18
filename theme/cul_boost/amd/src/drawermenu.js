/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            var width = $(window).width();
            var plugin = this;

            $('.navbar-toggle').on('click', function() {
                plugin.toggleDrawer();
            });

            $('.drawer .toggler').on('click touchmove', function() {
                plugin.toggleDrawer();
            });

            // AD - CMDLTWO-1641 My Modules menu regression.
            $('.mainmenu-wrap #theme-cul_boost-modulemenu .dropdown-item a[href*="#"]').on('click', function() {
                plugin.toggleDrawer();
            });

            $('.drawer .usermenu_header').on('click touchmove', function() {
                $('.mainmenu-wrap').collapse('show');
                $("html").addClass('navopen');
            });

            $('.navbar-toggle').on('click touchmove', function(e) {
                if ($(e.target).closest('.navbar-collapse').length == 0) {
                    // click happened outside of .navbar, so hide
                    var opened = $('.navbar-collapse').hasClass('collapse show');
                    if (opened === true) {
                        plugin.toggleDrawer();
                    }
                }
            });

        },
        toggleDrawer: function() {
            if ($("html").hasClass('navopen')) {
                $("html").removeClass('navopen');
                $('.drawer .block .body, .usermenu_content, .mainmenu-wrap').collapse('hide');
            } else {
                $('.mainmenu-wrap').collapse('show');
                $("html").addClass('navopen');
            }
        }
    }
});
/* jshint ignore:end */