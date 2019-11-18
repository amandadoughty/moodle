// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Javascript controller for the "Grading" panel at the right of the page.
 *
 * @module     theme_cul_boost/grading_panel
 * @package    theme_cul_boost
 * @class      GradingPanel
 * @copyright  2017 Amanda Doughty
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.2
 */
define(['jquery', 'core/yui', 'core/notification', 'core/templates', 'core/fragment',
        'core/ajax', 'core/str', 'mod_assign/grading_panel'],
       function($, Y, notification, templates, fragment, ajax, str, GradingPanel) {

    /**
     * Update the grade alert.
     *
     * @private
     * @method _nicePrependGradeAlertContents
     * @param {JQuery} node
     * @param {String} html
     * @param {String} js
     * @return {Deferred} promise resolved when the animations are complete.
     */
    GradingPanel.prototype._nicePrependGradeAlertContents = function(node, html, js) {
        var promise = $.Deferred();

        node.fadeOut("fast", function() {
            templates.prependNodeContents(node, html, js);
            node.fadeIn("fast", function() {
                promise.resolve();
            });
        });

        return promise.promise();
    };

    /**
     * Add an alert indicating whether or not the grade is hidden in the gradebook.
     *
     * @private
     * @method _gradeHiddenAlert
     */
    GradingPanel.prototype._gradeHiddenAlert = function() {
        var contextid = this._region.attr('data-contextid');
        var assignmentid = this._region.attr('data-assignmentid');
        var courseid = $('[data-region="grading-navigation-panel"]').attr('data-courseid');
        var gradingform = $('.gradingform');

        // Tell behat to back off too.
        window.M.util.js_pending('theme-cul_boost-loading-grade-alert');

        // Update the page.
        if (this._lastUserId > 0) {
            // Reload the grading form "fragment" for this user.
            var params = {
                userid: this._lastUserId,
                attemptnumber: this._lastAttemptNumber,
                assignid: assignmentid,
                courseid: courseid
            };

            fragment.loadFragment('theme_cul_boost', 'gradealert', contextid, params).done(function(html, js) {
                this._nicePrependGradeAlertContents(gradingform, html, js)
                .done(function() {
                    // Tell behat we are friends again.
                    window.M.util.js_complete('theme-cul_boost-loading-grade-alert');
                }.bind(this))
                .fail(notification.exception);
            }.bind(this)).fail(notification.exception);
        } else {
            // Tell behat we are friends again.
            window.M.util.js_complete('theme-cul_boost-loading-grade-alert');
        }
    };

    /**
     * Register more event listeners for the grade panel.
     *
     * @method registerMoreEventListeners
     */
    GradingPanel.prototype.registerMoreEventListeners = function() {
        var docElement = $(document);
        docElement.on('finish-loading-user', this._gradeHiddenAlert.bind(this));
    };

    return GradingPanel;
});
