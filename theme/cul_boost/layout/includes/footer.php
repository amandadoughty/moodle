<?php
$footnote = (empty($PAGE->theme->settings->footnote)) ? false : $PAGE->theme->settings->footnote;
$copyright = (empty($PAGE->theme->settings->copyright)) ? false : $PAGE->theme->settings->copyright;
$copyright = '&copy; ' . date("Y") . ' ' . $copyright;
$footerlinks = (empty($PAGE->theme->settings->footerlinks)) ? false : $PAGE->theme->settings->footerlinks;
$footerimageurl = $OUTPUT->image_url('footerlogo', 'theme');

$footertemplatecontext = [
    'footnote' => $footnote,
    'copyright' => $copyright,
    'footerlinks' => $footerlinks,
    'footerimageurl' => $footerimageurl,
];