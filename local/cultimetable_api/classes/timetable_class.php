<?php
namespace local_cultimetable_api;

defined('MOODLE_INTERNAL') || die();

class timetable {
    private $doc;
    private $viewstate = '';
    private $eventvalidation = '';
    private $viewstaregenerator = '' ;
    private $connect_timeout;
    private $imeout;
    private $login_url;
    private $default_url;
    private $timetable_url;
    private $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
    private $cookie_file_path;
    private $weekoptions;
    private $formatoptions;
    private $httpcode;
    private $errorcode;

    public function __construct() {
        $this->connect_timeout = get_config('local_cultimetable_api', 'connect_timeout');
        $this->timeout = get_config('local_cultimetable_api', 'timeout');
        $this->login_url = trim(get_config('local_cultimetable_api', 'login_url'));
        $this->default_url = trim(get_config('local_cultimetable_api', 'default_url'));
        $this->timetable_url = trim(get_config('local_cultimetable_api', 'timetable_url'));
        list($weekoptions, $defaultweeks, $formatoptions, $defaultformat) = self::get_timetable_config();            
        $this->weekoptions = $weekoptions;
        $this->formatoptions = $formatoptions;
        $this->cookie_file_path = '/var/tmp/cook' . uniqid();
        $this->doc  = new \DOMDocument();        
    }

    public function display_module_timetable($module, $weeks, $format, $cid) {
        $html = '';

        if($this->timetable_login()) {    
            if($this->timetable_module_form()) {
                $html .= $this->get_module_timetable($module, $weeks, $format, $cid);
                $html .= $this->get_timetable_form($weeks, $format, $cid, $module);
            }
        }
        
        return array(
            'html' => $html, 
            'http' => $this->httpcode,
            'error' => $this->errorcode
            );
    }   

    private function timetable_login() {
        #Get login page and set hidden values.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->login_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file_path);
        $result = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);
        #die(print_r($result));
        libxml_use_internal_errors(true);

        if($this->httpcode <> 200) {
            return false;
        }

        if($result) {
            $this->doc->loadHTML($result);

            if($element = $this->doc->getElementById('__VIEWSTATE')) {
                $this->viewstate = $element->getAttribute('value');
            }

            if($element = $this->doc->getElementById('__EVENTVALIDATION')) {
                $this->eventvalidation = $element->getAttribute('value');
            }

            return true;
        }
        #print $this->eventvalidation;
        #print $this->viewstate;
    }

    private function timetable_module_form() {
        # Get module page and hidden values..

        $fields_string = http_build_query(
            array(
                '__VIEWSTATE' => $this->viewstate,
                '__EVENTVALIDATION' => $this->eventvalidation,
                '__EVENTTARGET' => 'LinkBtn_modules'
            ),
            null, 
            '&'
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->default_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        if($this->httpcode <> 200 || !$result) {
            return false;
        }

        // if($result) {
            $this->doc->loadHTML($result);
            // echo $result;
            // echo $this->error;
            // echo $this->httpcode;
            $this->viewstate = $this->doc->getElementById('__VIEWSTATE')->getAttribute('value');
            $this->eventvalidation = $this->doc->getElementById('__EVENTVALIDATION')->getAttribute('value');
            $this->viewstaregenerator = $this->doc->getElementById('__VIEWSTATEGENERATOR')->getAttribute('value');
        // }

        return true;
    }

    private function get_module_timetable($module, $weeks, $format, $cid) {
        $fields_string =  http_build_query(
            array(
                '__EVENTTARGET' => NULL ,
                '__EVENTARGUMENT' =>  NULL,
                '__LASTFOCUS' =>  NULL,
                '__VIEWSTATE' => $this->viewstate,
                '__EVENTVALIDATION' => $this->eventvalidation,
                '__VIEWSTATEGENERATOR' => $this->viewstaregenerator,
                'tLinkType' => 'modules',
                'dlFilter' => '%',
                'tWildcard' => NULL,
                'dlObject' => $module,
                'lbWeeks' => $this->weekoptions[$weeks]['value'],
                'lbDays' => '1-5',
                'dlPeriod' => '1-26',
                'RadioType' => $this->formatoptions[$format]['value'],
                'bGetTimetable' => 'View Timetable',
                'button' => 'bGetTimetable'
            ),
            null, 
            '&'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->default_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // print_r(curl_getinfo($ch));
        // print_r($result);
        curl_close ($ch);

        if($this->httpcode == 500) {
            return $result;
        }

        // if($this->httpcode <> 200) {
        //     return false;
        // }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->timetable_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file_path);
        $result = $this->parse_timetable_output($ch, $module, $format, $cid);
        #print_r($this->tt_data);
        // $result = curl_exec($ch);
        // $this->error = curl_error($ch);
        // $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        // curl_close ($ch);
        #print_r($result);
        return $result;
    }

    private function parse_timetable_output($ch, $module, $format, $cid) {
        global $DB;

        $course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
        $result = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);

        if($this->httpcode <> 200) {
            return false;
        }

        if($result) {
            $doc = new \DOMDocument();
            $doc->loadHTML($result);

            if ($this->formatoptions[$format]['display'] == 'Grid') {
                // Remove the JS operated links.
                $lastweeklink = $doc->getElementById('bLastWeek');
                $font = $lastweeklink->parentNode;
                $td = $font->parentNode;
                $tr = $td->parentNode;
                $table = $tr->parentNode;
                $a = $table->parentNode->removeChild($table);
                // Add the course fullname to the title.
                $finder = new \DomXPath($doc);
                $classname = 'header-0-0-1';
                $nodes = $finder->query("//*[contains(@class, '$classname')]");
                $node = $nodes->item(0);

                if($node) {
                    $newtitle = new \DOMText($course->fullname);
                    $node->removeChild($node->firstChild);
                    $node->appendChild($newtitle);
                }

                $result = $doc->saveHTML();
            } else if ($this->formatoptions[$format]['display'] == 'Event') {
                // Add the course fullname to the title.
                $finder = new \DomXPath($doc);
                $classname = 'header-0-0-0';
                $nodes = $finder->query("//*[contains(@class, '$classname')]");
                $node = $nodes->item(0);

                if($node) {                
                    $newtitle = new \DOMText('Timetable for ' . $course->fullname);
                    $node->removeChild($node->firstChild);
                    $node->appendChild($newtitle);
                }

                $result = $doc->saveHTML();
            } else if ($this->formatoptions[$format]['display'] == 'Module List') {
                // Standardise heading to use 'Timetable for'.
                $finder = new \DomXPath($doc);
                $classname = 'header-0-0-0';
                $nodes = $finder->query("//*[contains(@class, '$classname')]");
                $node = $nodes->item(0);
                
                if($node) {                
                    $newtitle = new \DOMText('Timetable for ');
                    $node->removeChild($node->firstChild);
                    $node->appendChild($newtitle);
                }

                // Add the course fullname to the title.
                $classname = 'header-0-0-1';
                $nodes = $finder->query("//*[contains(@class, '$classname')]");
                $node = $nodes->item(0);
                
                if($node) {                
                    $newtitle = new \DOMText($course->fullname);
                    $node->removeChild($node->firstChild);
                    $node->appendChild($newtitle);
                }

                $result = $doc->saveHTML();
            }
        }

        return $result;
    }

    private function get_timetable_form($weeks, $format, $cid, $module) {
        $selectstring = $this->construct_select($weeks);
        $radiostring = $this->construct_radio($format);

        $html = <<<EOT
        <p>You can select different date ranges here:</p>
        <form action="" method="get">
        <input type="hidden" name="cid" value="$cid"> 
        <input type="hidden" name="mcode" value="$module"> 
        $selectstring 
        <br />
        Format:  
        $radiostring 
        <br />
        <input type="submit" value="show timetable">
        </form>
        <p>
        Please visit <a href="http://sws.city.ac.uk">http://sws.city.ac.uk</a> 
        to check timetable for different period, search for programme and cohort data, 
        and view different output formats.
        </p>
EOT;
        return $html;
    }

    private function construct_select($weeks) {
        $selectstring = '';
        $optionsstring = '';      

        foreach($this->weekoptions as $key => $option) { 
            if ($weeks == $key) {
                $selected = ' selected';
            } else {
                $selected = '';
            } if($this->httpcode <> 200) {
                return false;
            }

            $optionsstring .= '<option value="' . $key . '"' . $selected . '>'. $option['display'] . '</option>';
        }    

        $selectstring .= '<select name="weeks">' . $optionsstring . '</select>';
        return($selectstring );
    }

    private function construct_radio($format) {
        $radiostring = '';

        foreach($this->formatoptions as $key => $option) {
            if ($format == $key) {
                $checked = ' checked';
            } else {
                $checked = '';
            }

            $radiostring .= '<input type="radio" name="format" value="' . $key . '"' . $checked . '>'. $option['display'];
        }

        return($radiostring );
    }

    public static function get_timetable_config() {
        $weekoptionstring = trim(get_config('local_cultimetable_api', 'timetable_weekoptions'));
        $formatoptionstring = trim(get_config('local_cultimetable_api', 'timetable_formatoptions'));
        
        if (!$weekoptionstring) {
            $weekoptionstring = <<<EOT
            default (not shown on page)|1516_WEEK
            This Week|t|1516_WEEK
            Next Week|n|1516_NEXTWEEK
            Autumn Term (standard): ranging w/c 28 Sep - w/c 7 Dec 2015|5;6;7;8;9;10;11;12;13;14;15|1516_PRD1
            Autumn Term (non-standard): ranging w/c 31 Aug - w/c 14 Dec 2015|1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16|1516_PRD1A
            Spring Term (standard): ranging w/c 25 Jan - w/c 4 Apr 2016 (+ w/c 11 Apr)|22;23;24;25;26;27;28;29;30;31;32;33|1516_PRD2
            Spring Term (non-standard): ranging w/c 4 Jan - w/c 25 Apr 2016|19;20;21;22;23;24;25;26;27;28;29;30;31;32;33;34;35|1516_PRD2A
            Summer Term (standard): ranging w/c 9 May - w/c 18 Jul 2016|36;37;38;39;40;41;42;43;44;45;46;47|1516_PRD3
            Summer Term (non-standard): ranging w/c 2 May - w/c 22 Aug 2016|36;37;38;39;40;41;42;43;44;45;46;47;48;49;50;51;52|1516_PRD3A
            MSc Cass Autumn Term: w/c 28 Sep - w/c 7 Dec 2015|5;6;7;8;9;10;11;12;13;14;15|1516_PRD1_CASSMSC
            MSc Cass Spring Term: w/c 25 Jan - w/c 28 Mar 2016|22;23;24;25;26;27;28;29;30;31|1516_PRD2_CASSMSC
            MSc Cass Summer Term: w/c 16 May - w/c 27 June 2016|38;39;40;41;42;43;44|1516_PRD3_CASSMSC
            Entire Year: ranging w/c 31 Aug 2015 - w/c 22 Aug 2016|1;2;3;4;5;6;7;8;9;10;11;12;13;14;15;16;17;18;19;20;21;22;23;24;25;26;27;28;29;30;31;32;33;34;35;36;37;38;39;40;41;42;43;44;45;46;47;48;49;50;5;|1516_PRD
EOT;
        }

        if (!$formatoptionstring) {
            $formatoptionstring = <<<EOT
            default (not shown on page)|event
            Module List|TextSpreadsheet;swsurl;TextSpreadsheet Object|list
            Grid|Individual;swsurl;Individual Object|grid
            Event|TextSpreadsheet;swsurl;TextSpreadsheet Object SoNM|event
EOT;
        }

        // Build associative arrays from string options
        $weekoptions = array();
        $weekoptionstrings = explode(PHP_EOL, $weekoptionstring);
        $defaultweekstring = array_shift($weekoptionstrings);
        $defaultweekparts = explode('|', trim($defaultweekstring));
        $defaultweeks = $defaultweekparts[1];

        foreach ($weekoptionstrings as $weekoptionstring) {
            $parts = explode('|', trim($weekoptionstring));
            $weekoptions[$parts[2]] = array('display' => $parts[0], 'value' => $parts[1]);
        }

        $formatoptions = array();
        $formatoptionstrings = explode(PHP_EOL, $formatoptionstring);
        $defaultformatstring = array_shift($formatoptionstrings);
        $defaultformatparts = explode('|', trim($defaultformatstring));
        $defaultformat = $defaultformatparts[1];

        foreach ($formatoptionstrings as $formatoptionstring) {
            $parts = explode('|', trim($formatoptionstring));
            $formatoptions[$parts[2]] = array('display' => $parts[0], 'value' => $parts[1]);
        }

        return array (
            $weekoptions,
            $defaultweeks,
            $formatoptions,
            $defaultformat
            );    
    }

    public function get_alternative_module_codes($module) {
        $fields_string = http_build_query(
            array(
                '__EVENTTARGET' => NULL ,
                '__EVENTARGUMENT' =>  NULL,
                '__LASTFOCUS' =>  NULL,
                '__VIEWSTATE' => $this->viewstate,
                '__EVENTVALIDATION' => $this->eventvalidation,
                '__VIEWSTATEGENERATOR' => $this->viewstaregenerator,
                'tLinkType' => 'modules',
                'dlFilter' => '%',
                'tWildcard' => $module,
                'lbWeeks' => 't',
                'lbDays' => '1-5',
                'dlPeriod' => '1-26',
                'RadioType' => $this->formatoptions['list']['value'],
                'bWildcard' => 'search',
                'button' => 'bWildcard'
            ),
            null, 
            '&'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connect_timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->timeout);
        curl_setopt($ch, CURLOPT_URL, $this->default_url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file_path);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        $result = curl_exec($ch);
        $this->error = curl_error($ch);
        $this->httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        #print_r(curl_getinfo($ch));
        #print_r($result);
        curl_close ($ch);

        if($this->httpcode <> 200) {
            return false;
        }

        $modulecodes = array();

        if($result){
            $doc = new \DOMDocument();
            $doc->loadHTML($result);

            if($select = $doc->getElementById('dlObject')) {
                $modulelist = $select->getElementsByTagName('option');            

                foreach($modulelist as $module) {
                    $modulecodes[] = $module->getAttribute('value');
                }
            }
        }
        
        return $modulecodes;
    }
}











