<?php
// This file is part of The City University Moodle theme
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * City University specific renderers
 *
 * @package   City University Moodle theme
 * @copyright 2014 City University London
 * @author    Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_cul_boost_city_menu_renderer extends plugin_renderer_base {
    
    public function get_coursename($link = true) {
        global $COURSE, $PAGE;
        if ($COURSE->id > 1) {
            $name = $COURSE->fullname;

            if ($link) {
                $url = new moodle_url('/course/view.php', array('id' => $COURSE->id));
            } else {
                $url = '#';
            }
            return html_writer::link($url,
                $name, array('class' => 'courselink'));
        } else {
            if ($PAGE->bodyid == 'page-login-index') {
                return '';
            }
            if ($link) {
                $url = new moodle_url('/');
            } else {
                $url = '#';
            }
            return html_writer::link($url,
                get_string('mymoodle', 'my'), array('class' => 'courselink'));
        }
    }

    public function get_logo() {
        global $OUTPUT, $CFG;
        $userinfo = $this->user_info();
        $imageurl = $OUTPUT->image_url($userinfo->logoprefix . '-logo', 'theme');
        $image = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'sitelogo '.$userinfo->logoprefix.'logo'));
        $link = html_writer::link(new moodle_url('/'), $image , array('class' => 'homelink'));
        return $link;
    }

    public function get_white_logo() {
        global $OUTPUT, $CFG;
        $userinfo = $this->user_info();
        $imageurl = $OUTPUT->image_url($userinfo->logoprefix . '-logo-white', 'theme');
        $image = html_writer::empty_tag('img', array('src' => $imageurl, 'class' => 'sitelogo '.$userinfo->logoprefix.'logo'));
        $link = html_writer::link(new moodle_url('/'), $image , array('class' => 'homelink py-3'));
        return $link;
    }

    private function user_info() {
        global $USER;

        $logo = new stdClass();
        if (isset($USER->institution)) {
            $userschool = trim($USER->institution);
        } else {
            $userschool = '';
        }

        if (isset($USER->department)) {
            $userdept = trim($USER->department);
        } else {
            $userdept = '';
        }

        // Get school code for logo.
        // Default settings.
        $logo->logoprefix = "city";
        $logo->gaschool = "UUCITY";
        $logo->title = "City Unversity London homepage";
        $logo->website = "city.ac.uk";
        $logo->studenthub = "https://studenthub.city.ac.uk/";
        $logo->staffhub = "https://staffhub.city.ac.uk/";
        $logo->library = "https://www.city.ac.uk/library";

        // City Uni Central Services.
        if ((trim($userschool) == 'UUCITY') && (substr(trim($userdept), 0, 1) == 'U')) {
            $logo->logoprefix = 'city';
            $logo->gaschool = 'UUCITY';
        }
        // Law School.
        if (trim($userschool) == 'LLILAW')  {
            $logo->gaschool = 'LLILAW';
        }
        // Cass Business School.
        if (trim($userschool) == 'BBCASS') {
            $logo->logoprefix = 'cass';
            $logo->gaschool = 'BBCASS';
            $logo->title = "Cass Business School homepage";
            // CMDLTWO-362 Cass global nav.
            $logo->website = "cass.city.ac.uk";
            $logo->studenthub = "http://www.cass.city.ac.uk/intranet/staff";
            $logo->staffhub = "http://www.cass.city.ac.uk/intranet/student";
            $logo->library = "http://www.cass.city.ac.uk/intranet/staff/services/learning-resource-centre";
        }
        // School of Arts and Social Sciences
        if ((trim($userschool) == 'AASOAR') OR (trim($userschool) == 'ASSASS') OR (trim($userschool) == 'SSSOSS') OR (trim($userschool) == 'ASSOCL')) {
            $logo->gaschool = 'ASSASS';
        }
        // School of Engineering and Maths and Informatics
        if ((trim($userschool) == 'EESEMS') OR (trim($userschool) == 'EEMCSE') OR (trim($userschool) == 'IISOIN') OR (substr(trim($userschool), 0, 2) == 'EE')) {
            $logo->gaschool = 'EEMCSE';
        }
        // School of Health Sciences (leave as schs for Google Analytics).
        if ((trim($userschool) == 'HASAHS') OR (trim($userschool) == 'HNSONM') OR (trim($userschool) == 'HHSOHS') OR (trim($userschool) == 'HSSOHS')) {
            $logo->gaschool = 'HSSOHS';
        }
        return $logo;
    }
    
    /**
     * Prints the top menu City University Global navigation.
     */
    public function city_global_navigation() { // CMDLTWO-362 Cass global nav.
        global $OUTPUT, $CFG;

        $logo = $this->user_info();

        $content = html_writer::start_tag('div' , array('id' => 'cross-domain'));
        $hideoncollapse = array();
        $attributes = $hideoncollapse + array('target' => '_blank');
        // CMDLTWO-362 Cass global nav - get links passed to function from logo.php in renderers/city.php.
        $listitems = array (
            html_writer::link('https://www.' . $logo->website . '/', $logo->website),
            html_writer::link('https://outlook.office.com/', 'Email', $attributes),
            html_writer::link(new moodle_url('/index.php'), 'Moodle',
                array('class' => 'active', 'style' => 'pointer-events: none')), // CUL CMDLTWO-212.
            html_writer::link($logo->library, 'Library', $hideoncollapse),
            html_writer::link($logo->studenthub, 'Student Hub', $hideoncollapse),
            html_writer::link($logo->staffhub, 'Staff Hub', $hideoncollapse),
        );
        $content .= html_writer::alist( $listitems, array('id' => 'cross-domain-nav', 'class'=>'d-flex flex-wrap'), 'ul');
        

        // $content .= $OUTPUT->login_info(true);

        // // $loggedinas = $OUTPUT->login_info(true);

        // $content .= '<div class="logininfo loginwarn"><i class="fa fa-exclamation-triangle"></i></div>';

        // $content .= str_replace('<div class="logininfo">', '<div class="loginwarn"><i class="fa fa-exclamation-triangle"></i></div><div class="logininfo">', $loggedinas);


        $content .= html_writer::end_tag('div');
        return $content;
    }

    /*
     * Prints the footer menu navigation
     */
    public function city_footer_navigation() {

        $baseurl = 'http://www.city.ac.uk';
        $listitems = array (
            html_writer::link($baseurl . '/contact', 'Contact us'),
            html_writer::link($baseurl . '/visit', 'Maps'),
            html_writer::link($baseurl . '/about/working-at-city', 'Jobs'),
            html_writer::link($baseurl . '/about/city-information/legal', 'Legal'),
            html_writer::link($baseurl . '/about/city-information', 'City Information'),
            html_writer::link($baseurl . '/about/city-information/governance/charity-information', 'Charity Information'),
            html_writer::link($baseurl . '/media', 'Press'),
            html_writer::link($baseurl . '/accessibility', 'Accessibility'),
            html_writer::link($baseurl . '/about/city-information/legal/cookies', 'Cookies'),
            html_writer::link('http://estore.city.ac.uk/', 'Store'),
        );
        $content  = html_writer::alist($listitems, array('id' => 'footer-links'), 'ul');
        $content .= html_writer::start_tag('div', array('class' => 'footerbottom'));
        $content .= html_writer::start_tag('div', array('class' => 'vcard'));
        $content .= html_writer::link($baseurl, 'City, University of London');
        $content .= html_writer::tag('span', ' London', array('class' => 'locality'));
        $content .= html_writer::tag('span', ' EC1V 0HB', array('class' => 'postal-code'));
        $content .= html_writer::tag('span', ' United Kingdom', array('class' => 'country-name'));
        $content .= html_writer::tag('span', ' +44 (0)20 7040 5060', array('class' => 'phone-number'));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::tag('div', '', array('class' => 'clearer'));
        $content .= html_writer::end_tag('div');
        return $content;
    }

    // CMDLTWO-349 Crazy Egg.
    public function tracking_script() {
        $tscript = '';
        global $PAGE, $COURSE;
        if (!empty($PAGE->theme->settings->tracking) && $PAGE->theme->settings->tracking == '1') {
            $tscript = '
            <script type="text/javascript">
            setTimeout(function(){var a=document.createElement("script");
            var b=document.getElementsByTagName("script")[0];
            a.src=document.location.protocol+
            "//dnn506yrbagrg.cloudfront.net/pages/scripts/0011/9622.js?"+Math.floor(new Date().getTime()/3600000);
            a.async=true;a.type="text/javascript";b.parentNode.insertBefore(a,b)}, 1);
            </script>';
        }
        return $tscript;
    }

    // Google Analytics code.
    public function google_analytics() {
        global $DB , $USER, $COURSE, $PAGE;

        $userinfo = $this->user_info();

        $trackurl = $userinfo->gaschool;
        if ($COURSE->id != 1 ) {
            // Add course category idnumber.
            if ($category = $DB->get_record('course_categories', array('id' => $COURSE->category))) {
                $trackurl .= '/' . urlencode($category->idnumber);
            }

            // Add course name.
            $trackurl .= '/' . urlencode($COURSE->shortname);

            // Get role in course.
            $userroles = get_user_roles_in_course($USER->id, $COURSE->id);
            if ($userroles == '') {
                $userroles = 'norole';
            }
            $trackurl .= '/' . strip_tags($userroles);
        }

        // Get page type.
        $trackurl .= '/' . urlencode($PAGE->pagetype);

        // Get page action and id ... bit after ? in URL but only if it has any.

        if (strpos($PAGE->url, '?') > 0) {
            $args = substr( ($PAGE->url), strrpos(($PAGE->url), '?' ) + 1 );
            $trackurl .= '/' . (str_replace('&amp;', '+', $args));
        }

        $script = '
        <script type="text/javascript">
        var _gaq = _gaq || [];
        _gaq.push([\'_setAccount\', \''.$PAGE->theme->settings->gakey.'\']);
        _gaq.push([\'_trackPageview\',\''. $trackurl .'\']);

        (function() {
        var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
        ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
        var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
        })();
        </script>';
        return $script;
    }
};
