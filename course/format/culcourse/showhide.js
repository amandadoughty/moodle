/**
 * CUL Course Format Information
 *
 * A collapsed format that solves the issue of the 'Scroll of Death' when a course has many sections. All sections
 * except zero have a toggle that displays that section. One or more sections can be displayed at any given time.
 * Toggles are persistent on a per browser session per course basis but can be made to persist longer.
 *
 * @package    course/format
 * @subpackage culcourse
 * @version    See the value of '$plugin->version' in version.php.
 * @author     Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @namespace
 */
M.format_culcourse_showhide = M.format_culcourse_showhide || {};


M.format_culcourse_showhide.init = function(Y) {

    Y.log('boo');

    // Y.delegate('click', this.toggle_showhide, Y.config.doc, 'ul.culcourse .editing_showhide', this);

};


/**
 * Toggle hiding the current section.
 *
 * @method toggle_showhide
 * @param {EventFacade} e
 */
// M.format_culcourse_showhide.toggle_showhide = function(e) {

    // var button = e.target.ancestor('a', true);
    // var buttonicon = button.one('i');

    // // Determine whether the section is currently hidden.
    // var hidden = buttonicon.hasClass('fa-eye-slash');

    // var hide_string = M.util.get_string('hidefromothers', 'moodle');
    // var show_string = M.util.get_string('showfromothers', 'moodle');

    // if (!hidden) {          
    //     buttonicon
    //         .set('title', show_string)
    //         .removeClass('fa-eye')
    //         .addClass('fa-eye-slash');
    // } else {
    //     buttonicon
    //         .set('title', hide_string)
    //         .removeClass('fa-eye-slash')
    //         .addClass('fa-eye');
    // }
// };

