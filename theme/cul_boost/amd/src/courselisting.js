/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.block.block_culcourse_listing').each(function() {

                var clcontent = $(this).find('.card-text.content');
                var tabitem = $(this).find('.nav-item');
                var tabcontent = $(this).find('.tab-pane');

                $('<div id="courselistingcontent" class="tab-content"></div>').prependTo(clcontent);
                $('<ul id="courselistingnav" class="nav nav-pills"></div>').prependTo(clcontent);

                tabitem.each(function() {
                    $(this).appendTo('#courselistingnav');
                });

                tabcontent.each(function() {
                    $(this).appendTo('#courselistingcontent');
                });

                // Open/Close find a module overlay
                var findblocks = $('.findblocks-wrap');
                var body = $('body');

                $('.initialsearch').on('click', function(e) {
                    findblocks.addClass('show');
                    body.addClass('overflow-hidden');
                    $('.allcourses-link').trigger('click');
                    e.preventDefault();
                    e.stopPropagation();
                    setTimeout(function() {
                        $('input#ac-input').trigger('click');
                        $('input#ac-input').focus();
                    }, 100);
                });

                $('.close-icon').on('click', function() {
                    findblocks.removeClass('show');
                    body.removeClass('overflow-hidden');
                });

                $(document).keyup(function(e) {
                    if (e.keyCode == 27) {
                        findblocks.removeClass('show');
                        body.removeClass('overflow-hidden');
                    }
                });

                $('.allmodules-btn').on('click', function() {
                    findblocks.addClass('show');
                    body.addClass('overflow-hidden');
                    $('.allcourses-link').trigger('click');
                });

                $('.favourites-btn').on('click', function() {
                    findblocks.addClass('show');
                    body.addClass('overflow-hidden');
                    $('.favourites-link').trigger('click');
                });

            });

        }
    }
});
/* jshint ignore:end */