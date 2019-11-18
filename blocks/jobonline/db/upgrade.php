<?php
/**
 * Block tgcfeed
 *
 * block_jobonline is distributed as GPLv3 software, and is provided free of charge without warranty.
 * A full copy of this licence can be found @
 * http://www.gnu.org/licenses/gpl.html
 *
 *
 * @package block_jobonline
 * @author Thomas Worthington
 * @copyright © 2018 University of London. All rights reserved.
 * @version 20180129
 */

include_once(__DIR__.'/../block_jobonline.php');

function xmldb_block_jobonline_upgrade($oldversion)
{
   global $DB,$CFG;
   $success=true;

   block_jobonline::readfeed();
   return $success;
}
