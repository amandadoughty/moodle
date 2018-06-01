<?php

$slides = 6;
$no = 0;

for ($i=1; $i <= $slides; $i++) {
    $name = 'slide'.$i;

    if (isset($PAGE->theme->settings->{"image_{$name}"}) && !empty($PAGE->theme->settings->{"image_{$name}"})) {
        $no++;
    }
}

if ($no == 0) {
    return;
}

$slideroutput = '';
$slideroutput .= html_writer::start_tag('div', array('class' => 'slidercontainer'));
    
    $slideroutput .= html_writer::start_tag('div', array('class' => 'slider d-flex flex-nowrap align-items-stretch'));
    for ($i=1; $i <= $slides; $i++) {
        $name = 'slide'.$i;

        if (isset($PAGE->theme->settings->{"image_{$name}"}) && !empty($PAGE->theme->settings->{"image_{$name}"})) {
            $slider = new stdClass();
            $slider->image = $PAGE->theme->setting_file_url('image_'.$name, 'image_'.$name);
            if (!empty($PAGE->theme->settings->{"url_{$name}"})) {
                $slider->hasurl = true;
                $slider->url =  $PAGE->theme->settings->{"url_$name"};
            }
            if (!empty($PAGE->theme->settings->{"caption_{$name}"})) {
                $slider->hascaption = true;
                $caption = $PAGE->theme->settings->{"caption_$name"};
                $slider->caption = (strlen($caption) > 200) ? substr($caption, 0, 200) . '&hellip;' : $caption;
            }

            $slideroutput .= $OUTPUT->render_from_template('theme_cul_boost/slide', $slider);
        }
    }
    
    $controls = html_writer::tag('div', '', ['class'=>'container-fluid']);

    $slideroutput .= html_writer::end_tag('div');

    $slideroutput .= html_writer::tag('div', $controls, ['class'=>'slide-controls']);

$slideroutput .= html_writer::end_tag('div');

echo $slideroutput;