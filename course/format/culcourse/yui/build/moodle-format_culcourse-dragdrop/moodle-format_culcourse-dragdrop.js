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
        BLOCK: 'dash-panel',
        DASHLINK: 'dash',
        QUICKLINKDRAGGABLE: 'quicklinkdraggable',
        QUICKLINKCONTAINER: 'course-content',
        MOVE: 'dash-move',
        ICONCLASS: 'iconsmall'
    },
    SELECTORS = {
        QUICKLINK: '.dash.quick',
        QUICKLINKCONTAINER: '.links.quicklinks',
        ACTIVITYLINK: '.dash.activity',
        ACTIVITYLINKCONTAINER: '.links.activitylinks',
        MOVE: '.dash-move',
        DASHLINK: '.dash',
        QUICKLINKDRAGGABLE: '.quicklinkdraggable'
    };
    // URL = M.cfg.wwwroot + '/course/format/culcourse/dashboard/dashlink_edit_ajax.php.php';

Y.extend(DASHLINK, M.core.dragdrop, {

    initializer: function() {
        // Set group for parent class
        this.groups = [CSS.QUICKLINKDRAGGABLE];
        // this.samenodeclass = 'quick dash';
        // this.parentnodeclass = 'quicklinks links clearfix';

        // Initialise quicklinks dragging
        this.quicklinklistselector = SELECTORS.QUICKLINK;
        // if (this.quicklinklistselector) {
            // this.quicklinklistselector = '.' + CSS.QUICKLINKCONTAINER + ' ' + this.quicklinklistselector;

            this.setup_for_quicklink(this.quicklinklistselector);

            // Make each li element in the lists of quicklinks draggable
            var del = new Y.DD.Delegate({
                container: SELECTORS.QUICKLINKCONTAINER,
                nodes: SELECTORS.QUICKLINKDRAGGABLE,
                target: true,
                handles: [SELECTORS.MOVE],
                dragConfig: {groups: this.groups}
            });
            del.dd.plug(Y.Plugin.DDProxy, {
                // Don't move the node at the end of the drag
                moveOnEnd: false,
                cloneNode: true
            });
            del.dd.plug(Y.Plugin.DDConstrained, {
                // Keep it inside the .course-content
                constrain: SELECTORS.QUICKLINKCONTAINER
            });
            del.dd.plug(Y.Plugin.DDWinScroll);
        // }
        //Create targets for drop.
        var droparea = Y.Node.one(SELECTORS.QUICKLINKCONTAINER);
        var tar = new Y.DD.Drop({
            groups: this.groups,
            node: droparea
        });
 
    },

        /**
     * Apply dragdrop features to the specified selector or node that refers to resource(s)
     *
     * @method setup_for_resource
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

            // Replace move icons
            var move = quicklinknode.one('a' + SELECTORS.MOVE);
            if (move) {
                // var sr = move.getData('sectionreturn');
                // move.replace(this.get_drag_handle(M.util.get_string('movecoursemodule', 'moodle'),
                //              CSS.EDITINGMOVE, CSS.ICONCLASS, true).setAttribute('data-sectionreturn', sr));

                move.replace(this.get_drag_handle(M.util.get_string('movecoursemodule', 'moodle'),
                             CSS.MOVE, CSS.ICONCLASS, true));
            }
        }, this);
    },

    /*
     * Drag-dropping related functions
     */
    drag_start: function(e) {
        // // Get our drag object
        // var drag = e.target;
        // // Creat a dummy structure of the outer elemnents for clean styles application
        // var containernode = Y.Node.create('<ul></ul>');
        // containernode.addClass('quicklinks links clearfix');
        // var quicklinknode = Y.Node.create('<li></li>');
        // quicklinknode.addClass('dash quick');
        // quicklinknode.setStyle('margin', 0);
        // quicklinknode.setContent(drag.get('node').get('innerHTML'));
        // containernode.appendChild(quicklinknode);
        // drag.get('dragNode').setContent(containernode);
        // drag.get('dragNode').addClass(CSS.QUICKLINKCONTAINER);

        //Get our drag object
        var drag = e.target;

        //Set some styles here
        drag.get('node').addClass('drag_target_active');
        drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
        drag.get('dragNode').addClass('drag_item_active');
        drag.get('dragNode').setStyles({
            borderColor: drag.get('node').getStyle('borderColor'),
            backgroundColor: drag.get('node').getStyle('backgroundColor')
        });
    },

    drag_dropmiss: function(e) {
        // Missed the target, but we assume the user intended to drop it
        // on the last last ghost node location, e.drag and e.drop should be
        // prepared by global_drag_dropmiss parent so simulate drop_hit(e).
        this.drop_hit(e);
    },

    // get_quicklink_index: function(node) {
    //     var quicklinklistselector = '.' + CSS.QUICKLINKCONTAINER + ' ' + M.course.format.get_quicklink_selector(Y),
    //         quicklinkList = Y.all(quicklinklistselector),
    //         nodeIndex = quicklinkList.indexOf(node),
    //         zeroIndex = quicklinkList.indexOf(Y.one('#quicklink-0'));

    //     return (nodeIndex - zeroIndex);
    // },

    drop_hit: function(e) {
        // var drag = e.drag;

        // // Get references to our nodes and their IDs.
        // var dragnode = drag.get('node'),
        //     dragnodeid = Y.Moodle.core_course.util.quicklink.getId(dragnode),
        //     loopstart = dragnodeid,

        //     dropnodeindex = this.get_quicklink_index(dragnode),
        //     loopend = dropnodeindex;

        // if (dragnodeid === dropnodeindex) {
        //     return;
        // }


        // if (loopstart > loopend) {
        //     // If we're going up, we need to swap the loop order
        //     // because loops can't go backwards.
        //     loopstart = dropnodeindex;
        //     loopend = dragnodeid;
        // }

        // // Get the list of nodes.
        // drag.get('dragNode').removeClass(CSS.QUICKLINKCONTAINER);
        // var quicklinklist = Y.Node.all(this.quicklinklistselector);

        // Add a lightbox if it's not there.
        // var lightbox = M.util.add_lightbox(Y, dragnode);

        // Handle any variables which we must pass via AJAX.
        // var params = {},
        //     pageparams = this.get('config').pageparams,
        //     varname;

        // for (varname in pageparams) {
        //     if (!pageparams.hasOwnProperty(varname)) {
        //         continue;
        //     }
        //     params[varname] = pageparams[varname];
        // }

        // // Prepare request parameters
        // params.sesskey = M.cfg.sesskey;
        // params.courseId = this.get('courseid');
        // params['class'] = 'quicklink';
        // params.field = 'move';
        // params.id = dragnodeid;
        // params.value = dropnodeindex;

        // // Perform the AJAX request.
        // var uri = M.cfg.wwwroot + this.get('ajaxurl');
        // Y.io(uri, {
        //     method: 'POST',
        //     data: params,
        //     on: {
        //         start: function() {
        //             lightbox.show();
        //         },
        //         success: function(tid, response) {
        //             // Update quicklink titles, we can't simply swap them as
        //             // they might have custom title
        //             // try {
        //             //     var responsetext = Y.JSON.parse(response.responseText);
        //             //     if (responsetext.error) {
        //             //         new M.core.ajaxException(responsetext);
        //             //     }
        //             //     M.course.format.process_quicklinks(Y, quicklinklist, responsetext, loopstart, loopend);
        //             // } catch (e) {
        //             //     // Ignore.
        //             // }

        //             // // Update all of the quicklink IDs - first unset them, then set them
        //             // // to avoid duplicates in the DOM.
        //             // var index;

        //             // // Classic bubble sort algorithm is applied to the quicklink
        //             // // nodes between original drag node location and the new one.
        //             // var swapped = false;
        //             // do {
        //             //     swapped = false;
        //             //     for (index = loopstart; index <= loopend; index++) {
        //             //         if (Y.Moodle.core_course.util.quicklink.getId(quicklinklist.item(index - 1)) >
        //             //                     Y.Moodle.core_course.util.quicklink.getId(quicklinklist.item(index))) {
        //             //             // Swap quicklink id.
        //             //             var quicklinkid = quicklinklist.item(index - 1).get('id');
        //             //             quicklinklist.item(index - 1).set('id', quicklinklist.item(index).get('id'));
        //             //             quicklinklist.item(index).set('id', quicklinkid);

        //             //             // See what format needs to swap.
        //             //             M.course.format.swap_quicklinks(Y, index - 1, index);

        //             //             // Update flag.
        //             //             swapped = true;
        //             //         }
        //             //     }
        //             //     loopend = loopend - 1;
        //             // } while (swapped);

        //             window.setTimeout(function() {
        //                 lightbox.hide();
        //             }, 250);
            //     },

            //     failure: function(tid, response) {
            //         this.ajax_failure(response);
            //         lightbox.hide();
            //     }
            // },
        //     context: this
        // });
    }

}, {
    NAME : 'format_culcourse_dragdrop'
    // ATTRS : {
        // node : {
        //     value : null
        // }
    // }
});

M.format_culcourse = M.format_culcourse || {};
M.format_culcourse.init_dragdrop = function() {
    new DASHLINK();
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
