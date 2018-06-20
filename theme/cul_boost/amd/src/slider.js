/* jshint ignore:start */
define(['jquery', 'theme_cul_boost/slick'], function($, slick) {
    return {
        init: function(opts) {
            $('.slider').each(function() {

                var slider = $(this);
                var time = opts.duration * 1;
                var bar,
                    slider,
                    isPause,
                    isBtnPressed = false,
                    tick,
                    percentTime;


                function dashslider() {
                    slider.slick({
                        dots: false,
                        arrows: true,
                        speed: 500,
                        pauseOnDotsHover: true,
                        appendDots: $('.slidercontainer .slide-controls .container-fluid'),
                        appendArrows: $('.slidercontainer .slide-controls .container-fluid'),
                        slide: '.slide',
                        prevArrow: '<button type="button" data-role="none" class="slick-prev d-inline-flex align-items-center justify-content-center btn btn-primary" name="Next" aria-label="Previous" tabindex="0" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i></button>',
                        nextArrow: '<button type="button" data-role="none" class="slick-next d-inline-flex align-items-center justify-content-center btn btn-primary" name="Next" aria-label="Next" tabindex="0" role="button"><i class="fa fa-angle-right" aria-hidden="true"></i></button>',
                    });
                }

                dashslider();

                $('.pause-button').on('click', function() {
                    isPause = true;
                    isBtnPressed = true;
                    $('.pause-button').css('display', 'none');
                    $('.play-button').css('display', 'block');
                });

                $('.play-button').on('click', function() {
                    isPause = false;
                    isBtnPressed = false;
                    $('.play-button').css('display', 'none');
                    $('.pause-button').css('display', 'block');
                });

                bar = $('.slider-progress .progress');

                slider.on({
                    mouseenter: function() {
                        isPause = true;
                    },
                    mouseleave: function() {
                        if (isBtnPressed == false) {
                            isPause = false;
                        }
                    }
                });

                function startProgressbar() {
                    resetProgressbar();
                    percentTime = 0;
                    isPause = false;
                    tick = setInterval(interval, 10);
                }

                function interval() {
                    if (isPause === false) {
                        percentTime += 1 / (time + 0.1);
                        bar.css({
                            width: percentTime + "%"
                        });
                        if (percentTime >= 100) {
                            slider.slick('slickNext');
                            startProgressbar();
                        }
                    }
                }

                function resetProgressbar() {
                    bar.css({
                        width: 0 + '%'
                    });
                    clearTimeout(tick);
                }

                startProgressbar();

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
                            prevArrow: '<button type="button" data-role="none" class="slick-prev d-inline-flex align-items-center justify-content-center btn btn-primary" name="Next" aria-label="Previous" tabindex="0" role="button"><i class="fa fa-angle-left" aria-hidden="true"></i></button>',
                            nextArrow: '<button type="button" data-role="none" class="slick-next d-inline-flex align-items-center justify-content-center btn btn-primary" name="Next" aria-label="Next" tabindex="0" role="button"><i class="fa fa-angle-right" aria-hidden="true"></i></button>',
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