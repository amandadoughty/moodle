/* favourites.js
 * copyright  2014 City University London
 * author     Amanda Doughty
 * license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

M.theme_cul_boost = M.theme_cul_boost || {};
M.theme_cul_boost.favourites =  {
    init: function () {
        Y.Global.on('culcourse-listing:update-favourites', function(data){
            Y.io(M.cfg.wwwroot+'/theme/cul_boost/favourites_ajax.php', {
                // data is not used, but keeping it here for now in case
                // I think of a better way of doing this
                data: data,
                on: {
                    success: function(id, e) {
                        var favourites = Y.one('li#theme-cul_boost-myfavourites');
                        var html = Y.JSON.parse(e.responseText);
                        var newnode = Y.Node.create(html);

                        if (favourites) {
                            favourites.remove();
                        }

                        Y.one('.navbar .nav-wrap .nav:nth-of-type(2)').prepend(newnode);
                    }
                }
            });
        });
    }
};