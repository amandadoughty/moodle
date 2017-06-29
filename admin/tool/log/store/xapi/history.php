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

/**
 * Process events from standard log. 
 * !!! Use with caution to avoid duplicated statements !!!
 *
 * @package    logstore_xapi
 * @copyright  2015 Watershed Systems Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_xapi\task;

use tool_log\log\manager;
use logstore_xapi\log\store;

require_once(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))).'/config.php');

global $DB;

$manager = get_log_manager();
$store = new store($manager);

$firsttimestamp = date('U',strtotime('2016-03-19T03:08:02.000Z'));
echo ($firsttimestamp.'<br/>');
$events;
$counter = 0;
$totalStatements = 0;
$BATCH_SIZE = 100;
$time_start = microtime(true);
do {
	$limitfrom = $counter * $BATCH_SIZE;
	$events = $DB->get_records_select('logstore_standard_log','timecreated >= '.$firsttimestamp, null,'','*',$limitfrom,$BATCH_SIZE);
	echo('<pre>');
	var_dump($events);
	echo('</pre><hr/>');
	
	$statementCount = $store->process_events($events);
	$totalStatements += $statementCount;
	$counter++;
	mtrace("[".(microtime(true)-$time_start)."] ");
	mtrace($statementCount." statements sent in batch ".$counter.". Total: ".$totalStatements.".<br/>");
} while (sizeof($events) >0); 