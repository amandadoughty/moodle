<?php

$dashboard = '';
$hasdashboard = false;
$url = '';

// CAUTION, hacky fundamental variable defintion to follow!
// Note that because of the way course fromats are constructed though
// inclusion we pass parameters around this way..
$section = optional_param('section', 0, PARAM_INT);

if ($PAGE->pagelayout == 'course' && $COURSE->id != 1) {
    $extraclass = 'courseheader w-100 py-4 px-3 mb-3';    
    $course = new core_course_list_element($COURSE);

    foreach ($course->get_course_overviewfiles() as $file) {
        $isimage = $file->is_valid_image();
        $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
    }

    if (empty($url)) {
    	$url = new moodle_url('/theme/'.$PAGE->theme->name.'/pix/coursebg.jpg');	
    }

    $course = course_get_format($COURSE)->get_course();

    if (file_exists($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php')) {
        require_once($CFG->dirroot.'/course/format/'.$course->format.'/renderer.php');
        if (class_exists('format_'.$course->format.'_renderer')) {
            // call get_renderer only if renderer is defined in format plugin
            // otherwise an exception would be thrown
            $renderer = $PAGE->get_renderer('format_'. $course->format);
        }
    }

    if (isset($renderer) && method_exists($renderer, 'build_dashboard_in_header')) {
        $dashboard = $renderer->build_dashboard_in_header($course, $section);
    }

    if ($dashboard) {
    	$hasdashboard = true;
    }
}

