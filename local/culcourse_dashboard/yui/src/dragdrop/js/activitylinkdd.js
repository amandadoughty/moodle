/**
 * Activity link drag and drop.
 *
 * @class M.local_culcourse_dashboard_dragdrop
 * @constructor
 */

var ACTIVITYLINK = function() {
    ACTIVITYLINK.superclass.constructor.apply(this, arguments);
};

Y.extend(ACTIVITYLINK, M.core.dragdrop, {

    goingLeft: null,
    isdragging: false,

    initializer: function() {
        // Set group for parent class.
        this.groups = [CSS.ACTIVITYLINKDRAGGABLE];
        this.samenodeclass = CSS.ACTIVITYLINK;
        this.parentnodeclass = CSS.ACTIVITYLINKCONTAINER;

        this.samenodelabel = {
            identifier: 'afterlink',
            component: 'local_culcourse_dashboard'
        };
        this.parentnodelabel = {
            identifier: 'totopoflinks',
            component: 'local_culcourse_dashboard'
        };
        // Initialise activitylinks dragging
        this.activitylinklistselector = '.' + CSS.ACTIVITYLINK;

        if(Y.Node.all(this.activitylinklistselector).size()) {
            this.setup_for_activitylink(this.activitylinklistselector);

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
            var draggroups = activitylinknode.getData('draggroups');
            if (!draggroups) {
                // This Drop Node has not been set up. Configure it now.
                activitylinknode.setAttribute('data-draggroups', this.groups.join(' '));
                // Define empty ul as droptarget, so that item could be moved to empty list
                new Y.DD.Drop({
                    node: activitylinknode,
                    groups: this.groups,
                    padding: '20 0 20 0'
                });
            }

            // Replace move icons.
            var move = activitylinknode.one('a' + '.' + CSS.MOVE);

            if (move) {
                var str = move.get('text');
                move.replace(this.get_drag_handle(str,
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
        // If we are in this function then the movement is by
        // dragging.
        this.isdragging = true;
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

        // Are we dropping within the same parent node?
        if (drop.hasClass(this.samenodeclass)) {

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
        } else if ((drop.hasClass(this.parentnodeclass) || drop.test('[data-droptarget="1"]')) && !drop.contains(drag)) {
            // We are dropping on parent node and it is empty
            if (this.goingup) {
                drop.append(drag);
            } else {
                drop.prepend(drag);
            }
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
        params.keyboard = !this.isdragging;

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
    NAME : 'local_culcourse_dashboard_activitylinkdd',
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

M.local_culcourse_dashboard = M.local_culcourse_dashboard || {};
M.local_culcourse_dashboard.init_activitylinkdd = function(params) {
    new ACTIVITYLINK(params);
};
