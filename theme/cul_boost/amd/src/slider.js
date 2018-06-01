/* jshint ignore:start */
define(['jquery', 'theme_cul_boost/slick'], function($, slick) {
    return {
        init: function() {
            $('.slider').each(function() {

                var slider = $(this);

                function dashslider() {
                    slider.slick({
                        dots: false,
                        arrows: true,
                        speed: 500,
                        autoplay: true,
                        autoplaySpeed: 5000,
                        appendDots: $('.slidercontainer .slide-controls .container-fluid'),
                        appendArrows: $('.slidercontainer .slide-controls .container-fluid'),
                        slide: '.slide',
                        prevArrow: '<button type="button" data-role="none" class="slick-prev btn btn-primary" name="Next" aria-label="Previous" tabindex="0" role="button"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',
                        nextArrow: '<button type="button" data-role="none" class="slick-next btn btn-primary" name="Next" aria-label="Next" tabindex="0" role="button"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',
                    });
                }

                dashslider();

            });
            $('#block-region-dash-full .block_culupcoming_events .culupcoming_events ul').each(function() {

                var eventwrap = $(this);
                var events = eventwrap.children('li');

                if (events.length) {
                    function eventslider(e) {
                        e.slick({
                            dots: false,
                            arrows: true,
                            speed: 500,
                            autoplay: false,
                            slidesToShow: 1,
                            centerMode: true,
                            variableWidth: true,
                            prevArrow: '<button type="button" data-role="none" class="slick-prev btn btn-primary" name="Next" aria-label="Previous" tabindex="0" role="button"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',
                            nextArrow: '<button type="button" data-role="none" class="slick-next btn btn-primary" name="Next" aria-label="Next" tabindex="0" role="button"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>',
                            appendArrows: $('#block-region-dash-full .block.block_culupcoming_events .culupcoming_events')
                        });
                    }

                    eventslider($(this));

                    $('.block_culupcoming_events_reload').on('click', function() {

                        var eventouter = eventwrap.parent();
                        eventouter.addClass('loading');
                        $('<i class="fa fa-circle-o-notch fa-spin"></i>').appendTo(eventouter);

                        setTimeout(function() {
                            eventouter.removeClass('loading');
                            eventouter.find('.fa.fa-circle-o-notch').remove();
                            eventwrap.slick('refresh');
                        }, 1000);
                    });
                }

            });
        }
    }
});
/* jshint ignore:end */