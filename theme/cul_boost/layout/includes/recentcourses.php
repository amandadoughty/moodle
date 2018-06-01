<?php 

$courses = enrol_get_my_courses();

$content = '';
if (!empty($OUTPUT->recent_courses($courses))) {

	$title = html_writer::tag('h2', get_string('recentmodules', 'theme_cul_boost'), ['class'=>'section-title mb-0']);
	$buttons = html_writer::tag('div', get_string('allmodules', 'theme_cul_boost'), ['class'=>'allmodules-btn btn btn-primary ml-auto']);
	$buttons .= html_writer::tag('div', get_string('favourites', 'theme_cul_boost'), ['class'=>'favourites-btn btn btn-primary ml-2']);

	$content = html_writer::tag('div', $title.$buttons, ['class'=>'recentmodules-header d-flex flex-wrap align-items-center mb-3']);
	
	$content = html_writer::tag('div', $content, ['class'=>'recentmodules-header']);

	$content .= $OUTPUT->recent_courses($courses);

	$content = html_writer::tag('div', $content, ['class'=>'container-fluid']);
	
	echo html_writer::tag('div', $content, ['class'=>'recentcourses-wrap py-5']);
}