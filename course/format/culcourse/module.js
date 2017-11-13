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

// Cleaned through use of http://jshint.com/.
/**
 * @namespace
 */
M.format_culcourse = M.format_culcourse || {};

// Namespace variables:
M.format_culcourse.thesparezeros = "00000000000000000000000000"; // A constant of 26 0's to be used to pad the storage state of the toggles when converting between base 2 and 36, this is to be compact.
M.format_culcourse.togglestate;
M.format_culcourse.courseid;
M.format_culcourse.togglePersistence = 1; // Toggle persistence - 1 = on, 0 = off.
M.format_culcourse.ourYUI;
M.format_culcourse.numSections;
M.format_culcourse.ie8;

// Namespace constants:
M.format_culcourse.TOGGLE_6 = 1;
M.format_culcourse.TOGGLE_5 = 2;
M.format_culcourse.TOGGLE_4 = 4;
M.format_culcourse.TOGGLE_3 = 8;
M.format_culcourse.TOGGLE_2 = 16;
M.format_culcourse.TOGGLE_1 = 32;

/**
 * Initialise with the information supplied from the course format 'format.php' so we can operate.
 * @param {Object} Y YUI instance
 * @param {String} theCourseId the id of the current course to allow for settings for each course.
 * @param {String} theToggleState the current state of the toggles.
 * @param {Integer} theNumSections the number of sections in the course.
 * @param {Integer} theTogglePersistence Persistence on (1) or off (0).
 * @param {Integer} theDefaultTogglePersistence Persistence all open (1) or all closed (0) when thetogglestate is null.
 */
M.format_culcourse.init = function(Y, theCourseId, theToggleState, theNumSections, theTogglePersistence, theDefaultTogglePersistence) {
    "use strict";
    // Init.
    this.ourYUI = Y;
    this.courseid = theCourseId;
    this.togglestate = theToggleState;
    this.numSections = parseInt(theNumSections);
    this.togglePersistence = theTogglePersistence;

    // IE8 - humm!
    var bodyNode = Y.one(document.body);
    M.format_culcourse.ie8 = bodyNode.hasClass('ie8');
    if ((this.togglestate !== null) && (this.togglePersistence == 1)) { // Toggle persistence - 1 = on, 0 = off.
        if (this.is_old_preference(this.togglestate) == true) {
            // Old preference, so convert to new.
            this.convert_to_new_preference();
        }
        // Check we have enough digits for the number of toggles in case this has increased.
        var numdigits = this.get_required_digits(this.numSections);
        if (numdigits > this.togglestate.length) {
            var dchar;
            if (theDefaultTogglePersistence == 0) {
                dchar = this.get_min_digit();
            } else {
                dchar = this.get_max_digit();
            }
            for (var i = this.togglestate.length; i < numdigits; i++) {
                this.togglestate += dchar;
            }
        } else if (numdigits < this.togglestate.length) {
            // Shorten to save space.
            this.togglestate = this.togglestate.substring(0, numdigits);
        }
    } else {
        // Reset to default.
        if (theDefaultTogglePersistence == 0) {
            this.resetState(this.get_min_digit());
        } else {
            this.resetState(this.get_max_digit());
        }
    }

    // Info on http://yuilibrary.com/yui/docs/event/delegation.html
    // Delegated event handler for the toggles.
    // Inspiration thanks to Ben Kelada.
    // Code help thanks to the guru Andrew Nicols.
    Y.delegate('click', this.toggleClick, Y.config.doc, 'ul.culcourse .toggle', this);

    Y.delegate('click', this.toggle_showhide, Y.config.doc, 'ul.culcourse .editing_showhide', this);

    // Event handlers for all opened / closed.
    var allopen = Y.one("#toggles-all-opened");
    if (allopen) {
        allopen.on('click', this.allOpenClick);
    }
    var allclosed = Y.one("#toggles-all-closed");
    if (allclosed) {
        allclosed.on('click', this.allCloseClick);
    }
};

M.format_culcourse.toggleClick = function(e) {
    if (!e.target.test('.summary a')) {
        var toggleIndex = parseInt(e.currentTarget.get('id').replace("toggle-", ""));
        e.preventDefault();
        this.toggle_topic(e.currentTarget, toggleIndex);
    }
};

M.format_culcourse.allOpenClick = function(e) {
    e.preventDefault();
    M.format_culcourse.ourYUI.all(".toggledsection").addClass('sectionopen');
    M.format_culcourse.ourYUI.all(".toggle a.the_toggle").addClass('toggle_open').removeClass('toggle_closed');
    M.format_culcourse.ourYUI.all(".summary").addClass('summary_open').removeClass('summary_closed');
    M.format_culcourse.resetState(M.format_culcourse.get_max_digit());
    M.format_culcourse.save_toggles();
};

M.format_culcourse.allCloseClick = function(e) {
    e.preventDefault();
    M.format_culcourse.ourYUI.all(".toggledsection").removeClass('sectionopen');
    M.format_culcourse.ourYUI.all(".toggle a.the_toggle").addClass('toggle_closed').removeClass('toggle_open');
    M.format_culcourse.ourYUI.all(".summary").addClass('summary_closed').removeClass('summary_open');
    M.format_culcourse.resetState(M.format_culcourse.get_min_digit());
    M.format_culcourse.save_toggles();
};

M.format_culcourse.resetState = function(dchar) {
    M.format_culcourse.togglestate = "";
    var numdigits = M.format_culcourse.get_required_digits(M.format_culcourse.numSections);
    for (var i = 0; i < numdigits; i++) {
        M.format_culcourse.togglestate += dchar;
    }
};

// Toggle functions
// Args - targetNode that initiated the call, toggleNum the number of the toggle.
M.format_culcourse.toggle_topic = function(targetNode, toggleNum) {
    "use strict";
    var targetLink = targetNode.one('a.the_toggle');
    var targetsummary = targetNode.next('.toggledsection').one('.summary');
    var targetsummaryalwaysshown = targetNode.one('.summaryalwaysshown');
    var state;
    if (!targetLink.hasClass('toggle_open')) {
        targetLink.addClass('toggle_open').removeClass('toggle_closed');
        if (targetsummary) {
            targetsummary.removeClass('summary_closed');
            targetsummary.addClass('summary_open');
        }
        if (targetsummaryalwaysshown) {
            targetsummaryalwaysshown.removeClass('summary_closed');
            targetsummaryalwaysshown.addClass('summary_open');
        }
        targetNode.next('.toggledsection').addClass('sectionopen');
        state = true;
    } else {
        targetLink.addClass('toggle_closed').removeClass('toggle_open');
        if (targetsummary) {
            targetsummary.removeClass('summary_open');
            targetsummary.addClass('summary_closed');
        }
        if (targetsummaryalwaysshown) {
            targetsummaryalwaysshown.removeClass('summary_open');
            targetsummaryalwaysshown.addClass('summary_closed');
        }
        targetNode.next('.toggledsection').removeClass('sectionopen');
        state = false;
    }
    //IE 8 Hack/workaround to force IE8 to repaint everything
    if (M.format_culcourse.ie8) {
        M.format_culcourse.ourYUI.all(".toggle a.the_toggle").addClass('ie8_hackclass_donotuseincss').removeClass('ie8_hackclass_donotuseincss');
        console.log('IE8 repaint.');
    }

    this.set_toggle_state(toggleNum, state);
    this.save_toggles();
};

// Old maximum number of sections was 52, but as the conversion utilises integers which are 32 bit signed, this must be broken into two string segments for the
// process to work.  Therefore each 6 character base 36 string will represent 26 characters for part 1 and 27 for part 2 in base 2.
// This is all required to save cookie space, so instead of using 53 bytes (characters) per course, only 12 are used.
// Convert from a base 36 string to a base 2 string - effectively a private function.
// Args - thirtysix - a 12 character string representing a base 36 number.
M.format_culcourse.to2baseString = function(thirtysix) {
    "use strict";
    // Break apart the string because integers are signed 32 bit and therefore can only store 31 bits, therefore a 53 bit number will cause overflow / carry with loss of resolution.
    var firstpart = parseInt(thirtysix.substring(0,6),36);
    var secondpart = parseInt(thirtysix.substring(6,12),36);
    var fps = firstpart.toString(2);
    var sps = secondpart.toString(2);

    // Add in preceding 0's if base 2 sub strings are not long enough
    if (fps.length < 26) {
        // Need to PAD.
        fps = this.thesparezeros.substring(0,(26 - fps.length)) + fps;
    }
    if (sps.length < 27) {
        // Need to PAD.
        sps = this.thesparezeros.substring(0,(27 - sps.length)) + sps;
    }

    return fps + sps;
};

// Save the toggles - called from togglebinary and allToggle.
// AJAX call to server to save the state of the toggles for this course for the current user if on.
M.format_culcourse.save_toggles = function() {
    "use strict";
    if (this.togglePersistence == 1) { // Toggle persistence - 1 = on, 0 = off.
        M.format_culcourse.set_user_preference('culcourse_toggle_' + this.courseid, this.togglestate);
    }
};

// New base 64 code:
M.format_culcourse.is_old_preference = function(pref) {
    "use strict";
    var retr = false;
    var firstchar = pref[0];

    if ((firstchar == '0') || (firstchar == '1')) {
        retr = true;
    }

    return retr;
};

M.format_culcourse.convert_to_new_preference = function() {
    "use strict";
    var toggleBinary = this.to2baseString(this.togglestate);
    var bin, value;
    this.togglestate = "";
    var logbintext = "";

    for (var i = 1; i <= 43; i = i + 6) {
        bin = toggleBinary.substring(i, i + 6);
        value = parseInt(bin, 2);
        this.togglestate += this.encode_value_to_character(value);
        logbintext += bin + ' ';
    }

    bin = toggleBinary.substring(49, 53);
    logbintext += bin + ' ';
    value = parseInt(bin, 2);
    value = value << 2;
    this.togglestate += this.encode_value_to_character(value);
};

/**
 * Sets the state of the requested Toggle number.
 * int togglenum - The toggle number.
 * boolean state - true or false.
 */
M.format_culcourse.set_toggle_state = function(togglenum, state) {
    "use strict";
    var togglecharpos = this.get_toggle_pos(togglenum);
    var toggleflag = this.get_toggle_flag(togglenum, togglecharpos);
    var value = this.decode_character_to_value(this.togglestate.charAt(togglecharpos - 1));
    if (state == true) {
        value |= toggleflag;
    } else {
        value &= ~toggleflag;
    }
    var newchar = this.encode_value_to_character(value);
    var start = this.togglestate.substring(0,togglecharpos - 1);
    var end = this.togglestate.substring(togglecharpos);
    this.togglestate = start + newchar + end;
};

M.format_culcourse.get_required_digits = function(numtoggles) {
    "use strict";
    return this.get_toggle_pos(numtoggles);
};

M.format_culcourse.get_toggle_pos = function(togglenum) {
    "use strict";
    return Math.ceil(togglenum / 6);
};

M.format_culcourse.get_min_digit = function() {
    "use strict";
    return ':'; // 58 ':';
};

M.format_culcourse.get_max_digit = function() {
    "use strict";
    return 'y'; // 58 'y';
};

M.format_culcourse.get_toggle_flag = function(togglenum, togglecharpos) {
    "use strict";
    var toggleflagpos = togglenum - ((togglecharpos - 1) * 6);
    var flag;
    switch (toggleflagpos) {
        case 1:
            flag = this.TOGGLE_1;
            break;
        case 2:
            flag = this.TOGGLE_2;
            break;
        case 3:
            flag = this.TOGGLE_3;
            break;
        case 4:
            flag = this.TOGGLE_4;
            break;
        case 5:
            flag = this.TOGGLE_5;
            break;
        case 6:
            flag = this.TOGGLE_6;
            break;
    }
    return flag;
};

M.format_culcourse.decode_character_to_value = function(character) {
    "use strict";
    return character.charCodeAt(0) - 58;
}

M.format_culcourse.encode_value_to_character = function(val) {
    "use strict";
    return String.fromCharCode(val + 58);
};

/**
 * Makes a best effort to connect back to Moodle to update a user preference,
 * however, there is no mechanism for finding out if the update succeeded.
 *
 * Before you can use this function in your JavsScript, you must have called
 * user_preference_allow_ajax_update from moodlelib.php to tell Moodle that
 * the update is allowed, and how to safely clean and submitted values.
 *
 * @param String name the name of the setting to update.
 * @param String the value to set it to.
 */
M.format_culcourse.set_user_preference = function(name, value) {
    YUI().use('io', function(Y) {
        var url = M.cfg.wwwroot + '/course/format/culcourse/setculcoursepref.php?sesskey=' +
                M.cfg.sesskey + '&pref=' + encodeURI(name) + '&value=' + encodeURI(value);

        // If we are a developer, ensure that failures are reported.
        var cfg = {
                method: 'get',
                on: {}
            };
        if (M.cfg.developerdebug) {
            cfg.on.failure = function(id, o, args) {
                console.log("Error updating culcourse preference '" + name + "' using AJAX.  Almost certainly your session has timed out.  Clicking this link will repeat the AJAX call that failed so you can see the error: ");
            }
        }

        // Make the request.
        Y.io(url, cfg);
    });
};


/**
 * Toggle hiding the current section.
 *
 * @method toggle_showhide
 * @param {EventFacade} e
 */
M.format_culcourse.toggle_showhide = function(e) {

    var button = e.target.ancestor('a', true);
    var buttonicon = button.one('i');

    // Determine whether the section is currently hidden.
    var hidden = buttonicon.hasClass('fa-eye-slash');

    var hide_string = M.util.get_string('hidefromothers', 'format_culcourse');
    var show_string = M.util.get_string('showfromothers', 'format_culcourse');

    if (!hidden) {
        buttonicon
            .set('title', show_string)
            .removeClass('fa-eye')
            .addClass('fa-eye-slash');
    } else {
        buttonicon
            .set('title', hide_string)
            .removeClass('fa-eye-slash')
            .addClass('fa-eye');
    }
};
