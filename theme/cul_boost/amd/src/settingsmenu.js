/* jshint ignore:start */
define(['jquery', 'core/log'], function($, log) {
    return {
        init: function() {

            // Open/Close Settings Menu
            var settingsblock = $('#block-region-nav-settings');

            // Settings Menu Horizontal Feature
            // Init / re-init the menu
            $(document).ajaxComplete(function(event, xhr, settings) {

                // Settings Menu Horizontal Feature
                var branch = $('.tree_item.branch');
                var blocktree = $('.block_tree.list');
                var width = blocktree.outerWidth();
                var sibling = branch.siblings('ul');

                // Increase all branch list height to the tallest
                function maxheight(element) {
                    var maxheight = 0;
                    element.each(function() {
                        maxheight = ($(this).outerHeight() > maxheight ? $(this).height() : maxheight);
                    });
                    element.height(maxheight);
                    blocktree.height(maxheight);
                }

                // Events that create the horizontal navigation
                // by targeting the aria attributes
                function closenav(e) {
                    var rootnode = e.hasClass('root_node');
                    var children = e.siblings('ul').find('.tree_item.branch');
                    var sib = e.parent().siblings();
                    var level = e.parents('li.contains_branch').length * 2;

                    if (e.attr('aria-expanded') == 'true') {
                        level = level - 2;
                    }

                    children.attr('aria-expanded', 'false');
                    children.siblings('ul').attr('aria-hidden', 'true');
                    sib.find('.tree_item.branch').attr('aria-expanded', 'false');
                    sib.find('.tree_item.branch').siblings('ul').attr('aria-hidden', 'true');

                    blocktree.css('width', 'calc(' + width + 'px + ' + level + 'rem');
                }

                function fix_width() {
                    var openbranch = $('.tree_item.branch[aria-expanded="true"]');
                    var branches = openbranch.length * 2;
                    blocktree.css('width', 'calc(' + width + 'px + ' + branches + 'rem');
                }

                $('a.trigger').on('click', function() {

                    $(this).addClass('open');
                    settingsblock.addClass('show');
                    $('.overlay').addClass('show');
                    $('body').addClass('overflow-hidden');

                    var scrollto = settingsblock.parents('.right-navbar').hasClass('stick');
                    if (scrollto != true) {
                        $('html, body').animate({
                            scrollTop: settingsblock.offset().top
                        }, 200);
                    }

                    fix_width();

                    // Click the settings button to reset navigation tree
                    blocktree.animate({
                        scrollTop: 0
                    }, 0);
                });

                $('.overlay').on('click', function() {
                    settingsblock.removeClass('show');
                    $(this).removeClass('show');
                    $('body').removeClass('overflow-hidden');
                });

                // Close the settings menu with ESC key
                $(document).keyup(function(e) {
                    if (e.keyCode == 27) {
                        settingsblock.removeClass('show');
                        $('.overlay').removeClass('show');
                        $('body').removeClass('overflow-hidden');
                    }
                });

                // Click branch to open submenu and scroll to the top
                branch.on('click', function() {
                    closenav($(this));
                    blocktree.animate({
                        scrollTop: 0
                    }, 0);

                });

                // Call again just in case
                maxheight(sibling);
            });

        }
    }
});
/* jshint ignore:end */