/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.bootstrapelements .modal').each(function() {
                $(this).appendTo('body');
            });

            $('.bootstrapelements.modal').each(function() {
                    $(this).on('shown.bs.modal', function () {
                    $(this).find('button').trigger('focus');
                })
            });

        }
    }
});
/* jshint ignore:end */