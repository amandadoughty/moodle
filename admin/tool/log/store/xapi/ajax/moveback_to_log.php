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
 * Prepare events to replay.
 *
 * @package     logstore_xapi
 * @author      Záborski László <laszlo.zaborski@learningpool.com>
 * @copyright   2020 Learning Pool Ltd (http://learningpool.com)
 */

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))) . '/config.php');

require_login();

require_sesskey();

require_capability('logstore/xapi:manageerrors', context_system::instance());

$eventids = optional_param_array('events', 0, PARAM_INT);
$historical = optional_param('historical', 0, PARAM_BOOL);

$mover = new \logstore_xapi\log\moveback($eventids, $historical);

echo json_encode(['success' => $mover->execute()]);
