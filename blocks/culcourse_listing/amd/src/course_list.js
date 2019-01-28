define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/url', 'core/yui',
        'core/modal_factory', 'core/modal_events', 'core/key_codes'],
    function($, ajax, templates, notification, str, url, Y, ModalFactory, ModalEvents, KeyCodes) {

// YUI.add('moodle-block_culcourse_listing-course_list', function(Y) {

//     var CLISTNAME = 'blocks_culcourse_listing_course_list';
//     var CLIST = function() {
//         CLIST.superclass.constructor.apply(this, arguments);
//     };

    var CSS = {
            HIDE: 'hide',
            FILTERON: 'filter-on',
            FILTEROFF: 'filter-off',
        };
    var SELECTORS = {
        CATEGORYNOTMANAGED: '.culcategory:not(.manage)',
        COURSEBOXLIST: '.block_culcourse_listing  .course_category_tree',
        COURSEBOX: '.culcoursebox',
        COURSEBOXLISTCOURSEBOX: '.course_category_tree .culcoursebox',
        FAVOURITELIST: '.favourite_list',
        FAVOURITECOURSEBOX: '.favourite_list .culcoursebox',
        FILTERLIST: '#culcourse_listing_filter .select',
        FILTERALERT: '.block_culcourse_listing .course_category_tree .divalert',
    };

// Y.extend(CLIST, Y.Base, {

    return {
        initializer: function(params) {
            this.setupcourses();
            var doc = Y.one(Y.config.doc);
            doc.delegate('change', this.filtercourses, SELECTORS.FILTERLIST, this, params.config);
        }
    };

    var setupcourses = function() {
        M.blocks_culcourse_listing.init_course();
    };

    var filtercourses = function(e, config) {
        var selectlist = Y.all(SELECTORS.FILTERLIST);
        var filteryear = selectlist.get('value')[0];
        var filterperiod = selectlist.get('value')[1];
        var all = M.util.get_string('all', 'block_culcourse_listing');
        filteryear = (filteryear == all || config.filterbyyear == 0) ? false : filteryear;
        filterperiod = (filterperiod == all || config.filterbyperiod == 0) ? false : filterperiod;
        // Save the choice.
        M.util.set_user_preference(e.target.get('id'), e.target.get('value'));
        // Set the class for the course category list.
        var container = Y.one(SELECTORS.COURSEBOXLIST);

        if (filteryear == false && filterperiod == false ) {
            container.removeClass(CSS.FILTERON);
            container.addClass(CSS.FILTEROFF);
        } else {
            container.removeClass(CSS.FILTEROFF);
            container.addClass(CSS.FILTERON);
            // Set the text for the heading.
            var heading = Y.one(SELECTORS.FILTERALERT);
            var filterstring = '';

            if (filteryear != false) {
                filterstring = filteryear;
            }
            if (filteryear != false && filterperiod != false) {
                filterstring = filterstring + M.util.get_string('and', 'block_culcourse_listing');
            }
            if (filterperiod != false) {
                filterstring = filterstring + M.util.get_string(filterperiod, 'block_culcourse_listing');
            }

            filterstring = M.util.get_string('divalert', 'block_culcourse_listing', filterstring);
            heading.set('innerHTML', filterstring);
        }

        // Hide all the courses and categories.
        var courselist = Y.all(SELECTORS.COURSEBOXLISTCOURSEBOX);
        var categorylist = Y.all(SELECTORS.CATEGORYNOTMANAGED);
        courselist.removeClass(CSS.HIDE);
        categorylist.removeClass(CSS.HIDE);
        courselist.setStyle('display', 'block');
        categorylist.setStyle('display', 'none');

        // Hide the filtered out courses.
        var filterbyregex = function(node){
            var courseyear = Y.Array(node.getData('year'));
            var courseperiod = Y.Array(node.getData('period'));

            if (filteryear && filteryear != courseyear) {
                node.setStyle('display', 'none');
            }
            if (filterperiod && filterperiod != courseperiod) {
                node.setStyle('display', 'none');
            }
        };

        // TODO Hide the filtered out courses.
        var filterbydate = function(node){
            var courseyear = Y.Array(node.getData('year'));
            var courseperiod = Y.Array(node.getData('period'));

            if (filteryear && filteryear != courseyear) {
                node.setStyle('display', 'none');
            }
            if (filterperiod && filterperiod != courseperiod) {
                node.setStyle('display', 'none');
            }
        };

        if (config.filtertype == 'regex') {
            courselist.each(filterbyregex);
        } else if (config.filtertype == 'date') {
            courselist.each(filterbydate);
        }

        // Hide categories that have no unhidden children.
        var filtercategory = function(node){
            var children = node.all(SELECTORS.COURSEBOX);
            children.each(function(child){
                if (child.getStyle('display') !== 'none') {
                    node.setStyle('display', 'block');
                }
            });
        };

        categorylist.each(filtercategory);
    };

// }, {
//     NAME : CLISTNAME,
//     ATTRS : {
//         config : {
//             value : null
//         }
//     }
//     });

//     M.blocks_culcourse_listing = M.blocks_culcourse_listing || {};
//     M.blocks_culcourse_listing.init_course_list = function(params) {
//         return new CLIST(params);
//     }

// }, '@VERSION@', {
//     requires:['base', 'moodle-block_culcourse_listing-course',
//                 'moodle-block_culcourse_listing-category']
});
