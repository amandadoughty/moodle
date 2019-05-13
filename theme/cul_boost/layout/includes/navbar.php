<?php

$navbar = '';
$leftnavbar = '';
$rightnavbar = '';
$settingsblock = '';

if (!isset($PAGE->layout_options['nonavbar']) || $PAGE->layout_options['nonavbar'] == false) { 

    if ($PAGE->pagelayout != 'mydashboard') {
        $leftnavbar = html_writer::tag('nav', $OUTPUT->navbar(), ['class'=>'breadcrumb-nav d-flex flex-wrap align-items-center justify-content-center justify-content-md-start col-12 col-md p-0', 'role'=>'navigation', 'aria-label'=>'breadcrumb']);
    }

    $rightnavbar = $this->page_heading_button();

    $icon = html_writer::tag('i', '', ['class'=>'fa fa-cog ml-2']);
	$text = html_writer::tag('span', get_string('settings'), ['class'=>'trigger-text line-height-1']);
	$settingsblock = html_writer::link('javascript:void(0)', $text.$icon, ['class'=>'trigger d-flex flex-wrap align-items-center h5 mb-0 text-white']);
    $settingsblock .= $OUTPUT->synergyblocks($navsettings, 'settings-block');
    $settingsblock = html_writer::tag('div', $settingsblock, ['class'=>'settings-block-wrap']);

    if ($PAGE->user_is_editing() || $PAGE->blocks->region_has_content($navsettings, $OUTPUT)) {
        $rightnavbar = html_writer::tag('div', $rightnavbar.$settingsblock, ['class'=>'right-navbar d-flex flex-wrap align-items-center bg-gray-700 p-3']);
        $rightnavbar = html_writer::tag('div', $rightnavbar, ['class'=>'right-navbar-wrap mt-3 mt-md-0 mx-auto mr-md-0 ml-md-auto']);
    }

    $close = html_writer::tag('div', '', ['class'=>'close-icon']);
    $overlay = html_writer::tag('div', $close, ['class'=>'overlay']);

    if (($PAGE->pagelayout == 'course' || $PAGE->pagelayout == 'incourse') && $PAGE->pagetype != 'notes-index') {
        echo html_writer::tag('div', $OUTPUT->page_heading(), ['class'=>'page-header container-fluid text-center text-md-left']);
    }

    $navbar = html_writer::tag('div', $leftnavbar.$overlay.$rightnavbar, ['class'=>'d-flex flex-wrap align-items-center mb-4']);

    echo html_writer::tag('div', $navbar, ['id'=>'page-navbar', 'class'=>'container-fluid']);
}