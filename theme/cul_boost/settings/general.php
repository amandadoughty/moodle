<?php

if (!$ADMIN->locate('theme_cul_boost')) {
    $ADMIN->add('themes', new admin_category('theme_cul_boost', get_string('configtitle', 'theme_cul_boost')));
}

$temp = new admin_settingpage('theme_cul_boost_general',  get_string('generalsettings', 'theme_cul_boost'));


    // Logo file setting.
    $name = 'theme_cul_boost/logo';
    $title = get_string('logo', 'theme_cul_boost');
    $description = get_string('logodesc', 'theme_cul_boost');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'logo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Help & Support
    $name = 'theme_cul_boost/customhelpmenuitems';
    $title = get_string('helptext', 'theme_cul_boost');
    $description = get_string('helptextdesc', 'theme_cul_boost');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Years
    $name = 'theme_cul_boost/years';
    $title = get_string('years', 'theme_cul_boost');
    $description = get_string('yearsdesc', 'theme_cul_boost');
    $default = 3;
    $setting = new admin_setting_configselect($name, $title, $description, $default, range(0,10));
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Footnote
    $name = 'theme_cul_boost/footnote';
    $title = get_string('footnote', 'theme_cul_boost');
    $description = get_string('footnotedesc', 'theme_cul_boost');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Copyright
    $name = 'theme_cul_boost/copyright';
    $title = get_string('copyright', 'theme_cul_boost');
    $description = get_string('copyrightdesc', 'theme_cul_boost');
    $default = get_string('copyrightdefault', 'theme_cul_boost');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);

    // Footerlinks
    $name = 'theme_cul_boost/footerlinks';
    $title = get_string('footerlinks', 'theme_cul_boost');
    $description = get_string('footerlinksdesc', 'theme_cul_boost');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    $temp->add(new admin_setting_heading('theme_cul_boost_advanced', get_string('advanced'), ''));

    // Custom CSS file.
    $name = 'theme_cul_boost/customcss';
    $title = get_string('customcss', 'theme_cul_boost');
    $description = get_string('customcssdesc', 'theme_cul_boost');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google Analytics
    $name = 'theme_cul_boost/gakey';
    $title = get_string('gakey', 'theme_cul_boost');
    $description = get_string('gakeydesc', 'theme_cul_boost');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);

    // Google Analytics consent
    $policyopts = [];
    try {
        $policies = tool_policy\api::list_current_versions(tool_policy\policy_version::AUDIENCE_GUESTS);
    } catch (Exception $e) {
        $policies = false;
    }

    if ($policies) {
        foreach ($policies as $policy) {
            $policyopts[$policy->id] = $policy->name;
        }

        $name = 'theme_cul_boost/cookiepolicy';
        $title = get_string('cookiepolicy', 'theme_cul_boost');
        $description = get_string('cookiepolicydesc', 'theme_cul_boost');
        $default = null;
        $setting = new admin_setting_configselect($name, $title, $description, $default, $policyopts);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $temp->add($setting);
    }

if (!$ADMIN->locate($temp->name)) {
    $ADMIN->add('theme_cul_boost', $temp);
}