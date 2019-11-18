<?php

$hasnavbar = !isset($PAGE->layout_options['nonavbar']) || $PAGE->layout_options['nonavbar'] == false;
$haspageheading = ($PAGE->pagelayout == 'course' || $PAGE->pagelayout == 'incourse') && $PAGE->pagetype != 'notes-index';
$hasleftnavbar = $PAGE->pagelayout != 'mydashboard';
$hasnavsettings = $PAGE->user_is_editing() || $PAGE->blocks->region_has_content('nav-settings', $OUTPUT);
$settingsblock = '';

if ($hasnavbar) {    
    $settingsblock = $OUTPUT->synergyblocks('nav-settings', 'settings-block');    
}

$navbartemplatecontext = [
    'hasnavbar' => $hasnavbar,
    'haspageheading' => $haspageheading,
    'hasleftnavbar' => $hasleftnavbar,
    'hasnavsettings' => $hasnavsettings,
    'settingsblock' => $settingsblock,
];