<?php
/**
 * Version details
 *
 * @package    jobonline
 * @copyright  2018 onwards University of London
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname']='JobOnline Block';
$string['pluginnameplural'] = 'JobOnline Blocks';

$string['bounce']='Bounce block into centre';
$string['bounce_description']='If selected, the block will force its way into the main content part of any page it appears on. When not selected, it acts like any other block in the current theme.';

$string['feedurl']='Jobs Feed Url';
$string['feedurl_description']='The full url of the jobs feed, including the "HTTP" or "HTTPS" part.';

$string['feedusername']='User name for job feed.';
$string['feedusername_description']='This should be the raw text, not the base64 version.';

$string['feedpassword']='Password for job feed.';
$string['feedpassword_description']='This should be the raw text, not the base64 version.';

$string['sectorlist']='Priority Sectors';
$string['sectorlist_description']="List of business areas which should be listed at the top of the selection. Options should be separated by a | character. There should be no | at the start or end of the list.";

$string['locationlist']='Priority Locations';
$string['locationlist_description']="List of opportunity types which can be selected in the block's filter. Options should be separated by a | character. There should be no | at the start or end of the list.";

$string['listsize']='Maximum number of jobs';
$string['listsize_description']='The maximum number of jobs to show in the block. 0 means to show all matching jobs (not recommended). This overrides the cutoff date above.';

$string['feedcutoff']='Latest date to show';
$string['feedcutoff_description']='This time is added to the current time and any jobs with a closing date beyond that are not shown.';

$string['employername_label']='Employer:';
$string['postingdate_label']='Posted:';
$string['closingdate_label']='Closing Date:';
$string['application_label']='Application to:';
$string['applicationlinkname']='Click here to apply (opens new window)';

$string['ending-sort']='Closing Soon';
$string['post-sort']='Newly listed';

$string['general_cron']='JobOnline Feed Refresh';
$string['notsetup']='JobOnline not configured, check <a href="/admin/settings.php?section=blocksettingjobonline">url and/or password settings</a>.';
