<?php

$find = '';
    
if ($findtitle) {
    $find .= html_writer::tag('h2', $findtitle, ['class'=>'findmodule-title text-primary']);
}

if ($findcontent) {
    $find .= html_writer::tag('div', $findcontent, ['class'=>'findmodule-content']);
}

$search = html_writer::empty_tag('input', ['class'=>'initialsearch', 'type'=>'text', 'title'=>get_string('coursesearch', 'theme_cul_boost'), 'autocomplete'=>'off', 'placeholder'=>get_string('coursesearch', 'theme_cul_boost')]);
$search = html_writer::tag('div', $search, ['class'=>'initialsearch-wrap d-inline-block mt-4']);

$find = html_writer::tag('div', $find.$search, ['class'=>'container-fluid text-center py-5']);

$blocks = '';
if ($hasdashcl) {
    $close = html_writer::tag('div', '', ['class'=>'close-icon']);
    $blocks = $OUTPUT->synergyblocks($dashcl, 'findblocks w-100');
    $blocks = html_writer::tag('div', $blocks.$close, ['class'=>'findblocks-wrap d-flex flex-wrap align-items-center jusitify-content-center p-4']);
}

echo html_writer::tag('div', $find.$blocks, ['class'=>'findmodule-wrap bg-light']);