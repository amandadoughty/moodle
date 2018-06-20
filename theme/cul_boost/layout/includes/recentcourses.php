<?php 

$courses = enrol_get_my_courses();

$rc = '';
if (!empty($OUTPUT->recent_courses($courses))) {

	$rc = $OUTPUT->recent_courses($courses);

	if ($hasdashfeed) {
	    $rc .= $OUTPUT->synergyblocks($dashfeed, 'col-12 col-lg-4');
	}

	$rc = html_writer::tag('div', $rc, ['class'=>'row']);

	$content = html_writer::tag('div', $rc, ['class'=>'container-fluid']);
	
	echo html_writer::tag('div', $content, ['class'=>'recentcourses-wrap py-5']);
}