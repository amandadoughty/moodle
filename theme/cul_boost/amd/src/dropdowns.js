/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {
            $('.dropdown-menu a.dropdown-toggle').on('click', function(e) {
                // If the immediately following sibling (.dropdown-menu)
                // is not visible.
                if (!$(this).next().hasClass('show')) {
                    // Get (.dropdown-menu) ancestors, and find all (.subopen)
                    // in the first one. 
                    $(this).parents('.dropdown-menu').first().find('.show').removeClass('show');
                }

                // If the immediate parent (.dropdown-menu)
                // is not visible.
                if (!$(this).parent().hasClass('subopen')) {
                    // Get (.dropdown-menu) ancestors, and find all (.show)
                    // in the first one. 
                    $(this).parents('.dropdown-menu').first().find('.subopen').removeClass('subopen');
                }

                $(this).next('.dropdown-menu').toggleClass('show');
                $(this).parent().toggleClass('subopen');                

                $(this).parents('.nav-item.dropdown.show').on('hidden.bs.dropdown', function(e) {
                    $('.dropdown-menu').removeClass('show');
                    $('.dropdown-submenu').removeClass('subopen');
                });

                return false;
            });
        }
    }
});
/* jshint ignore:end */