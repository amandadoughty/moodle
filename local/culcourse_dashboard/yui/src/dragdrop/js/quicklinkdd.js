/**
 * Quick link drag and drop.
 *
 * @class M.local_culcourse_dashboard_dragdrop
 * @constructor
 */

var QUICKLINK = function() {
    QUICKLINK.superclass.constructor.apply(this, arguments);
};

Y.extend(QUICKLINK, M.core.dragdrop, {

    goingLeft: null,
    isdragging: false,

    initializer: function() {
        // Set group for parent class.
        this.groups = [CSS.QUICKLINKDRAGGABLE];
        this.samenodeclass = CSS.QUICKLINK;
        this.parentnodeclass = CSS.QUICKLINKCONTAINER;

        this.samenodelabel = {
            identifier: 'afterlink',
            component: 'local_culcourse_dashboard'
        };
        this.parentnodelabel = {
            identifier: 'totopoflinks',
            component: 'local_culcourse_dashboard'
        };
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
            var draggroups = quicklinknode.getData('draggroups');
            if (!draggroups) {
                // This Drop Node has not been set up. Configure it now.
                quicklinknode.setAttribute('data-draggroups', this.groups.join(' '));
                // Define empty ul as droptarget, so that item could be moved to empty list
                new Y.DD.Drop({
                    node: quicklinknode,
                    groups: this.groups,
                    padding: '20 0 20 0'
                });
            }

            // Replace move icons.
            var move = quicklinknode.one('a' + '.' + CSS.MOVE);

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
        if (info.offset[0] < 0) {
            this.absgoingleft = true;

        } else if (info.offset[0] > 0) {
            // Otherwise we're going right.
            this.absgoingleft = false;
        } else {
            this.absgoingleft = null;
        }

        // Detect changes in the position relative to the start point.
        if (info.offset[1] > 0) {
            // We are going down if our final position is higher than our start position.
            this.culabsgoingup = false;

        } else if (info.offset[1] < 0) {
            // Otherwise we're going down.
            this.culabsgoingup = true;
        } else {
            this.culabsgoingup = null;
        }

        // Detect changes in the position relative to the last movement.
        if (info.delta[0] < 0) {
            this.goingleft = true;

        } else if (info.delta[0] > 0) {
            // Otherwise we're going right.
            this.goingleft = false;
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
            drop = e.drop.get('node');

        // Are we dropping within the same parent node?
        if (drop.hasClass(this.samenodeclass)) {
            var where;

            if (this.absgoingleft === true) {
                where = 'before';
            } else if (this.absgoingleft === false) {
                where = 'after';
            }

            if (this.culabsgoingup === true) {
                where = 'before';
            } else if (this.culabsgoingup === false) {
                where = 'after';
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
    NAME : 'local_culcourse_dashboard_quicklinkdd',
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
M.local_culcourse_dashboard.init_quicklinkdd = function(params) {
    new QUICKLINK(params);
};


