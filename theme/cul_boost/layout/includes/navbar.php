<?php

$navbar = '';
$leftnavbar = '';
$rightnavbar = '';
$settingsblock = '';

if (!isset($PAGE->layout_options['nonavbar']) || $PAGE->layout_options['nonavbar'] == false) { 

    if ($PAGE->pagelayout != 'mydashboard') {
        $leftnavbar = html_writer::tag('nav', $OUTPUT->navbar(), ['class'=>'breadcrumb-nav d-flex flex-wrap align-items-center col p-0', 'role'=>'navigation', 'aria-label'=>'breadcrumb']);
    }

    $rightnavbar = html_writer::tag('div', $this->page_heading_button(), ['class'=>'breadcrumb-button d-flex flex-wrap ml-auto mt-0']);

    $icon = html_writer::tag('i', '', ['class'=>'fa fa-cog ml-2']);
	$text = html_writer::tag('span', get_string('settings'), ['class'=>'trigger-text']);
	$settingsblock = html_writer::link('javascript:void(0)', $text.$icon, ['class'=>'trigger d-flex flex-wrap align-items-center h5 mb-0 text-white']);
    $settingsblock .= $OUTPUT->synergyblocks($navsettings, 'settings-block');
    $settingsblock = html_writer::tag('div', $settingsblock, ['class'=>'settings-block-wrap']);

    if ($PAGE->user_is_editing() || $PAGE->blocks->region_has_content($navsettings, $OUTPUT)) {
        $rightnavbar = html_writer::tag('div', $rightnavbar.$settingsblock, ['class'=>'right-navbar d-flex flex-wrap align-items-center bg-gray-500 p-3']);
        $rightnavbar = html_writer::tag('div', $rightnavbar, ['class'=>'right-navbar-wrap ml-auto']);
    }

    $overlay = html_writer::tag('div', '', ['class'=>'overlay']);

    $navbar = html_writer::tag('div', $leftnavbar.$overlay.$rightnavbar, ['id'=>'page-navbar', 'class'=>'d-flex flex-wrap align-items-center pb-2 mb-4']);

    echo $navbar;
    
}