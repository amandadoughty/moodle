YUI.add('moodle-format_culcourse-dragdrop', function (Y, NAME) {

/**
 * Dash link drag and drop.
 *
 * @class M.format_culcourse_dragdrop
 * @constructor
 */

var DASHLINK = function() {
    DASHLINK.superclass.constructor.apply(this, arguments);
};

var CSS = {
        ACTIONAREA: 'dash-edit',
        ACTIONLINKCONTAINER: 'activitylinks',
        DASHLINK: 'dash',
        DASHLINKDRAGGABLE: 'dashlinkdraggable',
        MOVE: 'dash-move',
        QUICKLINKCONTAINER: 'quicklinks'
    };


Y.extend(DASHLINK, M.core.dragdrop, {

    goingup: null,

    initializer: function() {     
        // Set group for parent class
        this.groups = [CSS.DASHLINKDRAGGABLE];
        // Initialise quicklinks dragging
        this.dashlinklistselector = '.' + CSS.DASHLINK;
        this.setup_for_dashlink(this.dashlinklistselector);
        this.samenodeclass = CSS.DASHLINK;
        this.parentnodeclass = CSS.QUICKLINKCONTAINER;

        // Make each li element in the lists of quicklinks draggable.
        var del = new Y.DD.Delegate({
            container: '.' + CSS.QUICKLINKCONTAINER,
            nodes: '.' + CSS.DASHLINKDRAGGABLE,
            target: true,
            handles: ['.' + CSS.MOVE],
            dragConfig: {groups: this.groups}
        });
        del.dd.plug(Y.Plugin.DDProxy, {
            // Don't move the node at the end of the drag
            moveOnEnd: false,
            cloneNode: true
        });
        del.dd.plug(Y.Plugin.DDConstrained, {
            // Keep it inside the ul.
            constrain: '.' + CSS.QUICKLINKCONTAINER
        });
        del.dd.plug(Y.Plugin.DDWinScroll);

        // Create targets for drop.
        // var droparea = Y.Node.one('.' + CSS.QUICKLINKCONTAINER);
        // var tar = new Y.DD.Drop({
        //     groups: this.groups,
        //     node: droparea
        // });

        // Make each li element in the lists of activitylinks draggable.
        var del = new Y.DD.Delegate({
            container: '.' + CSS.ACTIONLINKCONTAINER,
            nodes: '.' + CSS.DASHLINKDRAGGABLE,
            target: true,
            handles: ['.' + CSS.MOVE],
            dragConfig: {groups: this.groups}
        });
        del.dd.plug(Y.Plugin.DDProxy, {
            // Don't move the node at the end of the drag
            moveOnEnd: false,
            cloneNode: true
        });
        del.dd.plug(Y.Plugin.DDConstrained, {
            // Keep it inside the ul.
            constrain: '.' + CSS.ACTIONLINKCONTAINER
        });
        del.dd.plug(Y.Plugin.DDWinScroll);
 
    },

    /**
     * Apply dragdrop features to the specified selector or node that refers to resource(s)
     *
     * @method setup_for_resource
     * @param {String} baseselector The CSS selector or node to limit scope to
     */
    setup_for_dashlink: function(baseselector) {
        Y.Node.all(baseselector).each(function(dashlinknode) {
            var draggroups = dashlinknode.getData('draggroups');
            if (!draggroups) {
                // This Drop Node has not been set up. Configure it now.
                dashlinknode.setAttribute('data-draggroups', this.groups.join(' '));
                
                new Y.DD.Drop({
                    node: dashlinknode,
                    groups: this.groups,
                    padding: '20 0 20 0'
                });
            }

            // Replace move icons
            var move = dashlinknode.one('a' + '.' + CSS.MOVE);
            if (move) {
                move.replace(this.get_drag_handle(M.util.get_string('movedashlink', 'format_culcourse'),
                             CSS.MOVE, CSS.ICONCLASS, true));
            }
        }, this);
    },

    /*
     * Drag-dropping related functions
     */
    drag_start: function(e) {
        //Get our drag object
        var drag = e.target;
        //Set some styles here
        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
    },

    drag_drag: function(e) {
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
        if (drop.hasClass(CSS.DASHLINK)) {
            // Are we not going up?
            if (!this.goingLeft && !this.goingUp) {
                drop = drop.get('nextSibling');
            }

            // Add the node to this list.
            e.drop.get('node').get('parentNode').insertBefore(drag, drop);
            // Resize this nodes shim, so we can drop on it later.
            // e.drop.sizeShim();
        }
    },

    drop_hit: function(e) {
        var drop = e.drop.get('node'),
            drag = e.drag.get('node'),
            params = {};

        // Add spinner if it not there.
        var actionarea = drag.one('.' + CSS.ACTIONAREA);
        var spinner = M.util.add_spinner(Y, actionarea);

        // if we are not on an li, we must have been dropped on a ul.
        if (!drop.hasClass(CSS.DASHLINK)) {
            // if (!drop.contains(drag)) {
                drop.appendChild(drag);
            // }
        }

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
                success: function(tid, response) {
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
    NAME : 'format_culcourse_dragdrop',
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
M.format_culcourse.init_dragdrop = function(params) {
    new DASHLINK(params);
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
