<?php
if (!$ADMIN->locate('theme_cul_boost')) {
    $ADMIN->add('themes', new admin_category('theme_cul_boost', get_string('configtitle', 'theme_cul_boost')));
}

$temp = new theme_cul_boost_admin_settingspage_tabs('theme_cul_boost_dashboard', get_string('dashboard', 'theme_cul_boost'));

$main = new admin_settingpage('theme_cul_boost_dashboard',  get_string('dashboard', 'theme_cul_boost'));

    // Frontpage Slider
    $main->add(new admin_setting_heading('theme_cul_boost_imageslider', get_string('frontpageslider', 'theme_cul_boost'),
            format_text(get_string('frontpagesliderdesc' , 'theme_cul_boost'), FORMAT_MARKDOWN)));

    $name = 'theme_cul_boost/usefrontpageslider';
    $title = get_string('usefrontpageslider', 'theme_cul_boost');
    $description = get_string('usefrontpagesliderdesc', 'theme_cul_boost');
    $default = false;
    $setting = new admin_setting_configcheckbox($name, $title, $description, $default, true, false);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $main->add($setting);

    $main->add(new admin_setting_heading('theme_cul_boost_findamodule', get_string('findamodule', 'theme_cul_boost'),
            format_text(get_string('findamoduledesc' , 'theme_cul_boost'), FORMAT_MARKDOWN)));

    $name = 'theme_cul_boost/findtitle';
    $title = get_string('findtitle', 'theme_cul_boost');
    $description = get_string('findtitledesc', 'theme_cul_boost');
    $default = get_string('findamodule', 'theme_cul_boost');
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $main->add($setting);

    $name = 'theme_cul_boost/findcontent';
    $title = get_string('findcontent', 'theme_cul_boost');
    $description = get_string('findcontentdesc', 'theme_cul_boost');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $main->add($setting);

    $temp->add($main);

    $slides = 6;

    for ($i = 1; $i <= $slides; $i++) {

        $main = new admin_settingpage('theme_cul_boost_slider'.$i, get_string('slide', 'theme_cul_boost', $i));

        $slidename = 'slide'.$i;

        $name = 'theme_cul_boost/info_'.$slidename;
        $heading = get_string('slide', 'theme_cul_boost', $i);
        $information = get_string('slideinfodesc', 'theme_cul_boost');
        $setting = new admin_setting_heading($name, $heading, $information);
        $main->add($setting);

        // Image
        $name = "theme_cul_boost/image_$slidename";
        $title = get_string('slideimage', 'theme_cul_boost');
        $description = get_string('slideimagedesc', 'theme_cul_boost');
        $setting = new admin_setting_configstoredfile($name, $title, $description, "image_$slidename");
        $setting->set_updatedcallback('theme_reset_all_caches');
        $main->add($setting);

        // Caption
        $name = "theme_cul_boost/caption_$slidename";
        $title = get_string('slidecaption', 'theme_cul_boost');
        $description = get_string('slidecaptiondesc', 'theme_cul_boost');
        $setting = new admin_setting_confightmleditor($name, $title, $description, '');
        $setting->set_updatedcallback('theme_reset_all_caches');
        $main->add($setting);

        // URL
        $name = "theme_cul_boost/url_$slidename";
        $title = get_string('slideurl', 'theme_cul_boost');
        $description = get_string('slideurldesc', 'theme_cul_boost');
        $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $main->add($setting);

        $temp->add($main);

    }


if (!$ADMIN->locate($temp->name)) {
    $ADMIN->add('theme_cul_boost', $temp);
}
