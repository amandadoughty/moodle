define(['jquery', 'core/ajax', 'core/templates', 'core/notification', 'core/str', 'core/url', 'core/yui',
        'core/modal_factory', 'core/modal_events', 'core/key_codes'],
    function($, ajax, templates, notification, str, url, Y, ModalFactory, ModalEvents, KeyCodes) {

// YUI.add('moodle-block_culcourse_listing-favourite', function(Y) {

//     var FAVOURITENAME = 'blocks_culcourse_listing_favourite';
//     var FAVOURITE = function() {
//         FAVOURITE.superclass.constructor.apply(this, arguments);
//     };

    var CSS = {
            BLOCK: 'block_culcourse_listing',
            COURSEBOX: 'culcoursebox',
        };
    var SELECTORS = {
        COURSEBOX: '.culcoursebox',
        FAVOURITELIST: '.favourite_list',
        FAVOURITECOURSEBOX: '.favourite_list .culcoursebox',
        FAVOURITEMOVEWITHOUTJS: '.moveicons',
        FAVOURITEMOVE: '.favourite_list .move',
        COURSEBOXLINK: '.info',
    };
    var URL = M.cfg.wwwroot + '/blocks/culcourse_listing/move_ajax.php';

// Y.extend(FAVOURITE, Y.Base, {



    var addmoveicon = function(params) {
        // Replace the non-JS links.
        var move = M.util.get_string('move', 'block_culcourse_listing');
        var newdiv = Y.Node.create('<div class="move"></div>');
        var icon = Y.Node.create(
            '<img src="' + M.util.image_url('i/move_2d', 'moodle') +
            '" alt="' + move +
            '" title="' + move +
            '" class="cursor"/>'
            );
        newdiv.append(icon);

        if (params.node.one(SELECTORS.FAVOURITEMOVEWITHOUTJS)) {
            params.node.one(SELECTORS.FAVOURITEMOVEWITHOUTJS).replace(newdiv);
        } else {
            params.node.one(SELECTORS.COURSEBOXLINK).prepend(newdiv);
        }
    };

    var adddragdrop = function(params) {
        Y.use('base', 'dd-constrain', 'dd-proxy', 'dd-drop', 'dd-delegate', 'dd-plugin', function() {
            addmoveicon(params);
            // Static Vars.
            var goingUp = false, lastY = 0;
            // var savemove = savemove;

            var d = new Y.DD.Drag({
                    node: params.node,
                    target: true
                }).plug(Y.Plugin.DDProxy, {
                    moveOnEnd: false,
                    cloneNode: true
                }).plug(Y.Plugin.DDConstrained, {
                    constrain2node: SELECTORS.FAVOURITELIST
                });
                d.addHandle(SELECTORS.FAVOURITEMOVE);

            Y.DD.DDM.on('drag:start', function(e) {
                // Get our drag object.
                var drag = e.target;
                // Set some styles here.
                drag.get('node').setStyle('opacity', '.25');
                drag.get('dragNode').addClass(CSS.BLOCK);
                drag.get('dragNode').set('innerHTML', drag.get('node').get('innerHTML'));
                drag.get('dragNode').setStyles({
                    opacity: '.5',
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
                savemove();
            });

            Y.DD.DDM.on('drag:drag', function(e) {
                // Get the last y point.
                var y = e.target.lastXY[1];

                // Is it greater than the lastY var?
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
                if (drop.hasClass(CSS.COURSEBOX)) {
                    // Are we not going up?
                    if (!goingUp) {
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
                if (!drop.hasClass(CSS.COURSEBOX)) {
                    if (!drop.contains(drag)) {
                        drop.appendChild(drag);
                    }
                }
            });
        });
    };

    var savemove = function() {
        var sortorder = Y.all(SELECTORS.FAVOURITECOURSEBOX).getData('courseid');

        var params = {
            sesskey : M.cfg.sesskey,
            sortorder : sortorder
        };

        Y.io(URL, {
            method: 'POST',
            data: build_querystring(params),
            context: this,
            on: {
                end: function(id, e) {
                    var favids = sortorder;
                    Y.fire('culcourse-listing:update-favourites', {
                        favourites: favids
                    });
                }
            }
        });
    };

    return {
        initializer: function(params) {
            adddragdrop(params);
        }
    };    

  //   }, {
  //       NAME : FAVOURITENAME,
  //       ATTRS : {
  //           node : {
  //               value : null
  //           }
  //       }
  //   });
  //   M.blocks_culcourse_listing = M.blocks_culcourse_listing || {};
  //   M.blocks_culcourse_listing.init_favourite = function(params) {
  //       return new FAVOURITE(params);
  //   };
  // }, '@VERSION@', {
  //     requires:['base', 'dd-constrain', 'dd-proxy', 'dd-drop', 'dd-delegate', 'dd-plugin']
});
