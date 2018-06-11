YUI.add('moodle-theme_cul_boost-favourite', function (Y, NAME) {

/* favourite.js
 * copyright  2014 City University London
 * author     Amanda Doughty
 * license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/

M.theme_cul_boost = M.theme_cul_boost || {};
M.theme_cul_boost.favourite =  {

    editlink: null,

    init: function () {
        if (Y.one('#theme-cul_boost-removefromfavourites')) {
            this.editlink = Y.one('#theme-cul_boost-removefromfavourites');
            this.editlink.on('click', this.editfavourite, this);
        }
        else if (Y.one('#theme-cul_boost-addtofavourites')) {
            this.editlink = Y.one('#theme-cul_boost-addtofavourites');
            this.editlink.on('click', this.editfavourite, this);
        }

        Y.publish('culcourse-listing:update-favourites', {
            broadcast:2
        });
    },
    editfavourite: function (e) {
        e.preventDefault();

        var href = e.target.get('href').split('?');
        var name = this.editlink.id;
        console.log(name);
        var url = href[0];
        var querystring = href[1];

        Y.io(M.cfg.wwwroot+'/theme/cul_boost/favourite_ajax.php', {
            method: 'POST',
            context: this,
            data: querystring,
            on: {
                success: function(id, e) {
                    data = Y.JSON.parse(e.responseText);
                    var link = this.editlink.one('a');
                    var newurl = url + '?' + querystring.replace(data.action, data.newaction);
                    link.set('href', newurl);
                    link.set('innerHTML', data.text);
                    link.set('title', data.text);
                },
                end: function() {
                    Y.fire('culcourse-listing:update-favourites');
                }
            }
        });
    }
};




}, '@VERSION@', {"requires": ["node", "querystring-parse", "json-parse"]});
