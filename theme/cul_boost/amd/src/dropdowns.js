/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {
            // We have nested dropdowns. Bootstrap toggles all the
            // 'show' classes.
            // $('.dropdown').on('hidden.bs.dropdown', function(e) {
            //     console.log('hide');
            //     // $(this).parents('.dropdown-sub').removeClass('show');
            //     $(this).children('.dropdown-submenu:first').removeClass('showsub');


            // });
            // $('.dropdown').on('shown.bs.dropdown', function(e) {
            //     console.log('show');
            //     // $(this).parents('.dropdown-sub').addClass('show');
            //     $(this).children('.dropdown-submenu:first').addClass('showsub');

            // });



            $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
                if (!$(this).next().hasClass('show')) {
                    $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
                }

                var $subMenu = $(this).next(".dropdown-menu");

                $(this).parent().toggleClass('subopen');
                $subMenu.toggleClass('show');

                $(this).parents('.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                    $('.dropdown-submenu .show').removeClass("show");
                    $('.dropdown-submenu').removeClass('subopen');
                });

                return false;
            });
        }
    }
});
/* jshint ignore:end */