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
    
    public function get_logo() {
        global $OUTPUT, $CFG;
        $userinfo = $this->user_info();
        $imageurl = $OUTPUT->image_url($userinfo->logoprefix . '-logo', 'theme');
        $image = html_writer::empty_tag(
            'img', 
            [
                'src' => $imageurl,
                'class' => 'sitelogo '.$userinfo->logoprefix . 'logo',
                'alt' => get_string('logotext', 'theme_cul_boost')
            ]
        );
        $link = html_writer::link(new moodle_url('/'), $image , array('class' => 'homelink'));
        return $link;
    }

    public function get_white_logo() {
        global $OUTPUT, $CFG;
        $userinfo = $this->user_info();
        $imageurl = $OUTPUT->image_url($userinfo->logoprefix . '-logo-white', 'theme');
        $image = html_writer::empty_tag(
            'img',
            [
                'src' => $imageurl,
                'class' => 'sitelogo '.$userinfo->logoprefix . 'logo',
                'alt' => get_string('logotext', 'theme_cul_boost')
            ]
        );
        $link = html_writer::link(new moodle_url('/'), $image , array('class' => 'homelink'));
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
            $logo->studenthub = "http://www.cass.city.ac.uk/intranet/student";
            $logo->staffhub = "http://www.cass.city.ac.uk/intranet/staff";
            // $logo->library = "http://www.cass.city.ac.uk/intranet/staff/services/learning-resource-centre";
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
        $content .= html_writer::end_tag('div');
        return $content;
    }




};
