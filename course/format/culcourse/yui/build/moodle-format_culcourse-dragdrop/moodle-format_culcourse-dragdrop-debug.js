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
    },

    /**
     * Apply dragdrop features to the specified selector or node that refers to quicklink(s)
     *
     * @method setup_for_quicklink
     * @param {String} baseselector The CSS selector or node to limit scope to
     */
    setup_for_quicklink: function(baseselector) {
        Y.Node.all(baseselector).each(function(quicklinknode) {
            var draggroups = quicklinknode.getData('draggroups');
            if (!draggroups) {
                // This Drop Node has not been set up. Configure it now.
                quicklinknode.setAttribute('data-draggroups', this.groups.join(' '));
                
                new Y.DD.Drop({
                    node: quicklinknode,
                    groups: this.groups,
                    padding: '20 0 20 0'
                });
            }

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
        this.keydown = false;
        // Core dragdrop checks for goingUp but our list is fluid
        // so we also need to check goingLeft.
        var x = e.target.lastXY[0];

        // Is it greater than the lastX var?s
        if (x < this.lastX) {
            // We are going left.
            this.goingLeft = true;
        } else {
            // We are going right.
            this.goingLeft = false;
        }

        // Cache for next check.
        this.lastX = x;

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
            drop = e.drop.get('node');

        // Are we dropping on a li node?
        if (drop.hasClass(CSS.QUICKLINK)) {
            // Are we not going left or up?
            if (!this.goingLeft && !this.goingUp) {
                drop = drop.get('nextSibling');
                e.drop.get('node').get('parentNode').insertBefore(drag, drop);
            }
        }
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
    },

    /**
     * Apply dragdrop features to the specified selector or node that refers to resource(s)
     *
     * @method setup_for_resource
     * @param {String} baseselector The CSS selector or node to limit scope to
     */
    setup_for_activitylink: function(baseselector) {
        Y.Node.all(baseselector).each(function(activitylinknode) {
            var draggroups = activitylinknode.getData('draggroups');
            if (!draggroups) {
                // This Drop Node has not been set up. Configure it now.
                activitylinknode.setAttribute('data-draggroups', this.groups.join(' '));
                
                new Y.DD.Drop({
                    node: activitylinknode,
                    groups: this.groups,
                    padding: '20 0 20 0'
                });
            }

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
        this.keydown = false;
        // Core dragdrop checks for goingUp but our list is fluid
        // so we also need to check goingLeft.
        var x = e.target.lastXY[0];

        // Is it greater than the lastX var?s
        if (x < this.lastX) {
            // We are going left.
            this.goingLeft = true;
        } else {
            // We are going right.
            this.goingLeft = false;
        }

        // Cache for next check.
        this.lastX = x;

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
            drop = e.drop.get('node');

        // Are we dropping on a li node?
        if (drop.hasClass(CSS.ACTIVITYLINK)) {
            // Are we not going left or up?
            if (!this.goingLeft && !this.goingUp) {
                drop = drop.get('nextSibling');
                e.drop.get('node').get('parentNode').insertBefore(drag, drop);
            }
        }
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
        "moodle-course-util"
    ]
});
