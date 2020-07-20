<?php
require_once('../../config.php'); // arg_separator.output breaks the curl, so you need third param for http_buld_query
require_once('classes/timetable_class.php');

list($weekoptions, $defaultweeks, $formatoptions, $defaultformat) = local_cultimetable_api\timetable::get_timetable_config();

$cid = required_param('cid', PARAM_RAW);
$module = required_param('mcode', PARAM_RAW);
$year = optional_param('yr', '', PARAM_RAW);
$weeks = optional_param('weeks', $defaultweeks, PARAM_RAW);
$format = optional_param('format', $defaultformat, PARAM_RAW);

if (!array_key_exists($weeks, $weekoptions)) {
    $weeks = $defaultweeks;
}

if (!array_key_exists($format, $formatoptions)) {
    $format = $defaultformat;
}

$timetable = new local_cultimetable_api\timetable();
// TODO write a parser - html has odd lines in body - mismatched head?
$result = $timetable->display_module_timetable($module, $weeks, $format, $cid);

if ($result['http'] == 200) {
    $doc = new \DOMDocument();
    $doc->loadHTML($result['html']);
    $elem = $doc->getElementsByTagName('head')->item(0);
    $title = $elem->getElementsByTagName('title')->item(0);

    while($elem->childNodes->length){
        $elem->removeChild($elem->firstChild);
    }

    $elem->appendChild($title);
    echo $doc->saveHTML();
} else {
    $timetable = new local_cultimetable_api\timetable();
    // TODO write a parser - html has odd lines in body - mismatched head?
    $modulecodes = $timetable->get_alternative_module_codes($module);
    $alturlstring = '';

    if($modulecodes) {
        $alternativeurls = array();

        foreach ($modulecodes as $modulecode) {
            $url = new \moodle_url(
                '/local/cultimetable_api/timetable.php',
                array(
                    'cid' => $cid,
                    'mcode' => $modulecode,
                    'yr' => $year
                )
                );

            $alternativeurls[] = html_writer::link($url, $modulecode);
        }

        $alturlstring = 'but you could try ' . join(' or ', $alternativeurls);
    }

    $year = explode('-', $year);

    if (count($year) == 2) {
        $from = $year[0];
        $to = $year[1];
    } else {
        $from = '?';
        $to = '?';
    }

    $html = <<<EOT
        <html>
        <head>
        <title>Web timetables - $module - $from - $to</title>
        </head>
        <body>
        <h1>Timetable unavailable</h1>
        <p>Timetables are provided directly from the University's timetabling system website.</p>
        <p>There are several reasons why this timetable may not be available:
        <ul>
        <li>the timetables for year $from - $to have not been setup yet</li>
        <li>the timetables for year $from - $to are now past and can no longer be accessed from Moodle</li>
        <li>there is no timetable for the module code $module {$alturlstring}</li>
        <li>there was a problem fetching the data from http://sws.city.ac.uk (HTTP status code: {$result['http']})</li>
        </ul>
        Please check here: <a href="http://sws.city.ac.uk">http://sws.city.ac.uk</a>
        </p>
        </body>
        </html>
EOT;
    echo $html;
}