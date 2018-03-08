# Echo360-Atto-Plugin

This is the <a href="https://docs.moodle.org/dev/Atto">Atto</a> plugin for moodle which will display an echo360 button which when pressed would display video options from the user's library. 

To Install:

- Clone this repo
- Rename the top level dir to "echo360attoplugin"
- The source for the button is at yui/src/button/js/button.js. Changes to button.js won't do anything until you have
  run "shifter" over them. Go to yui/src/button and type "shifter"
- Copy "echo360attoplugin" to $MAMP_HOME/htdocs/moodle<version number>/lib/editor/atto/plugins
- Visit Settings > Site Administration > Notifications, and let Moodle guide you through the install (you may have to restart the MAMP server for the notification to appear).
- Add the Public / Private Keys as well as the Host URL from your Echo360 LTI configuration on the next page
- Go to Site Administration > Plugins > Text Editors > Atto Toolbar Settings and you should find that this plugin has been added to the list of installed modules.
- Add the tool name to the "Other" button group next to HTML editor see <a href="https://docs.moodle.org/27/en/Text_editor">https://docs.moodle.org/27/en/Text_editor</a> for help
