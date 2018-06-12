<?php

$find = '';
    
if ($findtitle) {
    $find .= html_writer::tag('h2', $findtitle, ['class'=>'findmodule-title text-primary']);
}

if ($findcontent) {
    $find .= html_writer::tag('div', $findcontent, ['class'=>'findmodule-content col-md-9']);
}

$search = html_writer::tag('label', get_string('coursesearch', 'theme_cul_boost'), ['class'=>'initialsearch', 'for'=>'ac-input']);
$search = html_writer::tag('div', $search, ['class'=>'initialsearch-wrap d-inline-block mt-4']);

$find = html_writer::tag('div', $find.$search, ['class'=>'container-fluid d-flex flex-column flex-wrap align-items-center text-center py-5']);

$blocks = '';
if ($hasdashcl) {
    $close = html_writer::tag('div', '', ['class'=>'close-icon']);
    $blocks = $OUTPUT->synergyblocks($dashcl, 'findblocks w-100');
    $blocks = html_writer::tag('div', $blocks.$close, ['class'=>'findblocks-wrap d-flex flex-wrap align-items-center jusitify-content-center p-4']);
}

echo html_writer::tag('div', $find.$blocks, ['class'=>'findmodule-wrap bg-light']);