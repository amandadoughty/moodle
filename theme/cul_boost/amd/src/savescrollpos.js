/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            var continuescrolling = false;

            function uniqueClass() {
                var uniqueClass = $('body').attr('class').match(/course-\d+/)[0];
                return uniqueClass;
            }

            function scroll_to_section() {

                var id = localStorage.getItem('scrollPosition-' + uniqueClass());

                if (localStorage.getItem('scrollPosition-' + uniqueClass()) !== null) {
                    var nav = $('.nav-wrap').outerHeight();
                    var id = '#' + localStorage.getItem('scrollPosition-' + uniqueClass());

                    localStorage.setItem('savedscrollPosition-' + uniqueClass(), id);
                    var scrollsection = localStorage.getItem('savedscrollPosition-' + uniqueClass());

                    $('html, body').animate({
                        scrollTop: $(scrollsection).offset().top - 55
                    }, 400);
                }

                continuescrolling = true;
            }

            $(document).on('culcoursesectiontoggle', function() {
                scroll_to_section();
            });

            $(window).scroll(function() {

                if (continuescrolling == true) {
                    var scroll = $(window).scrollTop();
                    var section = $('.course-content ul.culcourse li.section');
                    var sectionheight = section.outerHeight();
                    var nav = $('.nav-wrap').outerHeight();
                    section.each(function() {
                        var sectiontop = $(this).offset().top - nav;
                        if (scroll > sectiontop &&
                            scroll < (sectiontop + sectionheight)) {
                            localStorage.setItem('scrollPosition-' + uniqueClass(), $(this).attr('id'));
                        } else if (scroll < ($('.course-content ul.culcourse #section-0').offset().top - nav)) {
                            localStorage.setItem('scrollPosition-' + uniqueClass(), 'javascript:void(0);');
                        }
                    });
                }

            });

        }
    }
});
/* jshint ignore:end */