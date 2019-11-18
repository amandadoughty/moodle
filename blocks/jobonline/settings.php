<?php
/**
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

global $CFG;

$settings->add(new admin_setting_configcheckbox('block_jobonline/bounce',
                                                get_string('bounce','block_jobonline'),
                                                get_string('bounce_description','block_jobonline'),
                                                1));

$settings->add(new admin_setting_configtext('block_jobonline/feedurl',get_string('feedurl','block_jobonline'),get_string('feedurl_description','block_jobonline'),'',PARAM_URL));

$settings->add(new admin_setting_configpasswordunmask('block_jobonline/feedusername',get_string('feedusername','block_jobonline'),get_string('feedusername_description','block_jobonline'),"",PARAM_RAW,64));

$settings->add(new admin_setting_configpasswordunmask('block_jobonline/feedpassword',get_string('feedpassword','block_jobonline'),get_string('feedpassword_description','block_jobonline'),"",PARAM_RAW,64));

$settings->add(new admin_setting_configtextarea('block_jobonline/sectorlist',
                                                get_string('sectorlist','block_jobonline'),
                                                get_string('sectorlist_description','block_jobonline'),'',PARAM_TEXT));

$settings->add(new admin_setting_configtextarea('block_jobonline/locationlist',get_string('locationlist','block_jobonline'),get_string('locationlist_description','block_jobonline'),"",PARAM_TEXT));

$settings->add(new admin_setting_configduration('block_jobonline/feedcutoff',get_string('feedcutoff','block_jobonline'),get_string('feedcutoff_description','block_jobonline'),7*86400,86400));

$settings->add(new admin_setting_configtext_with_maxlength ('block_jobonline/listsize',get_string('listsize','block_jobonline'),get_string('listsize_description','block_jobonline'),"150",PARAM_INT,3,3));
