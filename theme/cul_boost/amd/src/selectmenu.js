/*
 * @package    local_culrollover
 * @copyright  2016 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * @module local_culrollover/reviewsettings
  */

define(['jquery', 'jqueryui'], function($) {
    return {
        init: function () {
            $( ".formulation select" ).selectmenu();
        },
        _resizeMenu: function() {
          this.menu.outerWidth( 100 );
        }
    };

});