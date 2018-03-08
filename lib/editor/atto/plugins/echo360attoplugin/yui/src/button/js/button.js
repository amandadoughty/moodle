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

/*
 * @package    atto_echo360attoplugin
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * @module moodle-atto_echo360attoplugin-button
 */

/**
 * Atto text editor echo360attoplugin plugin.
 * @namespace M.atto_echo360attoplugin
 * @class button
 * @extends M.editor_atto.EditorPlugin
 */

var LOG_NAME = COMPONENT_NAME = 'atto_echo360attoplugin';
var EDITOR_ID = '';
var ECHO360 = 'echo360';
var MEDIA = null;
var MESSAGE_DATA = null;
var ADD_MESSAGE_HANDLER = {};
var EDITOR_INSTANCE = {};

Y.namespace('M.atto_echo360attoplugin').Button = Y.Base.create('button', Y.M.editor_atto.EditorPlugin, [], {

  /**
   * Initialize the button
   * @method Initializer
   */
  initializer: function () {
    // If we don't have the capability to view then give up.
    if (this.get('disabled')) {
      // It may not be disabled because of an error
      this.get('error') && console.error(this.get('error'));
      return;
    }
    EDITOR_ID = this.editor._yuid;

    var icon = 'echoIcon',
        json = this.get('ltiConfiguration'),
        ltiConfiguration = JSON.parse(json),
        iframeName = 'echo360-library-' + EDITOR_ID,
        iframe = document.createElement('iframe');

    iframe.id = iframe.name = iframeName;
    iframe.setAttribute('height', '500px');
    iframe.setAttribute('width', '100%');

    // Configure the LTI authentication form to target the iframe on submit
    var form = document.createElement('form');
    form.setAttribute('method', 'post');
    form.setAttribute('id', 'echo360-form' + EDITOR_ID);
    form.setAttribute('target', iframeName);
    form.setAttribute('hidden', 'true');
    form.action = ltiConfiguration.launch_url;

    // Create an input field in the form for each LTI parameter
    for (var property in ltiConfiguration) {
      if (ltiConfiguration.hasOwnProperty(property)) {
        var input = document.createElement('input');
        input.setAttribute('type', 'text');
        input.setAttribute('value', ltiConfiguration[property]);
        input.id = input.name = property;
        form.appendChild(input);
      }
    }
    // The form must be part of the DOM before you can submit it
    document.body.appendChild(form);

    var dialogue = this.getDialogue({
      headerContent: M.util.get_string('dialogtitle', COMPONENT_NAME),
      focusAfterHide: this.editor,
      width: 800
    });

    this._attachIframeToDialogue(dialogue, iframe, form);

    // The button will display the Dialogue modal
    this.addButton({
      icon: 'ed/' + icon,
      iconComponent: 'atto_echo360attoplugin',
      buttonName: icon,
      callback: this._doOpen,
      callbackArgs: null,
      name: ECHO360,
      tooltip: ECHO360
    });
  },

  /**
   * Attaches the IFrame to the Dialogue modal
   * @method _attachIframeToDialogue
   * @param dialogue {Element} The dialogue element that serves as a container for the iframe
   * @param iframe {Element} The iframe element to display the user library
   * @param form {Element} The form element to submit the LTI auth request targeting the iframe
   * @private
   */
  _attachIframeToDialogue: function (dialogue, iframe, form) {
    // Dialogue modal must be showing in order to append the iframe properly.
    // It will happen so fast that the end user will not see it
    dialogue.render();
    // Append the iframe to the Dialogue modal, submit the form, then remove it
    dialogue.bodyNode.appendChild(iframe);
    form.submit();
    if(this._browserIsIE()){
      form.parentNode.removeChild(form);
    } else {
      form.remove();
    }
  },

  /**
   * Opens the modal and displays the Echo360 user library
   * @method _doOpen
   * @param e {Object} the event object
   * @param dialogue {Element} the dialogue modal element
   * @private
   */
  _doOpen: function (e) {
    e.preventDefault();
    this._resetVariablesInScope();
    // Show the Dialogue modal and add an event listener
    // for messages sent from the iframe
    EDITOR_INSTANCE = {
      host: this.get('host'),
      editor: this.editor,
      dialogue: this.getDialogue()
    };
    EDITOR_INSTANCE.dialogue.show();
    if(!ADD_MESSAGE_HANDLER[EDITOR_ID]){
      window.addEventListener('message', function (e) {
        e.stopPropagation();
        return this._receiveMessage(e);
      }.bind(this), true);
    }
    ADD_MESSAGE_HANDLER[EDITOR_ID] = true;
    this.markUpdated();
  },

  /**
   * Handle the message received from the Echo360 user library
   * @method _receiveMessage
   * @param e {Object} the Message object sent from the IFrame.
   * Should contain information about the media we wish to embed
   * @private
   */
  _receiveMessage: function (e) {
    // Only accept messages from Echo360
    if (this._browserIsIE() || e.origin.includes(ECHO360)) {
      MESSAGE_DATA = e.data;
      if (MESSAGE_DATA) {
        var queryParameters = this._getQueryParams();
        if (queryParameters) {
          switch (queryParameters.return_type) {
            case 'iframe':
              MEDIA = '<iframe ' + 'src="' + queryParameters.url +
                  '" height="' + queryParameters.height +
                  '" width="' + queryParameters.width +
                  '" title="' + queryParameters.title +
                  '"></iframe> ';
              break;
            case 'url':
            case 'lti_launch_url':
              MEDIA = '<a href="' + queryParameters.url + '" target="_blank">' + queryParameters.title + '</a> ';
              break;
            default:
              console.error('Return type: ' + queryParameters.return_type + ' invalid');
              this._resetVariablesInScope();
          }
        } else {
          console.error('No parameters returned from parsed message: ' + MESSAGE_DATA);
          this._resetVariablesInScope();
        }
      } else{
        console.error('No data returned from message: ' + e);
        this._resetVariablesInScope();
      }
      this._doInsert();
    }
  },

  /**
   * Returns the query parameters from MESSAGE_DATA as an object
   * @method _getQueryParams
   * @private
   */
  _getQueryParams: function () {
    try {
      var queryParams = JSON.parse('{"' + decodeURI(MESSAGE_DATA.split('?')[1]).replace(/"/g, '\\"').replace(/&/g, '","').replace(/=/g, '":"') + '"}');
      if (!queryParams.hasOwnProperty('url')) {
        console.error('Required parameter "url" missing from message: ' + MESSAGE_DATA);
        return null;
      } else {
        queryParams.url = decodeURIComponent(queryParams.url);
      }
      queryParams.title = queryParams.title || queryParams.url;
      return queryParams;
    } catch (err) {
      console.error('Error caught while attempting to parse query parameters in message: ' + MESSAGE_DATA + '\n' + err.message);
      return null;
    }
  },

  /**
   * Inserts the users input onto the page
   * @method _doInsert
   * @private
   */
  _doInsert: function () {
    EDITOR_INSTANCE.dialogue.hide();
    // If no file is there to insert, don't do it.
    if (!MEDIA) {
      Y.log('If no file is there to insert, dont do it.', 'warn', LOG_NAME);
      return;
    }
    EDITOR_INSTANCE.editor.focus();
    EDITOR_INSTANCE.host.insertContentAtFocusPoint(MEDIA);
    this._resetVariablesInScope();
    this.markUpdated();
  },

  /**
   * Resets MEDIA and MESSAGE_DATA
   * @method _resetVariablesInScope
   * @private
   */
  _resetVariablesInScope: function () {
    MEDIA = null;
    MESSAGE_DATA = null;
    EDITOR_INSTANCE = {};
  },

  _browserIsIE: function () {
    return (
      navigator.appName == 'Microsoft Internet Explorer' ||
      !!(navigator.userAgent.match(/Trident/) ||
      navigator.userAgent.match(/rv:11/)) ||
      !!document.documentMode == true ||
      navigator.userAgent.indexOf("MSIE") != -1
    )
  }

}, {
  ATTRS: {
    disabled: {
      value: false
    },

    usercontextid: {
      value: null
    },

    ltiConfiguration: {
      value: ''
    }
  }
});
