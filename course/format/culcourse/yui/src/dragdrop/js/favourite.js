    var DASHLINK = function() {
        DASHLINK.superclass.constructor.apply(this, arguments);
    };

    var CSS = {
        QUICKLINK: 'quick',
        QUICKLINKCONTAINER: 'quicklinks',
        DASHLINK: 'dash',
        QUICKLINKDRAGGABLE: 'quicklinkdraggable',
        COURSECONTENT: 'course-content',
        MOVE: 'dash-move',
        ICONCLASS: 'iconsmall'
    },
    SELECTORS = {
        QUICKLINK: '.dash.quick',
        QUICKLINKCONTAINER: '.links.quicklinks',
        COURSECONTENT: '.course-content',
        ACTIVITYLINK: '.dash.activity',
        ACTIVITYLINKCONTAINER: '.links.activitylinks',
        MOVE: '.dash-move',
        DASHLINK: '.dash',
        QUICKLINKDRAGGABLE: '.quicklinkdraggable'
    },
    MOVEICON = {
        pix: "i/move_2d",
        largepix: "i/dragdrop",
        component: 'moodle',
        cssclass: 'moodle-core-dragdrop-draghandle'
    },
    URL = M.cfg.wwwroot + '/course/format/culcourse/dashboard/dashlink_edit_ajax.php.php';


    Y.extend(DASHLINK, Y.Base, {

    initializer: function() {
        // Set group for parent class
        // this.groups = [CSS.QUICKLINKDRAGGABLE];
        // Initialise quicklinks dragging
        this.quicklinklistselector = SELECTORS.QUICKLINK;
            // this.setup_for_quicklink(this.quicklinklistselector);
        // this.samenodeclass = CSS.QUICKLINK;
        // this.parentnodeclass = CSS.QUICKLINKCONTAINER;

        // Make each li element in the lists of quicklinks draggable
        var del = new Y.DD.Delegate({
            container: SELECTORS.QUICKLINKCONTAINER,
            nodes: SELECTORS.QUICKLINKDRAGGABLE,
            target: true,
            handles: [SELECTORS.MOVE]
            // dragConfig: {groups: this.groups}
        });
        del.dd.plug(Y.Plugin.DDProxy, {
            // Don't move the node at the end of the drag
            moveOnEnd: false,
            cloneNode: true
        });
        del.dd.plug(Y.Plugin.DDConstrained, {
            // Keep it inside the .course-content
            constrain: SELECTORS.COURSECONTENT
        });
        del.dd.plug(Y.Plugin.DDWinScroll);

        //Create targets for drop.
        var droparea = Y.Node.one(SELECTORS.QUICKLINKCONTAINER);
        var tar = new Y.DD.Drop({
            // groups: this.groups,
            node: droparea
        });

        this.adddragdrop();
 
    },
        /**
     * Build a new drag handle Node.
     *
     * @method get_drag_handle
     * @param {String} title The title on the drag handle
     * @param {String} classname The name of the class to add to the node
     * wrapping the drag icon
     * @param {String} iconclass Additional class to add to the icon.
     * @return Node The built drag handle.
     */
    get_drag_handle: function(title, classname, iconclass) {

        var dragelement = Y.Node.create('<span></span>')
            .addClass(classname)
            .setAttribute('title', title)
            .setAttribute('tabIndex', 0)
            .setAttribute('data-draggroups', this.groups)
            .setAttribute('role', 'button');
        dragelement.addClass(MOVEICON.cssclass);

        window.require(['core/templates'], function(Templates) {
            Templates.renderPix('i/move_2d', 'core').then(function(html) {
                var dragicon = Y.Node.create(html);
                dragicon.setStyle('cursor', 'move');
                if (typeof iconclass != 'undefined') {
                    dragicon.addClass(iconclass);
                }
                dragelement.appendChild(dragicon);
            });
        });

        return dragelement;
    },

        addmoveicon: function() {
            // Replace the non-JS links.
            // var move = M.util.get_string('move', 'block_culcourse_listing');
            // var newdiv = Y.Node.create('<div class="move"></div>');
            // var icon = Y.Node.create(
            //     '<img src="' + M.util.image_url('i/move_2d', 'moodle') +
            //     '" alt="' + move +
            //     '" title="' + move +
            //     '" class="cursor"/>'
            //     );
            // newdiv.append(icon);

            // if (params.node.one(SELECTORS.DASHLINKMOVEWITHOUTJS)) {
            //     params.node.one(SELECTORS.DASHLINKMOVEWITHOUTJS).replace(newdiv);
            // } else {
            //     params.node.one(SELECTORS.COURSEBOXLINK).prepend(newdiv);
            // }

            Y.Node.all(SELECTORS.QUICKLINK).each(function(quicklinknode) {

                // Replace move icons
                var move = quicklinknode.one('a' + SELECTORS.MOVE);
                if (move) {
                    move.replace(this.get_drag_handle(M.util.get_string('movecoursemodule', 'moodle'),
                                 CSS.MOVE, CSS.ICONCLASS, true));
                }
            }, this);
       
        },

        adddragdrop: function() {
            this.addmoveicon();
            // Static Vars.
            var goingLeft = false, lastX = 0;
            var goingUp = false, lastY = 0;
            var savemove = this.savemove;

            // var d = new Y.DD.Drag({
            //         node: params.node,
            //         target: true
            //     }).plug(Y.Plugin.DDProxy, {
            //         moveOnEnd: false,
            //         cloneNode: true
            //     }).plug(Y.Plugin.DDConstrained, {
            //         constrain2node: SELECTORS.QUICKLINKCONTAINER
            //     });
            //     d.addHandle(SELECTORS.MOVE);

            Y.DD.DDM.on('drag:start', function(e) {
                // // Get our drag object.
                // var drag = e.target;
                // // Set some styles here.
                // drag.get('node').setStyle('opacity', '.25');
                // drag.get('dragNode').addClass(CSS.BLOCK);
                // drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
                // drag.get('dragNode').setStyles({
                //     opacity: '.5',
                //     borderColor: drag.get('node').getStyle('borderColor'),
                //     backgroundColor: drag.get('node').getStyle('backgroundColor')
                // });

                //Get our drag object
                var drag = e.target;

                //Set some styles here
                drag.get('node').addClass('drag_target_active');
                drag.get('node').setStyle('opacity', '.25');
                drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
                drag.get('dragNode').addClass('drag_item_active');
                drag.get('dragNode').setStyles({
                    borderColor: drag.get('node').getStyle('borderColor'),
                    backgroundColor: drag.get('node').getStyle('backgroundColor')
                });
            });

            Y.DD.DDM.on('drag:end', function(e) {
                var drag = e.target;
                // Put our styles back.
                drag.get('node').setStyles({
                    visibility: '',
                    opacity: '1'
                });
                // savemove();
            });

            Y.DD.DDM.on('drag:drag', function(e) {
                // Get the last y point.
                var x = e.target.lastXY[0];
                var y = e.target.lastXY[1];

                // Is it greater than the lastY var?s
                if (x < lastX) {
                    // We are going up.
                    goingLeft = true;
                } else {
                    // We are going down.
                    goingLeft = false;
                }

                // Cache for next check.
                lastX = x;

                // Is it greater than the lastY var?s
                if (y < lastY) {
                    // We are going up.
                    goingUp = true;
                } else {
                    // We are going down.
                    goingUp = false;
                }

                // Cache for next check.
                lastY = y;
            });

            Y.DD.DDM.on('drop:over', function(e) {
                // Get a reference to our drag and drop nodes.
                var drag = e.drag.get('node'),
                    drop = e.drop.get('node');

                // Are we dropping on a li node?
                if (drop.hasClass(CSS.QUICKLINK)) {
                    // Are we not going up?
                    if (!goingLeft && !goingUp) {
                        drop = drop.get('nextSibling');
                    }
                    // Add the node to this list.
                    e.drop.get('node').get('parentNode').insertBefore(drag, drop);
                    // Resize this nodes shim, so we can drop on it later.
                    e.drop.sizeShim();
                }
            });

            Y.DD.DDM.on('drag:drophit', function(e) {
                var drop = e.drop.get('node'),
                    drag = e.drag.get('node');

                // if we are not on an li, we must have been dropped on a ul.
                if (!drop.hasClass(CSS.QUICKLINK)) {
                    // if (!drop.contains(drag)) {
                        drop.appendChild(drag);
                    // }
                }
            });

        },

        savemove: function() {
            // var sortorder = Y.all(SELECTORS.DASHLINKCOURSEBOX).getData('courseid');

            // var params = {
            //     sesskey : M.cfg.sesskey,
            //     sortorder : sortorder
            // };

            // Y.io(URL, {
            //     method: 'POST',
            //     data: build_querystring(params),
            //     context: this,
            //     on: {
            //         end: function(id, e) {
            //             var favids = sortorder;
            //             Y.fire('culcourse-listing:update-dashlinks', {
            //                 dashlinks: favids
            //             });
            //         }
            //     }
            // });
        }

    }, {
        NAME : 'format_culcourse_dragdrop'

    });
M.format_culcourse = M.format_culcourse || {};
M.format_culcourse.init_dragdrop = function() {
    new DASHLINK();
};

