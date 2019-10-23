<?php
// This file is part of Moodle - http://moodle.org/
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

defined('MOODLE_INTERNAL') || die;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_cul_boost_city_core_renderer extends \theme_boost\output\core_renderer {


    /**
     * City University main menu
     */
    public function custom_menu($custommenuitems = '') {
        global $CFG, $PAGE, $USER, $OUTPUT;

        $content = '';
        $showmuenu = isloggedin() && !isguestuser();

        if (!empty($PAGE->layout_options['nonavbar'])) {
            return '';
        }
        // My Moodle
        if ($showmuenu) {
            $mymenu = $this->my_menu();
            $content = $this->render_custom_menu($mymenu);
        }
        // Favourites
        if ($showmuenu) {
            if ($favouritesmenu = $this->favourites_menu()) {
                $content .= $this->render_custom_menu($favouritesmenu);
            }
        }
        // My Modules
        if ($showmuenu) {
            if ($mycoursesmenu = $this->my_modules_menu()) {
                $content .= $this->render_custom_menu($mycoursesmenu);
            }
        }
        // Module menu
        if ($showmuenu) {
            if ($coursemenu = $this->module_menu()) {
                $content .= $this->render_custom_menu($coursemenu);
            }
        }
        // Custom menu from Theme Settings
        if (!empty($CFG->custommenuitems)) {
            $custommenuitems .= $CFG->custommenuitems;
        }

        $custommenu = new custom_menu($custommenuitems, current_language());
        $content .= $this->render_custom_menu($custommenu);

        return $content;
    }

    /**
     * City University my menu
     */
    public function my_menu() {
        global $CFG, $PAGE;

        $mymoodletxt = get_string('mymoodle', 'my');
        $mymenuitems = "$mymoodletxt|/my/|$mymoodletxt";
        $mymenu = new custom_menu($mymenuitems, current_language());
        return $mymenu;
    }

    /**
     * City University favourites menu
     */
    public function favourites_menu() {
        // CMDLTWO-314: Add 'Favourites' menu - courses which the User has flagged as favourites.
        $favouritescourses = theme_cul_boost_utility::get_user_favourites_courses();

        if (count($favouritescourses)) {
            $favouritestxt = get_string('myfavourites', 'theme_cul_boost');
            $favouritesmenuitems = "$favouritestxt|''|$favouritestxt";
            $favouritesmenu = new custom_menu('', current_language());
            $favourites = $favouritesmenu->add($favouritestxt, new moodle_url(''), $favouritestxt);

            foreach ($favouritescourses as $favouritescourse) {
                $coursecontext = context_course::instance($favouritescourse->id);

                if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                    if (!$favouritescourse->visible) {
                        continue;
                    }
                }

                $favouritename = $favouritescourse->displayname;
                $favourites->add($favouritename,
                    new moodle_url('/course/view.php', array('id' => $favouritescourse->id)),
                    $favouritescourse->displayname);
            }
            return $favouritesmenu;
        }
        return false;
    }

    public function my_modules_menu() {
        global $CFG;
        
        // Add 'My modules' menu - a list of courses in which the user is enrolled.
        $years = 0;

        if (isset($this->page->theme->settings->years)) {
            $years = $this->page->theme->settings->years;
        }

        $enrolledcourses = theme_cul_boost_utility::get_user_enrolled_courses($years);

        if ($enrolledcourses) {
            $maxdropdowncourses = $CFG->frontpagecourselimit;
            $countcourses = 0;
            $mycoursestxt = get_string('mycourses', 'theme_cul_boost');
            $mycoursesmenu = new custom_menu('', current_language());
            $mycourses = $mycoursesmenu->add($mycoursestxt, new moodle_url(''), $mycoursestxt, 12);

            if ($enrolledcourses) {
                foreach($enrolledcourses as $year => $prds) {
                    if ($year == "other") {
                        continue;
                    }
                    if ($countcourses > $maxdropdowncourses ) {
                        $mycourses->add(get_string('morecourses', 'theme_cul_boost'), new moodle_url('/my'), null);
                        break;
                    }
                    if (count($prds) > 0) {
                        $yearmenu = $mycourses->add($year, null, $year);
                    }

                    foreach ($prds as $prd => $enrolledcourse) {
                        if ($prd != "other" && count($enrolledcourse) > 0) {
                            $prdtext = get_string($prd, 'theme_cul_boost');
                            $periodmenu = $yearmenu->add($prdtext, null, $prd . '_' . $year);
                        }

                        foreach ($enrolledcourse as $mycourse) {
                            $coursename = $mycourse->displayname;

                            if ($prd == "other") {
                                continue;
                            } else {
                                $periodmenu->add($coursename,
                                    new moodle_url('/course/view.php', ['id' => $mycourse->id]),
                                    $mycourse->displayname);
                            }

                            $countcourses++;
                        }
                    }

                    if (isset($prds['other'])) {
                        foreach ($prds['other'] as $mycourse) {
                            $coursename = $mycourse->displayname;
                            $yearmenu->add($coursename,
                                        new moodle_url('/course/view.php', ['id' => $mycourse->id]),
                                        $mycourse->displayname);
                        }
                    }
                }

                foreach ($enrolledcourses['other'] as $othercourse) {
                    $coursename = $othercourse->displayname;

                    if ( $countcourses < $maxdropdowncourses ) {
                        $mycourses->add($coursename,
                            new moodle_url('/course/view.php', array('id' => $othercourse->id)),
                            $othercourse->displayname);
                    }

                    $countcourses++;
                }
            }

            return $mycoursesmenu;
        }
        
        return false;
    }

    /**
     * City University module menu
     */
    public function module_menu() {
        global $CFG, $PAGE, $COURSE;

        // Create the 'Module menu'.
        if ($COURSE->id > 1) {
            require_once($CFG->dirroot . '/course/lib.php');
            
            $this->page->navigation->initialise();
            $currentcoursenav = clone $PAGE->navigation->find($COURSE->id, navigation_node::TYPE_COURSE);
            $coursecontext = context_course::instance($COURSE->id);

            if(has_capability('gradereport/grader:view', $coursecontext)) {
                $gradesurl = '/grade/report/grader/index.php';
            } else {
                $gradesurl = '/grade/report/culuser/index.php';
            }

            // Remove the Grades node and add Grades link to our custom report.
            // Make it appear at the top of the list.
            foreach ($currentcoursenav->children as $key => $node) {
                if ($node->key == 'grades') {
                    // $gradesnode = clone $node;
                    $node->remove();               
                    $gradesurl = new moodle_url($gradesurl, array(
                        'id' => $COURSE->id,
                        'sesskey' => sesskey()
                    ));

                    $itemkeylist = $currentcoursenav->children->get_key_list();
                    $firstitemkey = strtolower($itemkeylist[0]);
                    $gradesnode = new navigation_node(
                        [
                            'text' => get_string('grades'),
                            'type' => navigation_node::TYPE_CUSTOM,
                            'action' => $gradesurl]
                        );

                    $currentcoursenav->add_node($gradesnode, $firstitemkey);

                    continue;
                }
            }

            $coursemenutree = $this->topmenu_tree($currentcoursenav, get_string('coursemenu', 'theme_cul_boost'));
            $coursemenu = new custom_menu($coursemenutree, current_language());
            return $coursemenu;
        }

        return false;
    }

    /**
     * City University settings menu
     */
    public function settings_menu($settingsnav) {
        global $CFG, $PAGE;
        // Create the 'Settings' menu.
        $settingstxt = get_string('settings', 'theme_cul_boost');
        $settingstree = $this->topmenu_tree($settingsnav, $settingstxt);
        $settingsmenu = new custom_menu($settingstree, current_language());
        return $settingsmenu;
    }


    /**
     * City University profile menu
     */
    public function profile_menu($myprofilenav) {
        global $CFG, $PAGE;

        // Create the 'My profile' menu.
         $profiletxt = get_string('myprofile', 'theme_cul_boost');
            $profilemenuitems = "$profiletxt|''|$profiletxt";
            $myprofilemenu = new custom_menu('', current_language());
            $myprofile = $myprofilemenu->add($profiletxt, new moodle_url(''), $profiletxt);

            foreach ($myprofilenav as $profileitem) {

                $profilename = $profileitem->title;
                $myprofile->add($profilename, $profileitem->url, $profilename);
            }
            return $myprofilemenu;
    }



    /**
     * Serves requests to /theme/cul_boost/favourites_ajax.php
     *
     *
     * @return array
     */
    public function favourites_ajax() {
        global $DB, $CFG;

        $content = '';
        $favouritescourses = theme_cul_boost_utility::get_user_favourites_courses();
        $menu = new custom_menu('', current_language());

        if (count($favouritescourses)) {
            $favouritestxt = get_string('myfavourites', 'theme_cul_boost');
            $favourites = $menu->add($favouritestxt, new moodle_url(''), $favouritestxt, 11);

            foreach ($favouritescourses as $favouritescourse) {
                $coursecontext = context_course::instance($favouritescourse->id);

                if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                    if (!$favouritescourse->visible) {
                        continue;
                    }
                }

                $favouritename = $favouritescourse->displayname;
                $favourites->add($favouritename,
                    new moodle_url('/course/view.php', array('id' => $favouritescourse->id)),
                    $favouritescourse->displayname);
            }
        }

        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        return $content;
    }

    // /**
    //  * Overridden renderer - Returns a search box.
    //  *
    //  * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
    //  * @return string         HTML with the search form hidden by default.
    //  */
    // public function search_box($id = false) {
    //     global $CFG;

    //     // Accessing $CFG directly as using \core_search::is_global_search_enabled would
    //     // result in an extra included file for each site, even the ones where global search
    //     // is disabled.
    //     if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
    //         return '';
    //     }

    //     if ($id == false) {
    //         $id = uniqid();
    //     } else {
    //         // Needs to be cleaned, we use it for the input id.
    //         $id = clean_param($id, PARAM_ALPHANUMEXT);
    //     }

    //     // JS to animate the form.
    //     $this->page->requires->js_call_amd('core/search-input', 'init', array($id));

    //     $searchicon = html_writer::tag('div', '<i class="fa fa-search">&nbsp;</i>',
    //         array('role' => 'button', 'tabindex' => 0));
    //     $formattrs = array('class' => 'search-input-form', 'action' => $CFG->wwwroot . '/search/index.php');
    //     $inputattrs = array('type' => 'text', 'name' => 'q', 'placeholder' => get_string('search', 'search'),
    //         'size' => 13, 'tabindex' => -1, 'id' => 'id_q_' . $id);

    //     $contents = html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
    //         array('for' => 'id_q_' . $id, 'class' => 'accesshide')) . html_writer::tag('input', '', $inputattrs);
    //     $searchinput = html_writer::tag('form', $contents, $formattrs);

    //     return html_writer::tag('div', $searchicon . $searchinput, array('class' => 'search-input-wrapper nav-link', 'id' => $id));
    // }    

    // /**
    //  * Overwritten renderer - Output all the blocks in a particular region.
    //  *
    //  * @param string $region the name of a region on this page.
    //  * @return string the HTML to be output.
    //  */
    // public function blocks_for_region($region) {
    //     global $COURSE;

    //     $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
    //     $blocks = $this->page->blocks->get_blocks_for_region($region);
    //     $lastblock = null;
    //     $zones = array();
    //     $can_edit = false;
    //     $admininstanceid = -1;

    //     if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
    //         $can_edit = true;
    //     }

    //     foreach ($blocks as $block) {
    //         if (!$can_edit && $block->name() == 'settings') {
    //             $admininstanceid = $block->instance->id;
    //             continue;
    //         }

    //         $zones[] = $block->title;
    //     }

    //     $output = '';

    //     foreach ($blockcontents as $bc) {
    //         if ($bc->blockinstanceid == $admininstanceid) {
    //             continue;
    //         }

    //         if ($bc instanceof block_contents) {
    //             $output .= $this->block($bc, $region);
    //             $lastblock = $bc->title;
    //         } else if ($bc instanceof block_move_target) {
    //             $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
    //         } else {
    //             throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
    //         }
    //     }
    //     return $output;
    // }

    // /**
    //  * theme_cul_boost_topmenu_renderer::topmenu_tree()
    //  *
    //  * @param navigation_node $navigation
    //  * @param mixed $title
    //  * @return string Menu tree.
    //  */
    // public function topmenu_tree(navigation_node $navigation, $title) {
    //     if (!$navigation->has_children() || $navigation->children->count() == 0) {
    //         return '';
    //     }

    //     $content  = "$title|\n";
    //     $content .= $this->topmenu_node($navigation);
    //     return $content;
    // }

    // /**
    //  * theme_cul_boost_topmenu_renderer::topmenu_node()
    //  *
    //  * @param mixed $node
    //  * @param integer $navcounter
    //  * @return
    //  */
    // protected function topmenu_node(navigation_node $node, $navcounter = 0) {
    //     $prefix = '';
    //     $maxlen = 28; // Maximum length of menu item text before it will be shortened for display.

    //     for ($i = 0; $i <= $navcounter; $i++) {
    //         $prefix .= '-';
    //     }

    //     $navcounter++;
    //     $items = $node->children;

    //     if ($items->count() == 0) {
    //         return '';
    //     }

    //     $lis = array();

    //     foreach ($items as $item) {
    //         if (empty($item->text) || !$item->display) {
    //             continue;
    //         }

    //         $item->hideicon = true;
    //         $action = '';

    //         if (empty($item->action)) {
    //             $action = $this->page->url;
    //         } else if (is_object($item->action) && property_exists($item->action, 'scheme')) {
    //             $action = new moodle_url($item->action);

    //             // CMDLTWO-240, CMDLTWO-370
    //             // If the node has children as well as an associated action,
    //             // then append it as a submenu item, to allow acccess from the menu.
    //             $children = $item->children;

    //             if ( ($children->count() > 1)
    //                  || (($children->count() === 1) && ($children->last()->action !== $item->action)) ) {

    //                  $subitemprops = array('text'   => $item->text,
    //                                        'type'   => $item->type,
    //                                        'action' => $action
    //                                        );
    //                  $item->add_node(new navigation_node($subitemprops));
    //             }
    //             // CMDLTWO-586: Cannot print chapter or book from book module.
    //         } else if (is_object($item->action) && property_exists($item->action, 'url')) {
    //             $action = new moodle_url($item->action->url);
    //         } else {
    //             $action = new moodle_url('javascript:void(0);');
    //         }

    //         $content  = $item->text . "|{$action}|{$item->title}\n";
    //         $content .= $this->topmenu_node($item, $navcounter);
    //         $content  = $prefix . $content;
    //         $lis[]    = $content;
    //     }

    //     if (count($lis)) {
    //         $navcounter = 0;
    //         $prefix = '';
    //         return $prefix . implode($lis);
    //     } else {
    //         return '';
    //     }
    // }

    //         /**
    //  * theme_cul_boost_core_renderer::render_custom_menu()
    //  * Render a bootstrap top menu. This renderer is needed to enable the Bootstrap style navigation.
    //  * @param custom_menu $menu
    //  * @return string $content
    //  */
    // protected function render_custom_menu(custom_menu $menu, $classes = 'nav d-flex flex-wrap align-items-stretch justify-content-center') {
    //     global $COURSE, $PAGE, $CFG, $USER;

    //     $content = '';
    //     $content .= html_writer::start_tag('ul', array('class' => $classes, 'role' => 'menubar'));

    //     foreach ($menu->get_children() as $item) {
    //         $content .= $this->render_custom_menu_item($item, 1);
    //     }

    //     $content .= html_writer::end_tag('ul');
    //     return $content;
    // }

}

/**
 * theme_cul_boost_utility
 *
 * @package theme
 * @subpackage cul
 * @copyright 2013 Tim Gagen
 *
 */
class theme_cul_boost_utility {
    /**
     * theme_cul_boost_utility::get_user_favourites()
     *
     * @return array of courseids indexed by order
     */
    public static function get_user_favourites() {
        global $DB, $USER;

        // If the users favourites have not been transferred to the Favourite API then use the user preference.
        $userfavourites = get_user_preferences('culcourse_listing_course_favourites');
        if (!is_null($userfavourites)) {
            $userfavourites = unserialize($userfavourites);
        } else {
            $usercontext = context_user::instance($USER->id);

            // Get the user favourites service, scoped to a single user (their favourites only).
            $userservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

            // Get the favourites, by type, for the user.
            $favourites = $userservice->find_favourites_by_type('core_course', 'courses');

            // Sort the favourites by order set and then last added.
            usort($favourites, function($a, $b) {
                /* We don't want null to count as zero because that will display last added courses first. */
                if (is_null($b->ordering)) {
                    $b->ordering = $a->ordering + 1;
                }

                $ordering = $a->ordering - $b->ordering;

                if ($ordering === 0) {
                    return $a->timemodified - $b->timemodified;
                }

                return $ordering;
            });

            $userfavourites = [];

            foreach ($favourites as $favourite) {
                $userfavourites[$favourite->ordering] = $favourite->itemid;
            }
        }

        return $userfavourites;
    }

    /**
     * theme_cul_boost_utility::get_user_favourites_courses()
     *
     * @return array of objects - course data (favorites)
     */
    public static function get_user_favourites_courses() {
        global $DB, $USER;

        $courseids = [];
        $userfavourites = self::get_user_favourites();

        if (is_array($userfavourites) && !empty($userfavourites)) {
            $courseids = array_values($userfavourites);
        }

        // Filter-out non-integers. There won't be any, but I'm defensive where SQL injection's concerned!
        $courseids = array_filter($courseids, function($a) {
           return preg_match("/\A\d+\z/", $a);
        });

        if (empty($courseids)) {
            return [];
        }

        $projection = 'id, shortname, fullname, idnumber, visible, category';
        $selection  = 'id IN (' . implode(', ', $courseids) . ')';

        if (!$favouritescourses = $DB->get_records_select('course', $selection, null, 'id', $projection, 0, 999)) {
            return [];
        }

        // Ensure correct ordering.
        foreach ($userfavourites as $ordpos => $courseid) {
            if (!empty($favouritescourses[$courseid]) && is_numeric($favouritescourses[$courseid]->id)) {
                // Set the display name.
                $favouritescourses[$courseid]->displayname = $favouritescourses[$courseid]->fullname;
                $userfavourites[$ordpos] = $favouritescourses[$courseid];
            } else {
                unset($userfavourites[$ordpos]);
            }
        }

        return $userfavourites;
    }

    /**
     * theme_cul_boost_utility::get_user_enrolled_courses()
     *
     * @return array
     */
    public static function get_user_enrolled_courses($years = 0) {
        $enrolledcourses = enrol_get_my_courses(null, 'fullname ASC', '999');

        if (count($enrolledcourses) == 0) {
            return false;
        }

        $currentcourses = [];
        $yeararray = [];
        $yearnr = date('y', time());

        for ($i = 0; $i < $years; $i++) {
            $yearstring = '20' . $yearnr . '-' . ($yearnr+1);
            $yeararray[$yearstring] = [];
            $yearnr--;
        }

        $yeararray['other'] = [];

        $periodarray = [
            'PRD3'=> [],
            'PRD2' => [],
            'PRD1' => []
        ];

        foreach ($enrolledcourses as $enrolledcourse) {
            // Set the display name.
            $enrolledcourse->displayname = $enrolledcourse->fullname;
            $found = false;
            $foundprd = false;

            foreach ($yeararray as $year => &$prds) {
                if (strpos($enrolledcourse->shortname, $year)) {
                    $found = true;

                    foreach ($periodarray as $prd => $courses) {
                        if (strpos($enrolledcourse->shortname, $prd)) {
                            $foundprd = true;

                            if (!array_key_exists($prd, $prds)) {
                                $prds[$prd] = [];
                            }

                            $prds[$prd][] = $enrolledcourse;
                        }                        
                    }

                    if (!$foundprd) {
                        $prds['other'][] = $enrolledcourse;
                    }                    
                }
            }

            if (!$found) {
                $yeararray['other'][] = $enrolledcourse;
            }
        }
// print_r($yeararray);
        return $yeararray;
    }

}