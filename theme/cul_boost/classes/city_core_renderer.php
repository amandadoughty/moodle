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
	 * Internal implementation of user image rendering.
	 *
	 * @param user_picture $userpicture
	 * @return string
	 */
	protected function render_user_picture(\user_picture $userpicture) {
	    global $CFG, $DB, $COURSE;        

	    $context = \context_course::instance($COURSE->id);

	    if (!has_capability('moodle/course:viewhiddenuserfields', $context)) {
	    

	        $sql = 'SELECT shortname, data
	                FROM {user_info_data} uid
	                JOIN {user_info_field} uif
	                ON uid.fieldid = uif.id
	                WHERE uid.userid = :userid';

	        if ($result = $DB->get_records_sql($sql, array('userid' => $userpicture->user->id))){                      ;
	            if(isset($result['publicphoto']->data) && $result['publicphoto']->data == 0) {
	                $userpicture->user->picture = 0;
	            }
	        }
	    }

	    $user = $userpicture->user;

	    if ($userpicture->alttext) {
	        if (!empty($user->imagealt)) {
	            $alt = $user->imagealt;
	        } else {
	            $alt = get_string('pictureof', '', fullname($user));
	        }
	    } else {
	        $alt = '';
	    }

	    if (empty($userpicture->size)) {
	        $size = 35;
	    } else if ($userpicture->size === true or $userpicture->size == 1) {
	        $size = 100;
	    } else {
	        $size = $userpicture->size;
	    }

	    $class = $userpicture->class;

	    if ($user->picture == 0) {
	        $class .= ' defaultuserpic';
	    }

	    $src = $userpicture->get_url($this->page, $this);

	    $attributes = array('src'=>$src, 'alt'=>$alt, 'title'=>$alt, 'class'=>$class, 'width'=>$size, 'height'=>$size);
	    if (!$userpicture->visibletoscreenreaders) {
	        $attributes['role'] = 'presentation';
	    }

	    // get the image html output fisrt
	    $output = html_writer::empty_tag('img', $attributes);

	    // Show fullname together with the picture when desired.
	    if ($userpicture->includefullname) {
	        $output .= fullname($userpicture->user);
	    }

	    // then wrap it in link if needed
	    if (!$userpicture->link) {
	        return $output;
	    }

	    if (empty($userpicture->courseid)) {
	        $courseid = $this->page->course->id;
	    } else {
	        $courseid = $userpicture->courseid;
	    }

	    if ($courseid == SITEID) {
	        $url = new moodle_url('/user/profile.php', array('id' => $user->id));
	    } else {
	        $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
	    }

	    $attributes = array('href'=>$url);
	    if (!$userpicture->visibletoscreenreaders) {
	        $attributes['tabindex'] = '-1';
	        $attributes['aria-hidden'] = 'true';
	    }

	    if ($userpicture->popup) {
	        $id = html_writer::random_id('userpicture');
	        $attributes['id'] = $id;
	        $this->add_action_handler(new popup_action('click', $url), $id);
	    }

	    return html_writer::tag('a', $output, $attributes);
	}

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
            $favouritestxt = get_string('favourites', 'theme_cul_boost');
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

                $favouritename = theme_cul_boost_utility::shorten_string($favouritescourse->displayname);
                $favourites->add($favouritename,
                    new moodle_url('/course/view.php', array('id' => $favouritescourse->id)),
                    $favouritescourse->displayname);
            }
            return $favouritesmenu;
        }
        return false;
    }

    public function my_modules_menu() {
        // Add 'My modules' menu - a list of courses in which the user is enrolled.
        $years = 0;

        if (isset($this->page->theme->settings->years)) {
            $years = $this->page->theme->settings->years;
        }

        $enrolledcourses = theme_cul_boost_utility::get_user_enrolled_courses($years);
        $archivelink = $this->archive_link();

        if ($enrolledcourses || $archivelink) {
            $maxdropdowncourses = 15;
            $countcourses = 0;
            $mycoursestxt = get_string('mycourses', 'theme_cul_boost');
            $mycoursesmenu = new custom_menu('', current_language());
            $mycourses = $mycoursesmenu->add($mycoursestxt, new moodle_url(''), $mycoursestxt, 12);

            if ($enrolledcourses) {
                foreach($enrolledcourses as $year => $enrolledcourse) {
                    if ($year == "other") {
                        continue;
                    }
                    if ($countcourses > $maxdropdowncourses ) {
                        $mycourses->add(get_string('morecourses', 'theme_cul_boost'), new moodle_url('/my'), null);
                        break;
                    }
                    if (count($enrolledcourse) > 0) {
                        $yearmenu = $mycourses->add($year);
                    }
                    foreach ($enrolledcourse as $mycourse) {
                        $coursename = theme_cul_boost_utility::shorten_string($mycourse->displayname);
                        $yearmenu->add($coursename,
                            new moodle_url('/course/view.php', array('id' => $mycourse->id)),
                            $mycourse->displayname);
                        $countcourses++;
                    }
                }

                foreach ($enrolledcourses['other'] as $othercourse) {
                    $coursename = theme_cul_boost_utility::shorten_string($othercourse->displayname);

                    if ( $countcourses < $maxdropdowncourses ) {
                        $mycourses->add($coursename,
                            new moodle_url('/course/view.php', array('id' => $othercourse->id)),
                            $othercourse->displayname);
                    }

                    $countcourses++;
                }
            }

            $mycourses->add(get_string('archive', 'theme_cul_boost'),
                        $archivelink['href'],
                        $archivelink['tooltip']
                        );


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
     * theme_cul_boost_core_renderer::render_custom_menu()
     * Render a bootstrap top menu. This renderer is needed to enable the Bootstrap style navigation.
     * @param custom_menu $menu
     * @return string $content
     */
    protected function render_custom_menu(custom_menu $menu, $classes = 'nav d-flex flex-wrap align-items-stretch') {
        global $COURSE, $PAGE, $CFG, $USER;

        $content = '';
        $content .= html_writer::start_tag('ul', array('class' => $classes, 'role' => 'menubar'));

        foreach ($menu->get_children() as $item) {
            $content .= $this->render_custom_menu_item($item, 1);
        }

        $content .= html_writer::end_tag('ul');
        return $content;
    }

    /**
     * This code renders the custom menu items for the
     * bootstrap dropdown menu.
     */
    protected function render_custom_menu_item(custom_menu_item $menunode, $level = 0 ) {
        static $submenucount = 0;

        $id = strtolower($menunode->get_title());
        $id = str_replace(' ', '', $id);
        $id = 'theme-cul_boost-' . $id;

        if ($menunode->has_children()) {

            if ($level == 1) {
                $class = 'dropdown d-flex flex-wrap align-items-center py-3';
            } else {
                $class = 'dropdown-item dropdown-submenu';
            }

            $content = html_writer::start_tag('li', array(
                'id' => $id,
                'class' => $class
                )
            );
            // If the child has menus render it as a sub menu.
            $submenucount++;

            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '#cm_submenu_'.$submenucount;
            }

            $content .= html_writer::start_tag('a', array('href' => $url,
                'class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'title' => $menunode->get_title()));
            $content .= $menunode->get_text();

            $content .= '</a>';
            $content .= '<ul class="dropdown-menu mt-0">';

            foreach ($menunode->get_children() as $menunode) {
                $content .= $this->render_custom_menu_item($menunode, 0);
            }

            $content .= '</ul>';
        } else {
            
            $class = 'dropdown-item d-flex flex-wrap align-items-center';

            if (!$menunode->has_children() && $level == 1) {
                $class = 'dropdown-item d-flex flex-wrap align-items-center py-3';
            }

            $content = html_writer::start_tag('li', array('id' => $id, 'class'=>$class));

            // The node doesn't have children so produce a final menuitem.
            if ($menunode->get_url() !== null) {
                $url = $menunode->get_url();
            } else {
                $url = '';
            }

            $content .= html_writer::link($url, $menunode->get_text(), array('title' => $menunode->get_title()));
        }
        return $content;
    }

    /**
     * This code renders the navbar button to control the display of the custom menu
     * on smaller screens.
     *
     * Do not display the button if the menu is empty.
     *
     * @return string HTML fragment
     */
    public function navbar_button() {
        global $CFG;

        if (empty($CFG->custommenuitems) && $this->lang_menu() == '') {
            return '';
        }

        $iconbar = html_writer::tag('span', '', array('class' => 'icon-bar'));
        $button = html_writer::tag('a', $iconbar . "\n" . $iconbar. "\n" . $iconbar, array(
            'class'       => 'btn btn-navbar',
            'data-toggle' => 'collapse',
            'data-target' => '.nav-collapse'
        ));
        return $button;
    }    

    /**
     * Renders tabtree
     *
     * @param tabtree $tabtree
     * @return string
     */
    protected function render_tabtree(tabtree $tabtree) {
        if (empty($tabtree->subtree)) {
            return '';
        }

        $firstrow = $secondrow = '';

        foreach ($tabtree->subtree as $tab) {
            $firstrow .= $this->render($tab);

            if (($tab->selected || $tab->activated) && !empty($tab->subtree) && $tab->subtree !== array()) {
                $secondrow = $this->tabtree($tab->subtree);
            }
        }

        return html_writer::tag('ul', $firstrow, array('class' => 'nav nav-tabs')) . $secondrow;
    }

    /**
     * Renders tabobject (part of tabtree)
     *
     * This function is called from {@link core_renderer::render_tabtree()}
     * and also it calls itself when printing the $tabobject subtree recursively.
     *
     * @param tabobject $tabobject
     * @return string HTML fragment
     */
    protected function render_tabobject(tabobject $tab) {
        if ($tab->selected or $tab->activated) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'active'));
        } else if ($tab->inactive) {
            return html_writer::tag('li', html_writer::tag('a', $tab->text), array('class' => 'disabled'));
        } else {
            if (!($tab->link instanceof moodle_url)) {
                $link = "<a href=\"$tab->link\" title=\"$tab->title\">$tab->text</a>";
            } else {
                $link = html_writer::link($tab->link, $tab->text, array('title' => $tab->title));
            }

            return html_writer::tag('li', $link);
        }
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
            $favouritestxt = get_string('favourites', 'theme_cul_boost');
            $favourites = $menu->add($favouritestxt, new moodle_url(''), $favouritestxt, 11);

            foreach ($favouritescourses as $favouritescourse) {
                $coursecontext = context_course::instance($favouritescourse->id);

                if (!has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                    if (!$favouritescourse->visible) {
                        continue;
                    }
                }

                $favouritename = theme_cul_boost_utility::shorten_string($favouritescourse->displayname);
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

    /**
     *
     *
     *
     * @return
     */

    function archive_link() {
        global $CFG, $USER, $DB, $OUTPUT;

        $link = array(
            'islink' => false,
            'href' => new moodle_url(''),
            'tooltip' => ''
            );

        // shortcut -  only for logged in users!
        if (!isloggedin() || isguestuser()) {
            return $link;
        }

        if (\core\session\manager::is_loggedinas()) {
            $link['tooltip'] = get_string('notpermittedtojumpas', 'mnet');
            return $link;
        }

        // according to start_jump_session,
        // remote users can't on-jump
        // so don't show this block to them
        if (is_mnet_remote_user($USER)) {
            if (debugging() and !empty($CFG->debugdisplay)) {
                $link['tooltip'] = get_string('error_localusersonly', 'block_mnet_hosts');
            }

            return $link;
        }

        if (!is_enabled_auth('mnet')) {
            if (debugging() and !empty($CFG->debugdisplay)) {
                $link['tooltip'] = get_string('error_authmnetneeded', 'block_mnet_hosts');
            }

            return $link;
        }

        if (!has_capability('moodle/site:mnetlogintoremote', context_system::instance(), NULL, false)) {
            if (debugging() and !empty($CFG->debugdisplay)) {
                $link['tooltip'] = get_string('error_roamcapabilityneeded', 'block_mnet_hosts');
            }

            return $link;
        }

        // TODO: Test this query - it's appropriate? It works?
        // get the hosts and whether we are doing SSO with them
        $sql = "
             SELECT DISTINCT
                 h.id,
                 h.name,
                 h.wwwroot,
                 a.name as application,
                 a.display_name
             FROM
                 {mnet_host} h,
                 {mnet_application} a,
                 {mnet_host2service} h2s_IDP,
                 {mnet_service} s_IDP,
                 {mnet_host2service} h2s_SP,
                 {mnet_service} s_SP
             WHERE
                 h.id <> ? AND
                 h.id <> ? AND
                 h.id = h2s_IDP.hostid AND
                 h.deleted = 0 AND
                 h.applicationid = a.id AND
                 h2s_IDP.serviceid = s_IDP.id AND
                 s_IDP.name = 'sso_idp' AND
                 h2s_IDP.publish = '1' AND
                 h.id = h2s_SP.hostid AND
                 h2s_SP.serviceid = s_SP.id AND
                 s_SP.name = 'sso_idp' AND
                 h2s_SP.publish = '1' AND
                 h.wwwroot = 'http://moodle-archive.city.ac.uk'
             ORDER BY
                 a.display_name,
                 h.name";

        $hosts = $DB->get_records_sql($sql, array($CFG->mnet_localhost_id, $CFG->mnet_all_hosts_id));
        $host = array_pop($hosts);

        if ($host) {
            if ($host->id == $USER->mnethostid) {
                $link['href'] = new moodle_url("{$host->wwwroot}");
            } else {
                $link['href'] = new moodle_url("{$CFG->wwwroot}/auth/mnet/jump.php?hostid={$host->id}");
            }

            $link['islink'] = true;
            $link['tooltip'] = get_string('archivedisplay', 'theme_cul_boost');
        }

        return $link;
    }

    /**
     * Overridden renderer - Returns a search box.
     *
     * @param  string $id     The search box wrapper div id, defaults to an autogenerated one.
     * @return string         HTML with the search form hidden by default.
     */
    public function search_box($id = false) {
        global $CFG;

        // Accessing $CFG directly as using \core_search::is_global_search_enabled would
        // result in an extra included file for each site, even the ones where global search
        // is disabled.
        if (empty($CFG->enableglobalsearch) || !has_capability('moodle/search:query', context_system::instance())) {
            return '';
        }

        if ($id == false) {
            $id = uniqid();
        } else {
            // Needs to be cleaned, we use it for the input id.
            $id = clean_param($id, PARAM_ALPHANUMEXT);
        }

        // JS to animate the form.
        $this->page->requires->js_call_amd('core/search-input', 'init', array($id));

        $searchicon = html_writer::tag('div', '<i class="fa fa-search">&nbsp;</i>',
            array('role' => 'button', 'tabindex' => 0));
        $formattrs = array('class' => 'search-input-form', 'action' => $CFG->wwwroot . '/search/index.php');
        $inputattrs = array('type' => 'text', 'name' => 'q', 'placeholder' => get_string('search', 'search'),
            'size' => 13, 'tabindex' => -1, 'id' => 'id_q_' . $id);

        $contents = html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
            array('for' => 'id_q_' . $id, 'class' => 'accesshide')) . html_writer::tag('input', '', $inputattrs);
        $searchinput = html_writer::tag('form', $contents, $formattrs);

        return html_writer::tag('div', $searchicon . $searchinput, array('class' => 'search-input-wrapper nav-link', 'id' => $id));
    }    

    /**
     * Overwritten renderer - Output all the blocks in a particular region.
     *
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        global $COURSE;

        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);
        $blocks = $this->page->blocks->get_blocks_for_region($region);
        $lastblock = null;
        $zones = array();
        $can_edit = false;
        $admininstanceid = -1;

        if (has_capability('moodle/course:update', context_course::instance($COURSE->id))) {
            $can_edit = true;
        }

        foreach ($blocks as $block) {
            if (!$can_edit && $block->name() == 'settings') {
                $admininstanceid = $block->instance->id;
                continue;
            }

            $zones[] = $block->title;
        }

        $output = '';

        foreach ($blockcontents as $bc) {
            if ($bc->blockinstanceid == $admininstanceid) {
                continue;
            }

            if ($bc instanceof block_contents) {
                $output .= $this->block($bc, $region);
                $lastblock = $bc->title;
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc, $zones, $lastblock, $region);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }

    // public function render_url_select(url_select $select) {
    //     global $CFG, $COURSE;

    //     if($select->formid == 'choosepluginreport') {
    //         $gradereportuser = $CFG->wwwroot . '/grade/report/user/index.php?id=' . $COURSE->id;

    //         if(array_key_exists($gradereportuser, $select->urls[0]['View'])) {
    //             unset($select->urls[0]['View'][$gradereportuser]);
    //         }
    //     }

    //     return parent::render_url_select($select);
    // }  

    /**
     * theme_cul_boost_topmenu_renderer::topmenu_tree()
     *
     * @param navigation_node $navigation
     * @param mixed $title
     * @return string Menu tree.
     */
    public function topmenu_tree(navigation_node $navigation, $title) {
        if (!$navigation->has_children() || $navigation->children->count() == 0) {
            return '';
        }

        $content  = "$title|\n";
        $content .= $this->topmenu_node($navigation);
        return $content;
    }

    /**
     * theme_cul_boost_topmenu_renderer::topmenu_node()
     *
     * @param mixed $node
     * @param integer $navcounter
     * @return
     */
    protected function topmenu_node(navigation_node $node, $navcounter = 0) {
        $prefix = '';
        $maxlen = 28; // Maximum length of menu item text before it will be shortened for display.

        for ($i = 0; $i <= $navcounter; $i++) {
            $prefix .= '-';
        }

        $navcounter++;
        $items = $node->children;

        if ($items->count() == 0) {
            return '';
        }

        $lis = array();

        foreach ($items as $item) {
            if (empty($item->text) || !$item->display) {
                continue;
            }

            $item->hideicon = true;
            $action = '';

            if (empty($item->action)) {
                $action = $this->page->url;
            } else if (is_object($item->action) && property_exists($item->action, 'scheme')) {
                $action = new moodle_url($item->action);

                // CMDLTWO-240, CMDLTWO-370
                // If the node has children as well as an associated action,
                // then append it as a submenu item, to allow acccess from the menu.
                $children = $item->children;

                if ( ($children->count() > 1)
                     || (($children->count() === 1) && ($children->last()->action !== $item->action)) ) {

                     $subitemprops = array('text'   => $item->text,
                                           'type'   => $item->type,
                                           'action' => $action
                                           );
                     $item->add_node(new navigation_node($subitemprops));
                }
                // CMDLTWO-586: Cannot print chapter or book from book module.
            } else if (is_object($item->action) && property_exists($item->action, 'url')) {
                $action = new moodle_url($item->action->url);
            } else {
                $action = new moodle_url('javascript:void(0);');
            }

            $content  = $item->text . "|{$action}|{$item->title}\n";
            $content .= $this->topmenu_node($item, $navcounter);
            $content  = $prefix . $content;
            $lis[]    = $content;
        }

        if (count($lis)) {
            $navcounter = 0;
            $prefix = '';
            return $prefix . implode($lis);
        } else {
            return '';
        }
    }

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
     * theme_cul_boost_utility::get_user_favourites_courses()
     *
     * @return array of objects - course data (favorites)
     */
    public static function get_user_favourites_courses() {
        global $DB;

        $courseids = array();
        $userfavourites = unserialize(get_user_preferences('culcourse_listing_course_favourites'));

        if (is_array($userfavourites) && !empty($userfavourites)) {
            $courseids = array_values($userfavourites);
        } else {
            return array();
        }

        // Filter-out non-integers. There won't be any, but I'm defensive where SQL injection's concerned!
        $courseids = array_filter($courseids, create_function('$a', 'return preg_match("/\A\d+\z/", $a);'));

        if (empty($courseids)) {
            return array();
        }

        $projection = 'id, shortname, fullname, idnumber, visible, category';
        $selection  = 'id IN (' . implode(', ', $courseids) . ')';

        if (!$favouritescourses = $DB->get_records_select('course', $selection, null, 'id', $projection, 0, 999)) {
            return array();
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

        $currentcourses = array();
        $yeararray = array();
        $yearnr = date('y', time());

        for ($i = 0; $i < $years; $i++) {
            $yearstring = '20' . $yearnr . '-' . ($yearnr+1);
            $yeararray[$yearstring] = array();
            $yearnr--;
        }

        $yeararray['other'] = array();

        foreach ($enrolledcourses as $enrolledcourse) {
            // Set the display name.
            $enrolledcourse->displayname = $enrolledcourse->fullname;
            $found = false;

            foreach ($yeararray as $year => &$courses) {
                if (strpos($enrolledcourse->shortname, $year)) {
                    $courses[] = $enrolledcourse;
                    $found = true;
                }
            }

            if (!$found) {
                $yeararray['other'][] = $enrolledcourse;
            }
        }

        return $yeararray;
    }

    /**
     * theme_cul_boost_utility::shorten_string()
     * Shorten a string for display, and append an ellipsis to indicate continuation.
     * //TODO: Candidate for moving to external general culutility class...
     * @param string $string The string to shorten.
     * @param integer $maxlen The total maximum length of output string, including ellipsis.
     * @param string $ellipsis The string to append to the shortened string to indicate continuation.
     * @return string
     */
    public static function shorten_string($string, $maxlen = 22, $ellipsis = '..') {
        $boundary = $maxlen - strlen($ellipsis);

        if ((strlen($string) > $maxlen)) {
            $shortstring = substr($string, 0, $boundary) . $ellipsis;
        } else {
            $shortstring = $string;
        }

        return $shortstring;
    }
}