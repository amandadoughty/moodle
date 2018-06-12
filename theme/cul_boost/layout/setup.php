<?php

$cityrenderer = $PAGE->get_renderer('theme_cul_boost', 'city_menu');
$citycore = $PAGE->get_renderer('theme_cul_boost', 'city_core');
$visible = theme_cul_boost_get_visible();
$edituser = theme_cul_boost_get_edituser($PAGE);
theme_cul_boost_initialise_favourites($PAGE);

// Block region setup
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$knownregionpost = $PAGE->blocks->is_known_region('side-post');
$regions = theme_cul_boost_bootstrap_grid($hassidepost);
$hasnavsettings = (empty($PAGE->layout_options['noblocks']));

// Dashboard Blocks
$hasdashcl = (empty($PAGE->layout_options['noblocks']));
$hasdashfull = (empty($PAGE->layout_options['noblocks']));
$hasdashleft = (empty($PAGE->layout_options['noblocks']));
$hasdashmiddle = (empty($PAGE->layout_options['noblocks']));
$hasdashright = (empty($PAGE->layout_options['noblocks']));

$navsettings = 'nav-settings';
$dashcl = 'dash-cl';
$dashf = 'dash-full';
$dashl = 'dash-left';
$dashm = 'dash-middle';
$dashr = 'dash-right';

// Dashboard Settings
$findtitle = (empty($PAGE->theme->settings->findtitle)) ? get_string('findamodule', 'theme_cul_boost') : $PAGE->theme->settings->findtitle;
$findcontent = (empty($PAGE->theme->settings->findcontent)) ? false : $PAGE->theme->settings->findcontent;

// footer
$footnote = (empty($PAGE->theme->settings->footnote)) ? false : $PAGE->theme->settings->footnote;
$copyright = (empty($PAGE->theme->settings->copyright)) ? false : $PAGE->theme->settings->copyright;
$footerlinks = (empty($PAGE->theme->settings->footerlinks)) ? false : $PAGE->theme->settings->footerlinks;
