define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/url', 'core/yui',
        'core/modal_factory', 'core/modal_events', 'core/key_codes'],
    function($, ajax, templates, notification, str, url, Y, ModalFactory, ModalEvents, KeyCodes) {

// YUI.add('moodle-block_culcourse_listing-category', function(Y) {

//     var CATNAME = 'blocks_culcourse_listing_category';
//     var CAT = function() {
//         CAT.superclass.constructor.apply(this, arguments);
//     };

    var CSS = {
        CONTENTNODE: 'content',
        COLLAPSEALL: 'culcollapse-all',
        DISABLED: 'disabled',
        LOADED: 'loaded',
        NOTLOADED: 'notloaded',
        SECTIONCOLLAPSED: 'collapsed',
        HASCHILDREN: 'with_children',
        HIDE: 'hide'
    };
    var SELECTORS = {
        LOADEDTREES: '.with_children.loaded',
        CONTENTNODE: '.content',
        CATEGORYCHILDLINK: '.culcategory .info .categoryname a',
        CATEGORYLISTENLINK: '.culcategory .info .categoryname',
        CATEGORYSPINNERLOCATION: '.categoryname',        
        CATEGORYWITHCOLLAPSEDLOADEDCHILDREN: '.culcategory.with_children.loaded.collapsed',
        CATEGORYWITHMAXIMISEDLOADEDCHILDREN: '.culcategory.with_children.loaded:not(.collapsed)',
        COLLAPSEEXPAND: '.culcollapseexpand',
        COURSEBOX: '.culcoursebox',
        COURSEBOXLISTENLINK: '.culcoursebox .moreinfo',
        COURSEBOXSPINNERLOCATION: '.coursename a',
        COURSECATEGORYTREE: '.course_category_tree',
        PARENTWITHCHILDREN: '.culcategory',
        SPINNER: '.progress-icon'
    };
    var TYPE_CATEGORY = 0;
    var TYPE_COURSE = 1;
    var URL = M.cfg.wwwroot + '/blocks/culcourse_listing/category_ajax.php';

    // Y.extend(CAT, Y.Base, {

    var BLOCKCONFIG = null;



    /**
     * Set up keyboard expansion for course content.
     *
     * This includes setting up the delegation but also adding the nodes to the
     * tabflow.
     *
     * @method setup_keyboard_listeners
     */
    var setup_keyboard_listeners = function() {
        Y.one(Y.config.doc).delegate('key', toggle_category_expansion, 'enter', SELECTORS.CATEGORYLISTENLINK, this);
        Y.one(Y.config.doc).delegate('key', toggle_coursebox_expansion, 'enter', SELECTORS.COURSEBOXLISTENLINK, this);
        Y.one(Y.config.doc).delegate('key', collapse_expand_all, 'enter', SELECTORS.COLLAPSEEXPAND, this);
    };

    var toggle_coursebox_expansion = function(e) {
        var courseboxnode;

        // Grab the parent category container - this is where the new content will be added.
        courseboxnode = e.target.ancestor(SELECTORS.COURSEBOX, true);
        e.preventDefault();

        if (courseboxnode.hasClass(CSS.LOADED)) {
            // We've already loaded this content so we just need to toggle the view of it.
            run_expansion(courseboxnode);
            return;
        }

        _toggle_generic_expansion({
            parentnode: courseboxnode,
            childnode: courseboxnode.one(SELECTORS.CONTENTNODE),
            spinnerhandle: SELECTORS.COURSEBOXSPINNERLOCATION,
            spinner: SELECTORS.SPINNER,
            data: {
                sesskey : M.cfg.sesskey,
                courseid: courseboxnode.getData('courseid'),
                type: TYPE_COURSE
            }
        });
    };

    var toggle_category_expansion = function(e) {

        var categorynode,
            categoryid,
            depth,
            categorylink;

        if (e.target.test('a') || e.target.test('img')) {
            // Return early if either an anchor or an image were clicked.
            //return;
        }

        // Grab the parent category container - this is where the new content will be added.
        categorynode = e.target.ancestor(SELECTORS.PARENTWITHCHILDREN, true);

        if (!categorynode.hasClass(CSS.HASCHILDREN)) {
            // Nothing to do here - this category has no children.
            return;
        }

        if (categorynode.hasClass(CSS.LOADED)) {
            // We've already loaded this content so we just need to toggle the view of it.
            run_expansion(categorynode);
            return;
        }

        // We use Data attributes to store the category.
        categoryid = categorynode.getData('categoryid');
        depth = categorynode.getData('depth');

        if (typeof categoryid === "undefined" || typeof depth === "undefined") {
            return;
        }

        _toggle_generic_expansion({
            parentnode: categorynode,
            childnode: categorynode.one(SELECTORS.CONTENTNODE),
            spinnerhandle: SELECTORS.CATEGORYSPINNERLOCATION,
            spinner: SELECTORS.SPINNER,
            data: {
                sesskey: M.cfg.sesskey,
                categoryid: categoryid,
                depth: depth,
                showcourses: categorynode.getData('showcourses'),
                type: TYPE_CATEGORY
            }
        });
    };

    /**
     * Wrapper function to handle toggling of generic types.
     *
     * @method _toggle_generic_expansion
     * @private
     * @param {Object} config
     */
    var _toggle_generic_expansion = function(config) {
        var self = this;
        // Using amd instead of Y.Promise just because there is no point
        // learning new YUI techniques. And the YUI in this plugin may be
        // replaced entirely with amd in future.
        require(['jquery', 'core/str', 'core/templates', 'core/notification'], function($, Str, Templates, Notification) {
            Str.get_string('loading', 'core')
                .then(function(string) {
                    return Templates.renderPix('i/progressbar', 'core', string);
                })
                .then(function(html) {
                    var node = config.parentnode.one(config.spinnerhandle);
                    var spinnernode = $(html).addClass('progress-icon');
                    node.append(spinnernode);
                    return spinnernode;
                })
                .then(function(spinnernode) {
                    years = {};
                    periods = {};
                    yearselect = $('#culcourse_listing_filter_year');
                    periodselect = $('#culcourse_listing_filter_period');

                    if (yearselect) {
                        $.each(yearselect.attr('options'), function(key, value) {
                            years[value] = key;
                        });
                        config.data.years = JSON.stringify(years);
                    }

                    if (periodselect) {
                        $.each(periodselect.attr('options'), function(key, value) {
                            periods[value] = key;
                        });
                        config.data.periods = JSON.stringify(periods);
                    }

                    var args = {
                            parentnode: config.parentnode,
                            childnode: config.childnode,
                            spinnernode: spinnernode
                        };

                    $.ajax({
                        url: URL,
                        method: 'POST',
                        data: config.data,
                        context: self,
                        success: function(response) {
                            self.process_results(response, args);
                        }
                    })
 
                }).fail(Notification.exception);
        });
    };

    /**
     * Apply the animation on the supplied node.
     *
     * @method run_expansion
     * @private
     * @param {Node} categorynode The node to apply the animation to
     */
    var run_expansion = function(categorynode) {
        var categorychildren = categorynode.one(SELECTORS.CONTENTNODE),
            self = this,
            ancestor = categorynode.ancestor(SELECTORS.COURSECATEGORYTREE);

        // Add our animation to the categorychildren.
        add_animation(categorychildren);

        // If we already have the class, remove it before showing otherwise we perform the
        // animation whilst the node is hidden.
        if (categorynode.hasClass(CSS.SECTIONCOLLAPSED)) {
            // To avoid a jump effect, we need to set the height of the children to 0 here before removing the SECTIONCOLLAPSED class.
            categorychildren.setStyle('height', '0');
            categorynode.removeClass(CSS.SECTIONCOLLAPSED);
            categorynode.setAttribute('aria-expanded', 'true');
            categorychildren.fx.set('reverse', false);
        } else {
            categorychildren.fx.set('reverse', true);
            categorychildren.fx.once('end', function(e, categorynode) {
                categorynode.addClass(CSS.SECTIONCOLLAPSED);
                categorynode.setAttribute('aria-expanded', 'false');
            }, this, categorynode);
        }

        categorychildren.fx.once('end', function(e, categorychildren) {
            // Remove the styles that the animation has set.
            categorychildren.setStyles({
                height: '',
                opacity: ''
            });

            // To avoid memory gobbling, remove the animation. It will be added back if called again.
            this.destroy();
            self.update_collapsible_actions(ancestor);
        }, categorychildren.fx, categorychildren);

        // Now that everything has been set up, run the animation.
        categorychildren.fx.run();
    };

    var collapse_expand_all = function(e) {
        // The collapse/expand button has no actual target but we need to prevent it's default
        // action to ensure we don't make the page reload/jump.
        e.preventDefault();

        if (e.currentTarget.hasClass(CSS.DISABLED)) {
            // The collapse/expand is currently disabled.
            return;
        }

        var ancestor = e.currentTarget.ancestor(SELECTORS.COURSECATEGORYTREE);
        if (!ancestor) {
            return;
        }

        var collapseall = ancestor.one(SELECTORS.COLLAPSEEXPAND);
        if (collapseall.hasClass(CSS.COLLAPSEALL)) {
            collapse_all(ancestor);
        } else {
            expand_all(ancestor);
        }
        update_collapsible_actions(ancestor);
    };

    var expand_all = function(ancestor) {
        var finalexpansions = [];

        ancestor.all(SELECTORS.CATEGORYWITHCOLLAPSEDLOADEDCHILDREN)
            .each(function(c) {
            if (c.ancestor(SELECTORS.CATEGORYWITHCOLLAPSEDLOADEDCHILDREN)) {
                // Expand the hidden children first without animation.
                c.removeClass(CSS.SECTIONCOLLAPSED);
                c.all(SELECTORS.LOADEDTREES).removeClass(CSS.SECTIONCOLLAPSED);
            } else {
                finalexpansions.push(c);
            }
        }, this);

        // Run the final expansion with animation on the visible items.
        Y.all(finalexpansions).each(function(c) {
            run_expansion(c);
        }, this);

    };

    var collapse_all = function(ancestor) {
        var finalcollapses = [];

        ancestor.all(SELECTORS.CATEGORYWITHMAXIMISEDLOADEDCHILDREN)
            .each(function(c) {
            if (c.ancestor(SELECTORS.CATEGORYWITHMAXIMISEDLOADEDCHILDREN)) {
                finalcollapses.push(c);
            } else {
                // Collapse the visible items first
                run_expansion(c);
            }
        }, this);

        // Run the final collapses now that the these are hidden hidden.
        Y.all(finalcollapses).each(function(c) {
            c.addClass(CSS.SECTIONCOLLAPSED);
            c.all(SELECTORS.LOADEDTREES).addClass(CSS.SECTIONCOLLAPSED);
        }, this);
    };

    var update_collapsible_actions = function(ancestor) {
        if (!ancestor) {
            // We will not have an ancestor when a course in the favoutites list is expanded
            return;
        }

        var foundmaximisedchildren = false,
        // Grab the anchor for the collapseexpand all link.
        togglelink = ancestor.one(SELECTORS.COLLAPSEEXPAND);

        if (!togglelink) {
            // We should always have a togglelink but ensure.
            return;
        }

        // Search for any visibly expanded children.
        ancestor.all(SELECTORS.CATEGORYWITHMAXIMISEDLOADEDCHILDREN).each(function(n) {
            // If we can find any collapsed ancestors, skip.
            if (n.ancestor(SELECTORS.CATEGORYWITHCOLLAPSEDLOADEDCHILDREN)) {
                return false;
            }
            foundmaximisedchildren = true;
            return true;
        }, this);

        togglelink.removeClass(CSS.HIDE);

        if (foundmaximisedchildren) {
            // At least one maximised child found. Show the collapseall.
            togglelink.setHTML(M.util.get_string('collapseall', 'moodle'))
                .addClass(CSS.COLLAPSEALL)
                .removeClass(CSS.DISABLED);
        } else {
            // No maximised children found but there are collapsed children. Show the expandall.
            togglelink.setHTML(M.util.get_string('expandall', 'moodle'))
                .removeClass(CSS.COLLAPSEALL)
                .removeClass(CSS.DISABLED);
        }
    };

    /**
     * Process the data returned by $.ajax.
     * This includes appending it to the relevant part of the DOM, and applying our animation.
     *
     * @method process_results
     * @private
     * @param {Object} data The Response returned by $.ajax
     * @param {Object} args The additional arguments provided by $.ajax
     */
    var process_results = function(data, args) {
        var newnode;

        try {
            if (data.error) {
                return new M.core.ajaxException(data);
            }
        } catch (e) {
            return new M.core.exception(e);
        }

        // Insert the returned data into a new Node.
        newnode = Y.Node.create(data.content);

        // Append to the existing child location.
        args.childnode.appendChild(newnode);

        // Now that we have content, we can swap the classes on the toggled container.
        args.parentnode
            .addClass(CSS.LOADED)
            .removeClass(CSS.NOTLOADED);

        // Toggle the open/close status of the node now that it's content has been loaded.
        run_expansion(args.parentnode);

        // Update the filters.
        if (data.filterform) {
            filterform = Y.one('.filter');
            filterform.setHTML(data.filterform);
        }

        // Remove the spinner now that we've started to show the content.
        if (args.spinnernode) {
            args.spinnernode.hide();
        }
    };

    /**
     * Add our animation to the Node.
     *
     * @method add_animation
     * @private
     * @param {Node} childnode
     */
    var add_animation = function(childnode) {
        if (typeof childnode.fx !== "undefined") {
            // The animation has already been plugged to this node.
            return childnode;
        }

        childnode.plug(Y.Plugin.NodeFX, {
            from: {
                height: 0,
                opacity: 0
            },
            to: {
                // This sets a dynamic height in case the node content changes.
                height: function(node) {
                    // Get expanded height (offsetHeight may be zero).
                    return node.get('scrollHeight');
                },
                opacity: 1
            },
            duration: 0.2
        });

        return childnode;
    };

    return {
        /**
         * Set up the category expander.
         *
         * No arguments are required.
         *
         * @method init
         */
        initializer: function(params) {
            this.BLOCKCONFIG = params.config;
            var doc = Y.one(Y.config.doc);
            doc.delegate('click', function(e) {e.preventDefault();}, SELECTORS.CATEGORYCHILDLINK, this);
            doc.delegate('click', toggle_category_expansion, SELECTORS.CATEGORYLISTENLINK, this);
            doc.delegate('click', toggle_coursebox_expansion, SELECTORS.COURSEBOXLISTENLINK, this);
            doc.delegate('click', collapse_expand_all, SELECTORS.COLLAPSEEXPAND, this);

            // Only set up they keyboard listeners when tab is first pressed - it
            // may never happen and modifying the DOM on a large number of nodes
            // can be very expensive.
            doc.once('key', setup_keyboard_listeners, 'tab', this);
        }
    };    
// }, {
//         ATTRS : {
//             config : {
//                 value : null
//             }
//         }
//     });

//     M.blocks_culcourse_listing = M.blocks_culcourse_listing || {};
//     M.blocks_culcourse_listing.init_category = function(params) {
//         return new CAT(params);
//     }

// }, '@VERSION@', {
//     requires:['base', 'node', 'event-key', 'io-base', 'json-parse', 'json-stringify', 'moodle-core-notification', 'anim-node-plugin', 'promise']
});