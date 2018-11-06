<?php

if ($PAGE->pagelayout == 'course' && $COURSE->id != 1) {

	$sectioninfo = '';

	require_once($CFG->libdir.'/coursecatlib.php');

	$extraclass = 'courseheader w-100 py-4 px-3 mb-3';

	$course = new course_in_list($COURSE);

	$image = '';
	$url = '';
	foreach ($course->get_course_overviewfiles() as $file) {
	    $isimage = $file->is_valid_image();
	    $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
	            '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
	            $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
	}

	if (empty($url)) {
		$url = new moodle_url('/theme/'.$PAGE->theme->name.'/pix/coursebg.jpg');
	}

	$bg = html_writer::tag('div', '', ['class'=>'course-image bg-dark', 'style'=>'background-image: url("'.$url.'")']);

	$course = course_get_format($COURSE)->get_course();

	if (file_exists($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php')) {
	    require_once($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php');
	    if (class_exists('format_'.$course->format.'_renderer')) {
	        // call get_renderer only if renderer is defined in format plugin
	        // otherwise an exception would be thrown
	        $renderer = $PAGE->get_renderer('format_'. $course->format);
	    }
	}
	if (isset($renderer) && $course->format == 'culcourse' && !empty($renderer->dashboard_section())) {
	    $sectioninfo = html_writer::tag('div', $renderer->dashboard_section(), ['class'=>'container-fluid d-flex flex-wrap align-items-stretch position-relative']);
	}

	if ($PAGE->pagelayout == 'course' || $PAGE->pagelayout == 'incourse' && $PAGE->pagetype != 'notes-index') {
	    echo html_writer::tag('div', $OUTPUT->page_heading(), ['class'=>'page-header container-fluid']);
	}

	if ($sectioninfo) {
	    echo html_writer::tag('div', $bg.$sectioninfo, ['class'=>'courseheader position-relative pt-5 mb-4']);
	}

}