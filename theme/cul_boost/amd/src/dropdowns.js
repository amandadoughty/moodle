/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
                if (!$(this).next().hasClass('show')) {
                    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
                }

                var $subMenu = $(this).next(".dropdown-menu");

                $(this).parent().toggleClass('subopen');
                $subMenu.toggleClass('show');

                $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                    $('.dropdown-submenu .show').removeClass("show");
                    $('.dropdown-submenu').removeClass('subopen');
                });

                return false;
            });

        }
    }
});
/* jshint ignore:end */