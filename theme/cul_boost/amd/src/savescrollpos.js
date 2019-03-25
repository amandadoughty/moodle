/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            $('.editbutton button').on('click', function() {
                localStorage.setItem('scrollPosition', window.scrollY);
                console.log(localStorage.getItem('scrollPosition'));
            });

            function print_nav_timing_data() {
              // Use getEntriesByType() to just get the "navigation" events
              var perfEntries = performance.getEntriesByType("navigation");

              for (var i=0; i < perfEntries.length; i++) {
                var p = perfEntries[i];
                
                if (p.type == 'reload') {
                    localStorage.setItem('scrollPosition', null);
                }

                if (p.type == 'navigate') {
                    
                    if (localStorage.getItem('scrollPosition') !== null) {
                        window.scrollTo(0, localStorage.getItem('scrollPosition'));
                    }

                    localStorage.setItem('scrollPosition', null);
                }
              }
            }

            print_nav_timing_data();

        }
    }
});
/* jshint ignore:end */