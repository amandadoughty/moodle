<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('block_culschool_html_allowcssclasses', get_string('allowadditionalcssclasses', 'block_culschool_html'),
                       get_string('configallowadditionalcssclasses', 'block_culschool_html'), 0));
}


