<?php
$cityrenderer = $PAGE->get_renderer('theme_cul_boost', 'city_menu');
$citycore = $PAGE->get_renderer('theme_cul_boost', 'city_core');
$globalnav = $cityrenderer->city_global_navigation();
$help = $OUTPUT->help();
$helpmobile = $OUTPUT->help_mobile();
$logo = $cityrenderer->get_logo();
$whitelogo = $cityrenderer->get_white_logo();
$custommenu = $citycore->custom_menu();
$search = $OUTPUT->global_search();

$headertemplatecontext = [
    'globalnav' => $globalnav,
    'help' => $help,
    'helpmobile' => $helpmobile,
    'logo' => $logo,
    'whitelogo' => $whitelogo,
    'custommenu' => $custommenu,
    'search' => $search,
];