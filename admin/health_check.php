<?php
// moodle-apache-check.php
// Amanda Doughty 30/01/2014
// Used by F5s etc. to check that apache is up and able to talk to the Moodle database

$config = '';

// Read config.php and get the database variables
$fp = fopen("/moodle/application-current/config.php", "r");
$start_of_cfg = FALSE;
$end_of_db_vars = FALSE;

while(!$end_of_db_vars) {

    while(!$start_of_cfg) {
        $confdata = fgets($fp);
        if (strpos($confdata, "CFG") == 1) {
            $start_of_cfg = TRUE;
        }
    }

    if(!strpos($confdata, 'wwwroot')) {
        $config .= $confdata;
        $confdata = fgets($fp);
    } else {
        $end_of_db_vars = TRUE;
    }
}

fclose($fp);
eval("$config");

if (isset($CFG->dboptions['dbpersist']) && $CFG->dboptions['dbpersist']) {
    $db = mysqli_connect("p:" .$CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
} else {
    $db = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
}

if(mysqli_connect_error()) {
    // Database is down, so send a "503 Service Unavailable"
    header("HTTP/1.0 503 Service Unavailable");
} else {
    // Connected to database, so run a test query
    $result = $db->query("select 1 from dual");

    if(!$result) {
        // Nothing was returned from the query
        header("HTTP/1.0 503 Service Unavailable");
    } else {
        // No problemo
        header("HTTP/1.0 200 OK");
    }

    $db->close();
}

