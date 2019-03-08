<?php
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
include('LtiConfiguration.php');
use Echo360\LtiConfiguration;

/**
 * Atto text editor integration version file.
 *
 * @package    atto_echo360attoplugin
 * @copyright  COPYRIGHTINFO
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

const PLUGIN_NAME = 'atto_echo360attoplugin';

const ERROR_CONTEXT = 'ERROR';
const DEBUG_CONTEXT = 'DEBUG';
const INFO_CONTEXT = 'INFO';

/**
 * Initialize this plugin
 */
function atto_echo360attoplugin_strings_for_js() {
  global $PAGE;

  $PAGE->requires->strings_for_js(array('dialogtitle', 'ltiConfiguration'), PLUGIN_NAME);
}

/**
 * Return the JavaScript params required for this module.
 *
 * @param $elementid
 * @param $options
 * @param $fpoptions
 * @return mixed
 */
function atto_echo360attoplugin_params_for_js($elementid, $options, $fpoptions) {
  global $COURSE;

  // config our array of data
  $params = array();
  $params['disabled'] = true;

  if (empty($COURSE)) { return $params; }
  // fetch the course context, https://docs.moodle.org/34/en/Context
  $context = context_course::instance($COURSE->id);
  if (empty($context)) { return $params; }
  try {
    $lti = new LtiConfiguration($context, PLUGIN_NAME);
    $lti_configuration = $lti->generate_lti_configuration();
    debug_to_console($lti_configuration, INFO_CONTEXT);
    $params['ltiConfiguration'] = LtiConfiguration::object_to_json($lti_configuration);
    // if they don't have permission don't show it
    $params['disabled'] = (!has_capability('atto/echo360attoplugin:visible', $context));
  } catch (Exception $e) {
    debug_to_console($e, ERROR_CONTEXT);
    $params['error'] = $e->getMessage();
  }
  return $params;
}

/**
 * Simple helper to debug to the browser console
 *
 * @param $data object|array
 * @param $context string  Optional a description.
 * @return void
 */
function debug_to_console($data, $context = DEBUG_CONTEXT) {
  ob_start();
  $output  = 'console.info(\'' . $context . ':\');';
  $output .= 'console.log(' . LtiConfiguration::object_to_json($data) . ');';
  $output  = sprintf('<script>%s</script>', $output);
  echo $output;
}
