YUI.add('moodle-format_culcourse-dragdrop', function (Y, NAME) {

/**
 * Dash link drag and drop.
 *
 * @class M.format_culcourse_dragdrop
 */

var CSS = {
        ACTIONAREA: 'dash-edit',
        ACTIVITYLINK: 'activitylink',
        ACTIVITYLINKCONTAINER: 'activitylinks',
        ACTIVITYLINKDRAGGABLE: 'activitylinkdraggable',
        MOVE: 'dash-move',
        QUICKLINK: 'quicklink',
        QUICKLINKCONTAINER: 'quicklinks',
        QUICKLINKDRAGGABLE: 'quicklinkdraggable'
    };    /**
 * Quick link drag and drop.
 *
 * @class M.format_culcourse_dragdrop
 * @constructor
 */

var QUICKLINK = function() {
    QUICKLINK.superclass.constructor.apply(this, arguments);
};

Y.extend(QUICKLINK, M.core.dragdrop, {

    goingLeft: null,

    initializer: function() {
        
        // Set group for parent class.
        this.groups = [CSS.QUICKLINKDRAGGABLE];
        // Initialise quicklinks dragging.
        this.quicklinklistselector = '.' + CSS.QUICKLINK;
        
        if(Y.Node.all(this.quicklinklistselector).size()) {
            this.setup_for_quicklink(this.quicklinklistselector);
            this.samenodeclass = CSS.QUICKLINK;
            this.parentnodeclass = CSS.QUICKLINKCONTAINER;

            // Make each li element in the lists of quicklinks draggable.
            var del = new Y.DD.Delegate({
                container: '.' + CSS.QUICKLINKCONTAINER,
                nodes: '.' + CSS.QUICKLINKDRAGGABLE,
                target: true,
                handles: ['.' + CSS.MOVE],
                dragConfig: {groups: this.groups}
            });
            del.dd.plug(Y.Plugin.DDProxy, {
                // Don't move the node at the end of the drag.
                moveOnEnd: false,
                cloneNode: true
            });
            del.dd.plug(Y.Plugin.DDConstrained, {
                // Keep it inside the ul.
                constrain: '.' + CSS.QUICKLINKCONTAINER
            });
            del.dd.plug(Y.Plugin.DDWinScroll);
        }
    },

    /**
     * Apply dragdrop features to the specified selector or node that refers to quicklink(s)
     *
     * @method setup_for_quicklink
     * @param {String} baseselector The CSS selector or node to limit scope to
     */
    setup_for_quicklink: function(baseselector) {
        Y.Node.all(baseselector).each(function(quicklinknode) {
            // Replace move icons.
            var move = quicklinknode.one('a' + '.' + CSS.MOVE);
            if (move) {
                move.replace(this.get_drag_handle(M.util.get_string('movequicklink', 'format_culcourse'),
                             CSS.MOVE, CSS.ICONCLASS, true));
            }
        }, this);
    },

    /*
     * Drag-dropping related functions
     */
    drag_start: function(e) {
        // Get our drag object.
        var drag = e.target;
        // Set some styles here.
        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
    },

    drag_drag: function(e) {
        // Core dragdrop checks for goingUp but our list is fluid
        // so we also need to check goingLeft.
        var drag = e.target,
            info = e.info;

        // Check that drag object belongs to correct group.
        if (!this.in_group(drag)) {
            return;
        }

        // Note, we test both < and > situations here. We don't want to
        // effect a change in direction if the user is only moving up
        // and down with no X position change.

        // Detect changes in the position relative to the start point.
        if (info.start[0] < info.xy[0]) {
            this.absgoingleft = true;

        } else if (info.start[0] > info.xy[0]) {
            // Otherwise we're going right.
            this.absgoingleft = false;
        }

        // Detect changes in the position relative to the last movement.
        if (info.delta[0] < 0) {
            this.goingleft = true;

        } else if (info.delta[0] > 0) {
            // Otherwise we're going right.
            this.goingleft = false;
        }

        // Detect if we are going up or down.
        if (info.delta[1] != info.xy[1]) {
            this.vertical = true;
        } else {
            this.vertical = false;
        }
    },

    drag_dropmiss: function(e) {
        // Missed the target, but we assume the user intended to drop it
        // on the last last ghost node location, e.drag and e.drop should be
        // prepared by global_drag_dropmiss parent so simulate drop_hit(e).
        this.drop_hit(e);
    },

    drop_over: function(e) {
        // Get a reference to our drag and drop nodes.
        var drag = e.drag.get('node'),
            drop = e.drop.get('node'),
            where;

        if (this.goingleft) {
            where = 'before';
        } else {
            where = 'after';
        }

        if (this.goingup) {
            where = 'before';
        }

        // Add the node contents so that it's moved, otherwise only the drag handle is moved.
        drop.insert(drag, where);
    },

    drop_hit: function(e) {
        var drop = e.drop.get('node'),
            drag = e.drag.get('node'),
            params = {};

        // Add spinner if it not there.
        var actionarea = drag.one('.' + CSS.ACTIONAREA);
        var spinner = M.util.add_spinner(Y, actionarea);

        // Prepare request parameters
        params.sesskey = M.cfg.sesskey;
        params.courseid = this.get('courseid');
        params.moveto = drop.getData('position');
        params.copy = drag.getData('position');
        params.action = 2;
        params.name = drag.getData('setting');

        // Perform the AJAX request.
        var uri = M.cfg.wwwroot + this.get('ajaxurl');

        Y.io(uri, {
            method: 'POST',
            data: params,
            on: {
                start: function() {
                    // this.lock_drag_handle(drag, CSS.MOVE);
                    spinner.show();
                },
                success: function() {
                    // this.unlock_drag_handle(drag, CSS.MOVE);
                    window.setTimeout(function() {
                        spinner.hide();
                    }, 250);
                },
                failure: function(tid, response) {
                    this.ajax_failure(response);
                    // this.unlock_drag_handle(drag, CSS.MOVE);
                    spinner.hide();
                    // TODO: revert nodes location
                }
            },
            context: this
        });
    }

}, {
    NAME : 'format_culcourse_quicklinkdd',
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
M.format_culcourse.init_quicklinkdd = function(params) {
    new QUICKLINK(params);
};


/**
 * Activity link drag and drop.
 *
 * @class M.format_culcourse_dragdrop
 * @constructor
 */

var ACTIVITYLINK = function() {
    ACTIVITYLINK.superclass.constructor.apply(this, arguments);
};

Y.extend(ACTIVITYLINK, M.core.dragdrop, {

    goingLeft: null,

    initializer: function() {        
        // Set group for parent class.
        this.groups = [CSS.ACTIVITYLINKDRAGGABLE];
        // Initialise activitylinks dragging
        this.activitylinklistselector = '.' + CSS.ACTIVITYLINK;
        
        if(Y.Node.all(this.activitylinklistselector).size()) {
            this.setup_for_activitylink(this.activitylinklistselector);
            this.samenodeclass = CSS.ACTIVITYLINK;
            this.parentnodeclass = CSS.ACTIVITYLINKCONTAINER;

            // Make each li element in the lists of activitylinks draggable.
            var del = new Y.DD.Delegate({
                container: '.' + CSS.ACTIVITYLINKCONTAINER,
                nodes: '.' + CSS.ACTIVITYLINKDRAGGABLE,
                target: true,
                handles: ['.' + CSS.MOVE],
                dragConfig: {groups: this.groups}
            });
            del.dd.plug(Y.Plugin.DDProxy, {
                // Don't move the node at the end of the drag.
                moveOnEnd: false,
                cloneNode: true
            });
            del.dd.plug(Y.Plugin.DDConstrained, {
                // Keep it inside the ul.
                constrain: '.' + CSS.ACTIVITYLINKCONTAINER
            });
            del.dd.plug(Y.Plugin.DDWinScroll);
        }
    },

    /**
     * Apply dragdrop features to the specified selector or node that refers to resource(s)
     *
     * @method setup_for_resource
     * @param {String} baseselector The CSS selector or node to limit scope to
     */
    setup_for_activitylink: function(baseselector) {
        Y.Node.all(baseselector).each(function(activitylinknode) {
            // Replace move icons.
            var move = activitylinknode.one('a' + '.' + CSS.MOVE);
            if (move) {
                move.replace(this.get_drag_handle(M.util.get_string('moveactivitylink', 'format_culcourse'),
                             CSS.MOVE, CSS.ICONCLASS, true));
            }
        }, this);
    },

    /*
     * Drag-dropping related functions
     */
    drag_start: function(e) {
        // Get our drag object.
        var drag = e.target;
        // Set some styles here.
        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
    },

    drag_drag: function(e) {
        // Core dragdrop checks for goingUp but our list is fluid
        // so we also need to check goingLeft.
        var drag = e.target,
            info = e.info;

        // Check that drag object belongs to correct group.
        if (!this.in_group(drag)) {
            return;
        }

        // Note, we test both < and > situations here. We don't want to
        // effect a change in direction if the user is only moving up
        // and down with no X position change.

        // Detect changes in the position relative to the start point.
        if (info.start[0] < info.xy[0]) {
            this.absgoingleft = true;

        } else if (info.start[0] > info.xy[0]) {
            // Otherwise we're going right.
            this.absgoingleft = false;
        }

        // Detect changes in the position relative to the last movement.
        if (info.delta[0] < 0) {
            this.goingleft = true;

        } else if (info.delta[0] > 0) {
            // Otherwise we're going right.
            this.goingleft = false;
        }

        // Detect if we are going up or down.
        if (info.delta[1] != info.xy[1]) {
            this.vertical = true;
        } else {
            this.vertical = false;
        }
    },

    drag_dropmiss: function(e) {
        // Missed the target, but we assume the user intended to drop it
        // on the last last ghost node location, e.drag and e.drop should be
        // prepared by global_drag_dropmiss parent so simulate drop_hit(e).
        this.drop_hit(e);
    },

    drop_over: function(e) {
        // Get a reference to our drag and drop nodes.
        var drag = e.drag.get('node'),
            drop = e.drop.get('node'),
            where;

        if (this.goingleft) {
            where = 'before';
        } else {
            where = 'after';
        }

        if (this.goingup) {
            where = 'before';
        }

        // Add the node contents so that it's moved, otherwise only the drag handle is moved.
        drop.insert(drag, where);
    },

    drop_hit: function(e) {
        var drop = e.drop.get('node'),
            drag = e.drag.get('node'),
            params = {};

        // Add spinner if it not there.
        var actionarea = drag.one('.' + CSS.ACTIONAREA);
        var spinner = M.util.add_spinner(Y, actionarea);

        // Prepare request parameters
        params.sesskey = M.cfg.sesskey;
        params.courseid = this.get('courseid');
        params.moveto = drop.getData('position');
        params.copy = drag.getData('position');
        params.action = 2;
        params.name = drag.getData('setting');

        // Perform the AJAX request.
        var uri = M.cfg.wwwroot + this.get('ajaxurl');

        Y.io(uri, {
            method: 'POST',
            data: params,
            on: {
                start: function() {
                    // this.lock_drag_handle(drag, CSS.MOVE);
                    spinner.show();
                },
                success: function() {
                    // this.unlock_drag_handle(drag, CSS.MOVE);
                    window.setTimeout(function() {
                        spinner.hide();
                    }, 250);
                },
                failure: function(tid, response) {
                    this.ajax_failure(response);
                    // this.unlock_drag_handle(drag, CSS.MOVE);
                    spinner.hide();
                    // TODO: revert nodes location
                }
            },
            context: this
        });
    }

}, {
    NAME : 'format_culcourse_activitylinkdd',
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
M.format_culcourse.init_activitylinkdd = function(params) {
    new ACTIVITYLINK(params);
};


/**
 * Section drag and drop.
 *
 * @class M.course.dragdrop.section
 * @constructor
 * @extends M.core.dragdrop
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
    SECTIONADDMENUS: 'section_add_menus',
    SECTIONHANDLE: 'section-handle',
    SUMMARY: 'summary',
    SECTIONDRAGGABLE: 'sectiondraggable'
};

var DRAGSECTION = function() {
    DRAGSECTION.superclass.constructor.apply(this, arguments);
};
Y.extend(DRAGSECTION, M.core.dragdrop, {
    // sectionlistselector: null,

    initializer: function() {
        // Set group for parent class
        this.groups = [CSS.SECTIONDRAGGABLE];
        this.samenodeclass = M.course.format.get_sectionwrapperclass();
        this.parentnodeclass = M.course.format.get_containerclass();

        // // Check if we are in single section mode
        if (Y.Node.one('.' + CSS.JUMPMENU)) {
            return false;
        }
        // Initialise sections dragging
        this.sectionlistselector = M.course.format.get_section_wrapper(Y);
        if (this.sectionlistselector) {
            this.sectionlistselector = '.' + CSS.COURSECONTENT + ' ' + this.sectionlistselector;

            this.setup_for_section(this.sectionlistselector);

        //     // Make each li element in the lists of sections draggable
            // var del = new Y.DD.Delegate({
            //     container: '.' + CSS.COURSECONTENT,
            //     nodes: '.' + CSS.SECTIONDRAGGABLE,
            //     target: true,
            //     handles: ['.' + CSS.LEFT],
            //     dragConfig: {groups: this.groups}
            // });
            // del.dd.plug(Y.Plugin.DDProxy, {
            //     // Don't move the node at the end of the drag
            //     moveOnEnd: false
            // });
            // del.dd.plug(Y.Plugin.DDConstrained, {
            //     // Keep it inside the .course-content
            //     constrain: '#' + CSS.PAGECONTENT,
            //     stickY: true
            // });
            // del.dd.plug(Y.Plugin.DDWinScroll);
        }

        // Y.all('.' + CSS.SECTIONDRAGGABLE).each(function(node) {
        //     node.setAttribute('class', 'blah');
        // });
    },

     /**
     * Apply dragdrop features to the specified selector or node that refers to section(s)
     *
     * @method setup_for_section
     * @param {String} baseselector The CSS selector or node to limit scope to
     */
    setup_for_section: function(baseselector) {
        Y.Node.all(baseselector).each(function(sectionnode) {
            // Determine the section ID
            var sectionid = Y.Moodle.core_course.util.section.getId(sectionnode);

            // We skip the top section as it is not draggable
            if (sectionid > 0) {
                // Remove move icons
                var movedown = sectionnode.one('.' + CSS.RIGHT + ' a.' + CSS.MOVEDOWN);
                var moveup = sectionnode.one('.' + CSS.RIGHT + ' a.' + CSS.MOVEUP);

                // Add dragger icon
                // var title = M.util.get_string('movesection', 'moodle', sectionid);
                var cssleft = sectionnode.one('.' + CSS.LEFT);

                if ((movedown || moveup) && cssleft) {
                    // cssleft.setStyle('cursor', 'move');
                    // cssleft.appendChild(this.get_drag_handle(title, CSS.SECTIONHANDLE, 'icon', true));

                    // if (moveup) {
                    //     if (moveup.previous('br')) {
                    //         moveup.previous('br').remove();
                    //     } else if (moveup.next('br')) {
                    //         moveup.next('br').remove();
                    //     }

                    //     if (moveup.ancestor('.section_action_menu') && moveup.ancestor().get('nodeName').toLowerCase() == 'li') {
                    //         moveup.ancestor().remove();
                    //     } else {
                    //         moveup.remove();
                    //     }
                    // }
                    // if (movedown) {
                    //     if (movedown.previous('br')) {
                    //         movedown.previous('br').remove();
                    //     } else if (movedown.next('br')) {
                    //         movedown.next('br').remove();
                    //     }

                    //     var movedownParentType = movedown.ancestor().get('nodeName').toLowerCase();
                    //     if (movedown.ancestor('.section_action_menu') && movedownParentType == 'li') {
                    //         movedown.ancestor().remove();
                    //     } else {
                    //         movedown.remove();
                    //     }
                    // }

                    // This section can be moved - add the class to indicate this to Y.DD.
                    sectionnode.addClass(CSS.SECTIONDRAGGABLE);
                }
            }
        }, this);
    },

    // /*
    //  * Drag-dropping related functions
    //  */
    // drag_start: function(e) {
    //     // Get our drag object
    //     var drag = e.target;
    //     // Creat a dummy structure of the outer elemnents for clean styles application
    //     var containernode = Y.Node.create('<' + M.course.format.get_containernode() +
    //             '></' + M.course.format.get_containernode() + '>');
    //     containernode.addClass(M.course.format.get_containerclass());
    //     var sectionnode = Y.Node.create('<' + M.course.format.get_sectionwrappernode() +
    //             '></' + M.course.format.get_sectionwrappernode() + '>');
    //     sectionnode.addClass(M.course.format.get_sectionwrapperclass());
    //     sectionnode.setStyle('margin', 0);
    //     sectionnode.setContent(drag.get('node').get('innerHTML'));
    //     containernode.appendChild(sectionnode);
    //     drag.get('dragNode').setContent(containernode);
    //     drag.get('dragNode').addClass(CSS.COURSECONTENT);
    // },

    drag_end: function(e) {
        
    }

    // get_section_index: function(node) {
    //     var sectionlistselector = '.' + CSS.COURSECONTENT + ' ' + M.course.format.get_section_selector(Y),
    //         sectionList = Y.all(sectionlistselector),
    //         nodeIndex = sectionList.indexOf(node),
    //         zeroIndex = sectionList.indexOf(Y.one('#section-0'));

    //     return (nodeIndex - zeroIndex);
    // },

    // drop_hit: function(e) {
    //     var drag = e.drag;

    //     // Get references to our nodes and their IDs.
    //     var dragnode = drag.get('node'),
    //         dragnodeid = Y.Moodle.core_course.util.section.getId(dragnode),
    //         loopstart = dragnodeid,

    //         dropnodeindex = this.get_section_index(dragnode),
    //         loopend = dropnodeindex;

    //     if (dragnodeid === dropnodeindex) {
    //         return;
    //     }


    //     if (loopstart > loopend) {
    //         // If we're going up, we need to swap the loop order
    //         // because loops can't go backwards.
    //         loopstart = dropnodeindex;
    //         loopend = dragnodeid;
    //     }

    //     // Get the list of nodes.
    //     drag.get('dragNode').removeClass(CSS.COURSECONTENT);
    //     var sectionlist = Y.Node.all(this.sectionlistselector);

    //     // Add a lightbox if it's not there.
    //     var lightbox = M.util.add_lightbox(Y, dragnode);

    //     // Handle any variables which we must pass via AJAX.
    //     var params = {},
    //         pageparams = this.get('config').pageparams,
    //         varname;

    //     for (varname in pageparams) {
    //         if (!pageparams.hasOwnProperty(varname)) {
    //             continue;
    //         }
    //         params[varname] = pageparams[varname];
    //     }

    //     // Prepare request parameters
    //     params.sesskey = M.cfg.sesskey;
    //     params.courseId = this.get('courseid');
    //     params['class'] = 'section';
    //     params.field = 'move';
    //     params.id = dragnodeid;
    //     params.value = dropnodeindex;

    //     // Perform the AJAX request.
    //     var uri = M.cfg.wwwroot + this.get('ajaxurl');
    //     Y.io(uri, {
    //         method: 'POST',
    //         data: params,
    //         on: {
    //             start: function() {
    //                 lightbox.show();
    //             },
    //             success: function(tid, response) {
    //                 // Update section titles, we can't simply swap them as
    //                 // they might have custom title
    //                 try {
    //                     var responsetext = Y.JSON.parse(response.responseText);
    //                     if (responsetext.error) {
    //                         new M.core.ajaxException(responsetext);
    //                     }
    //                     M.course.format.process_sections(Y, sectionlist, responsetext, loopstart, loopend);
    //                 } catch (e) {
    //                     // Ignore.
    //                 }

    //                 // Update all of the section IDs - first unset them, then set them
    //                 // to avoid duplicates in the DOM.
    //                 var index;

    //                 // Classic bubble sort algorithm is applied to the section
    //                 // nodes between original drag node location and the new one.
    //                 var swapped = false;
    //                 do {
    //                     swapped = false;
    //                     for (index = loopstart; index <= loopend; index++) {
    //                         if (Y.Moodle.core_course.util.section.getId(sectionlist.item(index - 1)) >
    //                                     Y.Moodle.core_course.util.section.getId(sectionlist.item(index))) {
    //                             // Swap section id.
    //                             var sectionid = sectionlist.item(index - 1).get('id');
    //                             sectionlist.item(index - 1).set('id', sectionlist.item(index).get('id'));
    //                             sectionlist.item(index).set('id', sectionid);

    //                             // See what format needs to swap.
    //                             M.course.format.swap_sections(Y, index - 1, index);

    //                             // Update flag.
    //                             swapped = true;
    //                         }
    //                     }
    //                     loopend = loopend - 1;
    //                 } while (swapped);

    //                 window.setTimeout(function() {
    //                     lightbox.hide();
    //                 }, 250);
    //             },

    //             failure: function(tid, response) {
    //                 this.ajax_failure(response);
    //                 lightbox.hide();
    //             }
    //         },
    //         context: this
    //     });
    // }

}, {
    NAME : 'format_culcourse_sectiondd',
    //     ATTRS: {
    //     courseid: {
    //         value: null
    //     },
    //     ajaxurl: {
    //         value: 0
    //     },
    //     config: {
    //         value: 0
    //     }
    // }
});

M.format_culcourse = M.format_culcourse || {};
M.format_culcourse.init_sectiondd = function(params) {
    new DRAGSECTION(params);
};


}, '@VERSION@', {
    "requires": [
        "base",
        "node",
        "io",
        "dom",
        "dd",
        "dd-scroll",
        "moodle-core-dragdrop",
        "moodle-core-notification",
        "moodle-course-coursebase",
        "moodle-course-util",
        "moodle-course-dragdrop"
    ]
});
