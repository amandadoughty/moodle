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
                    $(this).on ('keydown', function (e) {
                        var $buttons = $(this).find (':enabled');
                        var first = $buttons.first()[0];
                        var last = $buttons.last()[0];

                        if (e.keyCode == 9) {
                            if (!e.shiftKey && e.target == last) {
                                $(first).focus ();
                            } else if (e.shiftKey && e.target == first) {
                                $(last).focus ();
                            } else {
                                return true;
                            } // if
                            return false;
                        } // if tab
                        return true;
                    }); // keydown
                })
                
            });

        }
    }
});
/* jshint ignore:end */

