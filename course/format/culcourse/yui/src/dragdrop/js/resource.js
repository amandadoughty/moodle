/**
 * Resource drag and drop.
 *
 * @class M.format_culcourse.dragdrop.interceptor
 * @constructor
 * @extends M.course.dragdrop.resource
 */

 var CSS = {
    ACTIONAREA: '.actions',
    ACTIVITY: 'activity',
    ACTIVITYINSTANCE: 'activityinstance',
    CONTENT: 'content',
    COURSECONTENT: 'course-content',
    EDITINGMOVE: 'editing_move',
    ICONCLASS: 'iconsmall',
    JUMPMENU: 'jumpmenu',
    LEFT: 'left',
    LIGHTBOX: 'lightbox',
    MOVEDOWN: 'movedown',
    MOVEUP: 'moveup',
    PAGECONTENT: 'page-content',
    RIGHT: 'right',
    SECTION: 'section',
    SECTIONBODY: 'sectionbody',
    SECTIONHOVEROPEN: 'sectionhoveropen',
    SECTIONADDMENUS: 'section_add_menus',
    SECTIONHANDLE: 'section-handle',
    SUMMARY: 'summary',
    SECTIONDRAGGABLE: 'sectiondraggable'
};
var DRAGINTERCEPTOR = function() {
    DRAGINTERCEPTOR.superclass.constructor.apply(this, arguments);
};
Y.extend(DRAGINTERCEPTOR, M.core.dragdrop, {
    initializer: function() {
        Y.DD.DDM.on('drag:drag', this.drag_drag, this);
        Y.DD.DDM.on('drop:hit', this.dropped, this);
        Y.DD.DDM.on('drop:miss', this.dropped, this);
        Y.DD.DDM.on('drag:start', this.drag_start, this);
 

        // Y.Node.all('li.' + CSS.SECTION).on("mouseenter", this.overFn);
                    
            
    },

    overFn: function(e) {
        var sectionnode = e.target;

        Y.log('overFn ' + sectionnode.getAttribute('id'));


        if(!sectionnode.hasClass(CSS.SECTIONHOVEROPEN)) {
            Y.log(CSS.SECTIONHOVEROPEN);
            sectionnode.addClass(CSS.SECTIONHOVEROPEN);
        }
    },

    drag_start: function(e) {
        var sectionnode = e.target;
        sectionnode.set('useShim', true);
    },

    drag_drag: function(e) {
        // Get our drag object
        var drag = e.target;

        drag.set('useShim', true);

        

        // If it is a resource then expand all sections.
        if(drag.get('dragNode').hasClass(CSS.ACTIVITY)) {
            Y.log('its a resource');

            Y.Node.all('li.' + CSS.SECTION).on("mouseenter", this.overFn);
        }



        // var sectionlistselector = M.course.format.get_section_selector(Y);
        // Y.log(sectionlistselector);
        // if (sectionlistselector) {
        //     sectionlistselector = '.' + CSS.COURSECONTENT + ' ' + sectionlistselector;


        //     Y.Node.all(sectionlistselector).each(function(resourcesnode) {
        //         var draggroups = resourcesnode.getData('draggroups');
        //         if (draggroups) {
        //             Y.log('this is a resource');
        //         }
        //     }, this);
        // }

        // ' li.' + CSS.ACTIVITY

        // Y.log(' li.' + CSS.ACTIVITY);

        // If it is a section then collapse all sections.

        // Y.log('started');
        
    },

    dropped: function() {
        // Get our drag object
        // var drag = e.target;
        // Revert all the section states.
        // Remove the mouseenter listener.


        Y.log('dropped');
        
    }

}, {
    NAME: 'format_culcourse-dragdrop-interceptor',
    ATTRS: {
        courseid: {
            value: null
        },
        ajaxurl: {
            value: 0
        },
        config: {
            value: 0
        }
    }
});

M.format_culcourse = M.format_culcourse || {};
M.format_culcourse.init_dragdrop_interceptor = function(params) {
    new DRAGINTERCEPTOR(params);
};

