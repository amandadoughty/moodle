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

namespace theme_cul_boost\output;

defined('MOODLE_INTERNAL') || die;

use coding_exception;
use core_text;
use html_writer;
use tabobject;
use tabtree;
use core_course_category;
use core_course_list_element;
use completion_info;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use paging_bar;
use context_course;
use pix_icon;
use plugin_renderer_base;
use action_menu_filler;
use tool_policy;

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {
    /*** OVERRIDDEN FUNCTIONS ***/

    /**
     * Overridden function - Returns HTML to display a "Turn editing on/off" button in a form.
     * Overridden to use copy core function and ignore Boost
     * override.
     *
     * @param moodle_url $url The URL + params to send through when clicking the button
     * @return string HTML the button
     */
    public function edit_button(moodle_url $url) {

        $url->param('sesskey', sesskey());
        if ($this->page->user_is_editing()) {
            $url->param('edit', 'off');
            $editstring = get_string('turneditingoff');
        } else {
            $url->param('edit', 'on');
            $editstring = get_string('turneditingon');
        }

        return $this->single_button($url, $editstring);
    }

    /**
     * Overridden function - Returns a form with a single button.
     * Overridden to add sate
     *
     * Theme developers: DO NOT OVERRIDE! Please override function
     * {@link core_renderer::render_single_button()} instead.
     *
     * @param string|moodle_url $url
     * @param string $label button text
     * @param string $method get or post submit method
     * @param array $options associative array {disabled, title, etc.}
     * @return string HTML fragment
     */
    public function single_button($url, $label, $method='post', array $options=null) {
        if (!($url instanceof moodle_url)) {
            $url = new moodle_url($url);
        }

        if ($label == get_string('blockseditoff')
                || $label == get_string('turneditingoff')
                || $label == get_string('updatemymoodleoff')) {
            $label = get_string('off', 'theme_cul_boost');
            $options['state'] = 'off';
        } else if ($label == get_string('blocksediton')
                || $label == get_string('turneditingon')
                || $label == get_string('updatemymoodleon')) {
            $label = get_string('on', 'theme_cul_boost');
            $options['state'] = 'on';
        }

        $button = new single_button($url, $label, $method);
        foreach ((array)$options as $key=>$value) {
            $button->$key = $value;
        }

        if ($label == get_string('on', 'theme_cul_boost')
                || $label == get_string('off', 'theme_cul_boost')) {
            return $this->render_edit_button($button);
        }

        if (!isset($button->large)) {
            $button->small = true;
        }


        return $this->render($button);
    }

    /**
     * Overridden function - Return the navbar content so that it can be echoed out by the layout
     * Overridden to:
     *   Add classes to ol an li tags
     *   Remove arrow separator
     *   change home link to icon
     *
     * @return string XHTML navbar
     */
    public function navbar() {
        $items = $this->page->navbar->get_items();
        $itemcount = count($items);

        if ($itemcount === 0) {
            return '';
        }

        $htmlblocks = [];
        // Iterate the navarray and display each node

        for ($i=0;$i < $itemcount;$i++) {
            $item = $items[$i];
            $item->hideicon = true;

            $content = html_writer::tag(
                'li',
                $this->render($item),
                ['class' => 'breadcrumb-item d-inline-flex flex-wrap align-items-center']
            );

            $htmlblocks[] = $content;
        }

        $hometext = html_writer::tag('b', get_string('home'), ['class' => 'showoncollapse']);
        $homelink = html_writer::link(new moodle_url('/'), '<i class="fa fa-home"></i><span class="accesshide">' . $hometext . '</span>', ['class'=>'d-flex align-items-center']);
        $homeitem = html_writer::tag(
                    'li',
                    $homelink,
                    ['class' => 'breadcrumb-item d-inline-flex flex-wrap align-items-center']
                );
        array_shift($htmlblocks);
        array_unshift($htmlblocks, $homeitem);

        //accessibility: heading for navbar list  (MDL-20446)
        $navbarcontent = html_writer::tag('span', get_string('pagepath'),
                array('class' => 'accesshide', 'id' => 'navbar-label'));
        $navbarcontent .= html_writer::tag(
            'nav',
            html_writer::tag(
                'ol',
                join('', $htmlblocks),
                ['class' => 'breadcrumb d-flex flex-wrap align-items-center justify-content-center justify-content-md-start bg-transparent px-0 py-2 mb-0']
            ),
            ['aria-labelledby' => 'navbar-label']
        );
        // XHTML
        return $navbarcontent;
    }

    /**
     * Overridden function - Gets HTML for the page heading.
     * Overridden to add classes
     *
     * @since Moodle 2.5.1 2.6
     * @param string $tag The tag to encase the heading in. h1 by default.
     * @return string HTML.
     */
    public function page_heading($tag = 'h1') {
        global $COURSE;

        $heading = html_writer::tag($tag, $this->page->heading, ['class'=>'pageheading font-weight-normal mb-0']);

        return $heading;
    }

    // TODO test how much difference this makes to look and feel?

    /**
     * Overridden function - Construct a user menu, returning HTML that can be echoed out by a
     * layout file.
     * Overridden to:
     *    Move user text to dropdown
     *    Remove dividers
     *    Change styling of logged in as image
     *
     * @param stdClass $user A user object, usually $USER.
     * @param bool $withlinks true if a dropdown should be built.
     * @return string HTML fragment.
     */
    public function user_menu($user = null, $withlinks = null) {
        global $USER, $CFG;
        require_once($CFG->dirroot . '/user/lib.php');

        if (is_null($user)) {
            $user = $USER;
        }

        // Note: this behaviour is intended to match that of core_renderer::login_info,
        // but should not be considered to be good practice; layout options are
        // intended to be theme-specific. Please don't copy this snippet anywhere else.
        if (is_null($withlinks)) {
            $withlinks = empty($this->page->layout_options['nologinlinks']);
        }

        // Add a class for when $withlinks is false.
        $usermenuclasses = 'usermenu d-flex flex-wrap align-items-center';
        if (!$withlinks) {
            $usermenuclasses .= ' withoutlinks';
        }

        $returnstr = "";

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return $returnstr;
        }

        $loginpage = $this->is_login_page();
        $loginurl = get_login_url();
        // If not logged in, show the typical not-logged-in string.
        if (!isloggedin()) {
            $returnstr = '';
            if (!$loginpage) {
                $returnstr = "";
                $returnstr .= " <a class='btn btn-primary loginbtn' href=\"$loginurl\">" . get_string('login') . '</a>';
            }
            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );

        }

        // If logged in as a guest user, show a string to that effect.
        if (isguestuser()) {
            $returnstr = get_string('loggedinasguest');
            if (!$loginpage && $withlinks) {
                $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
            }

            return html_writer::div(
                html_writer::span(
                    $returnstr,
                    'login'
                ),
                $usermenuclasses
            );
        }

        // Get some navigation opts.
        $opts = user_get_user_navigation_info($user, $this->page, array('avatarsize'=>36));

        $divider = new stdClass();
        $divider->itemtype = 'divider';

        $logout = null;
        $switchrole = null;

        $check = array_pop($opts->navitems);
        if ($check->pix == 'a/logout') {
            $logout = $check;
        } else if ($check->pix == 'i/switchrole') {
            $logout = array_pop($opts->navitems);
            $switchrole = $check;
        }

        $opts->navitems[] = $divider;
        $opts->navitems[] = $logout;

        if ($switchrole) {
            $opts->navitems[] = $divider;
            $opts->navitems[] = $switchrole;
        }

        $avatarclasses = "avatars";
        $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current bg-light');
        $usertextcontents = $opts->metadata['userfullname'];

        // Other user.
        if (!empty($opts->metadata['asotheruser'])) {
            $avatarcontents .= html_writer::span(
                $opts->metadata['realuseravatar'],
                'avatar realuser'
            );
            $usertextcontents .= html_writer::tag(
                'span',
                get_string(
                    'loggedinas',
                    'moodle',
                    html_writer::span(
                        $opts->metadata['userfullname'],
                        'value'
                    )
                ),
                array('class' => 'meta viewingas')
            );
        }

        // Role.
        if (!empty($opts->metadata['asotherrole'])) {
            $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
            $usertextcontents .= html_writer::span(
                ' | '.$opts->metadata['rolename'],
                'meta role role-' . $role
            );
        }

        // User login failures.
        if (!empty($opts->metadata['userloginfail'])) {
            $usertextcontents .= html_writer::span(
                $opts->metadata['userloginfail'],
                'meta loginfailures'
            );
        }

        // MNet.
        if (!empty($opts->metadata['asmnetuser'])) {
            $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
            $usertextcontents .= html_writer::span(
                $opts->metadata['mnetidprovidername'],
                'meta mnet mnet-' . $mnet
            );
        }

        $returnstr .= html_writer::span(
            html_writer::span($usertextcontents, 'usertext') .
            html_writer::span($avatarcontents, $avatarclasses),
            'userbutton'
        );

        $usertextcontents = html_writer::tag('div', $usertextcontents, array('class'=>'py-2 px-3 h5 m-0 bg-medium username'));

        $content = '';
        foreach ($opts->navitems as $item) {
            switch ($item->itemtype) {
                case 'divider':
                    $content .= html_writer::empty_tag('hr');
                    break;
                case 'invalid':
                    break;
                case 'link':
                    $pix = null;
                    if (isset($item->pix) && !empty($item->pix)) {
                        $pix = new pix_icon($item->pix, $item->title, null, array('class' => 'iconsmall'));
                    } else if (isset($item->imgsrc) && !empty($item->imgsrc)) {
                        $item->title = html_writer::img(
                            $item->imgsrc,
                            $item->title,
                            array('class' => 'iconsmall')
                        ) . $item->title;
                    }

                    $content .= html_writer::start_tag('div', array('class'=>'menu-item'));
                    $icon = '';
                    if ($pix) {
                        $icon = html_writer::tag('span', $this->render($pix), array('class'=>'icon m-a-0'));
                    }
                    $title = html_writer::tag('span', $item->title, array('class'=>'title'));

                    $content .= html_writer::link($item->url, $icon.$title, array('class'=>'menu-link d-block dropdown-item px-3'));
                    $content .= html_writer::end_tag('div');
                    break;
            }
        }

        $user = html_writer::link(
            'javascript://void(0)',
            $avatarcontents,
            [
                'data-toggle'=>"dropdown",
                'class'=>'usermenu_header d-flex flex-wrap align-items-center dropdown-toggle text-default',
                'aria-label' => get_string('usermenu')
            ]
        );

        $content = html_writer::tag('div', $usertextcontents.$content, array('id'=>'usermenu_content', 'class'=>"usermenu_content m-0 pt-0 dropdown-menu dropdown-menu-right"));

        return html_writer::div(
            $user.$content,
            $usermenuclasses.' dropdown ml-4'
        );
    }

    /**
     * Overridden function - Internal implementation of user image rendering.
     * Overridden to hide photo if student has selected this option
     *
     * @param user_picture $userpicture
     * @return string
     */
    protected function render_user_picture(\user_picture $userpicture) {
        global $DB, $COURSE;        

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

        return parent::render_user_picture($userpicture);       
    }

    /**
     * Overridden function - Prints a nice side block with an optional header.
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.

        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid;
        $context->dockable = $bc->dockable;
        $context->id = $id;

        if (strpos($bc->attributes['class'], 'invisible') !== false) {
            $context->hidden = true;
        }

        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->type = $bc->attributes['data-block'];
        $context->title = $bc->title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);

        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }
        
        return $this->render_from_template('core/block', $context);
    }    

    /*** THEME SPECIFIC FUNCTIONS ***/

    protected function render_edit_button(single_button $button) {
        $data = $button->export_for_template($this);
        $data->state = $button->state;
        return $this->render_from_template('theme_cul_boost/edit_button', $data);
    }

    /**
     * City University main menu
     */

    /**
     * Renders the City University Main menu items.
     *
     * @return string the HTML to be output.
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

    /*
     * Overridden renderer - This renders the bootstrap top menu.
     * 
     * This renderer is needed to enable the Bootstrap style navigation.
     * Overriden to add id to nodes for JS
     */
    protected function render_custom_menu(custom_menu $menu, $module = 'theme_cul_boost') {
        global $CFG;

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            if (isset($langs[$currentlang])) {
                $currentlang = $langs[$currentlang];
            } else {
                $currentlang = $strlang;
            }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '';        
        
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $context->tours = false;        

            if ($item->get_title() == 'User tour') {
                $context->tours = true;
            } else if ($item->get_title()) {
                $id = strtolower($item->get_title());
                $id = str_replace(' ', '', $id);
                $id = 'theme_cul_boost_' . $id;
                $context->id = $id;
            }

            $content .= $this->render_from_template("$module/custom_menu_item", $context);
        }
        return $content;
    }

    /**
     * Builds the My Moodle menu.
     *
     * @return custom_menu object.
     */
    public function my_menu() {
        global $CFG, $PAGE;

        $mymoodletxt = get_string('mymoodle', 'my');
        $mymenuitems = "$mymoodletxt|/my/|$mymoodletxt";
        $mymenu = new custom_menu($mymenuitems, current_language());
        return $mymenu;
    }

    /**
     * Builds a menu of courses in the users favourites..
     *
     * @return custom_menu|bool false custom menu object or false.
     */    
    public function favourites_menu() {
        $favouritescourses = self::get_user_favourites_courses();

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
                    $favouritename);
            }
            return $favouritesmenu;
        }
        return false;
    }

    /**
     * Builds a menu of courses the user is enrolled in
     * broken down by year and term.
     *
     * @return custom_menu|bool false custom menu object or false.
     */
    public function my_modules_menu() {
        global $CFG;
        
        // Add 'My modules' menu - a list of courses in which the user is enrolled.
        $years = 0;

        if (isset($this->page->theme->settings->years)) {
            $years = $this->page->theme->settings->years;
        }

        $enrolledcourses = self::get_user_enrolled_courses($years);

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
     * Manipulates the modules navigation node.
     *
     * @return custom_menu|bool false custom menu object or false.
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

            $coursemenutree = $this->get_custom_menu_tree($currentcoursenav, get_string('coursemenu', 'theme_cul_boost'));
            $coursemenu = new custom_menu($coursemenutree, current_language());
            return $coursemenu;
        }

        return false;
    }

    /**
     * Gets the favourites from the user preference (if it exists)
     * else the Favourites API.
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
            $usercontext = \context_user::instance($USER->id);

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
                $userfavourites[] = $favourite->itemid;
            }
        }

        return $userfavourites;
    }

    /**
     * Gets the favourites for the My Favourites menu.
     *
     * @return array of objects - course data (favourites)
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

        $fields = 'id, shortname, fullname, idnumber, visible, category';
        $select  = 'id IN (' . implode(', ', $courseids) . ')';

        if (!$favouritescourses = $DB->get_records_select('course', $select, null, 'id', $fields, 0, 999)) {
            return [];
        }

        // Ensure correct ordering.
        foreach ($userfavourites as $key => $courseid) {
            if (!empty($favouritescourses[$courseid]) && is_numeric($favouritescourses[$courseid]->id)) {
                // Set the display name.
                $favouritescourses[$courseid]->displayname = $favouritescourses[$courseid]->fullname;
                // Replace the courseid with the course object.
                $userfavourites[$key] = $favouritescourses[$courseid];
            } else {
                unset($userfavourites[$key]);
            }
        }

        return $userfavourites;
    }

    /**
     * Gets the courses for the My Modules menu
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

        return $yeararray;
    }    

    /**
     * Creates the top level custom menu item.
     *
     * @param navigation_node $navigation
     * @param mixed $title
     * @return string Menu tree.
     */
    public function get_custom_menu_tree(navigation_node $navigation, $title) {
        if (!$navigation->has_children() || $navigation->children->count() == 0) {
            return '';
        }

        $content  = "$title|\n";
        $content .= $this->navigation_node_to_custom_menu($navigation);
        return $content;
    }

    /**
     * Converts a navigation node into custom menu syntax.
     *
     * @param mixed $node
     * @param integer $navcounter
     * @return string Menu tree.
     */
    protected function navigation_node_to_custom_menu(navigation_node $node, $navcounter = 0) {
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
            $content .= $this->navigation_node_to_custom_menu($item, $navcounter);
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

    /**
     * City University help menu
     */

    /**
     * Gets the help menu setting and converts string to object..
     *
     * @return custom_menu object.
     */     
    public function help_menu_items() {
        global $CFG, $PAGE;

        if (!empty($this->page->theme->settings->customhelpmenuitems)) {
            $customhelpmenuitems = $this->page->theme->settings->customhelpmenuitems;
            $helptxt = get_string('helptext', 'theme_cul_boost');
            $helpmenu = new custom_menu($customhelpmenuitems);

            return $helpmenu;
        }

        return false;
    }

    /**
     * Gets the help menu and renders it.
     *
     * @return string the HTML to be output.
     */ 
    public function help_menu() {
        global $CFG, $PAGE, $USER, $OUTPUT;

        $content = '';
        $showmenu = isloggedin() && !isguestuser();

        // Help & Support from CUL Theme Settings
        if ($showmenu) {
            if ($helpmenu = $this->help_menu_items()) {
                $content .= $this->render_custom_menu($helpmenu, 'core');
            }
        }

        return $content;
    }

    /**
     * Outputs block regions.
     *
     * @return string the HTML to be output.
     */ 
    public function synergyblocks($region, $classes = array(), $tag = 'aside') {
        $classes = (array)$classes;
        $classes[] = 'block-region';
        $attributes = array(
            'id' => 'block-region-'.preg_replace('#[^a-zA-Z0-9_\-]+#', '-', $region),
            'class' => join(' ', $classes),
            'data-blockregion' => $region,
            'data-droptarget' => '1'
        );
        $content = '';

        if ($this->page->blocks->region_has_content($region, $this)) {
            $content = $this->blocks_for_region($region);
        }

        return html_writer::tag($tag, $content, $attributes);
    }

    /**
     * Checks if page requires gradebook discalimer.
     *
     * @return bool true if page requires discalimer.
     */ 
    public function gradebook_disclaimer() {
        $gradebookids = array (
            'page-grade-report-user-index',
            'page-grade-report-culuser-index',
            'page-grade-report-overview-index',
            'page-course-user'
        );

        if (in_array($this->page->bodyid, $gradebookids)) {
            return true;
        }

        return false;
    }

    /**
     * Provides personalisation data.
     *
     * @return stdClass object containing personalised settings.
     */ 
    public function user_info() {
        global $USER;

        $userinfo = new stdClass();

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
        $userinfo->logoprefix = "city";
        $userinfo->gaschool = "UUCITY";
        $userinfo->title = "City Unversity London homepage";
        $userinfo->website = "city.ac.uk";
        $userinfo->studenthub = "https://studenthub.city.ac.uk/";
        $userinfo->staffhub = "https://staffhub.city.ac.uk/";
        $userinfo->library = "https://www.city.ac.uk/library";

        // City Uni Central Services.
        if ((trim($userschool) == 'UUCITY') && (substr(trim($userdept), 0, 1) == 'U')) {
            $userinfo->gaschool = 'UUCITY';
        }
        // Law School.
        if (trim($userschool) == 'LLILAW')  {
            $userinfo->gaschool = 'LLILAW';
        }
        // Cass Business School.
        if (trim($userschool) == 'BBCASS') {
            $userinfo->logoprefix = 'cass';
            $userinfo->gaschool = 'BBCASS';
            $userinfo->title = "Cass Business School homepage";
            // CMDLTWO-362 Cass global nav.
            $userinfo->website = "cass.city.ac.uk";
            $userinfo->studenthub = "http://www.cass.city.ac.uk/intranet/student";
            $userinfo->staffhub = "http://www.cass.city.ac.uk/intranet/staff";
            // $userinfo->library = "http://www.cass.city.ac.uk/intranet/staff/services/learning-resource-centre";
        }
        // School of Arts and Social Sciences
        if ((trim($userschool) == 'AASOAR') OR (trim($userschool) == 'ASSASS') OR (trim($userschool) == 'SSSOSS') OR (trim($userschool) == 'ASSOCL')) {
            $userinfo->gaschool = 'ASSASS';
        }
        // School of Engineering and Maths and Informatics
        if ((trim($userschool) == 'EESEMS') OR (trim($userschool) == 'EEMCSE') OR (trim($userschool) == 'IISOIN') OR (substr(trim($userschool), 0, 2) == 'EE')) {
            $userinfo->gaschool = 'EEMCSE';
        }
        // School of Health Sciences (leave as schs for Google Analytics).
        if ((trim($userschool) == 'HASAHS') OR (trim($userschool) == 'HNSONM') OR (trim($userschool) == 'HHSOHS') OR (trim($userschool) == 'HSSOHS')) {
            $userinfo->gaschool = 'HSSOHS';
        }

        $userinfo->logourl = $this->image_url($userinfo->logoprefix . '-logo', 'theme');
        $userinfo->logourlwhite = $this->image_url($userinfo->logoprefix . '-logo-white', 'theme');

        return $userinfo;
    }

    /**
     * Outputs the Google Analytics code.
     *
     * @return string the HTML to be output.
     */     
    public function google_analytics() {
        global $DB , $USER, $COURSE, $PAGE;

        // Has the user accepted tracking cookies?
        $cookiepolicy = get_config('theme_cul_boost', 'cookiepolicy');

        if ($cookiepolicy) {
            $accepted = tool_policy\api::is_user_version_accepted($USER->id, $cookiepolicy);
            // If null or false (declined).
            if (!$accepted) {
                return '';
            }
        }

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

    /**
     * Outputs the favourite button on course pages.
     *
     * @return string the HTML to be output.
     */    
    public function favourite_course_button() {
        global $CFG, $PAGE, $COURSE, $USER;
        
        $content = '';
        $isfav = false;

        // Add Favourite url
        $favourites = null;

        // Try the old method of saving favourites in user preference.
        if (!is_null($favourites = get_user_preferences('culcourse_listing_course_favourites'))) {
            $favourites = unserialize($favourites);

            if ($favourites && in_array($COURSE->id, $favourites)){
                $isfav = true;
            }
        // Favourites have been transferred to Favourite API.
        } else {
            $usercontext = \context_user::instance($USER->id);
            $coursecontext = \context_course::instance($COURSE->id);

            // Get the user favourites service, scoped to a single user (their favourites only).
            $ufservice = \core_favourites\service_factory::get_service_for_user_context($usercontext);

            $isfav = $ufservice->favourite_exists('core_course', 'courses', $COURSE->id, $coursecontext);
        }

        if ($isfav) {
            $action = 'remove';
            $class = 'favourited';
            $id = 'theme-cul_boost-removefromfavourites';
            $actionstring = 'favouriteremove';
        } else {
            $action = 'add';
            $class = '';
            $id = 'theme-cul_boost-addtofavourites';
            $actionstring = 'favouriteadd';
        }

        $favouriteurl = new moodle_url('/theme/cul_boost/favourite_post.php', array(
            'action' => $action,
            'cid' => $COURSE->id,
            'sesskey' => sesskey()
        ));

        $favouritetxt = get_string($actionstring, 'theme_cul_boost');

        $data = [
            'favouriteurl' => $favouriteurl,
            'favouritetxt' => $favouritetxt,
            'class' => $class,
            'id' => $id         
        ];

        return $this->render_from_template('theme_cul_boost/favourite_course_button', $data);
    }

    /**
     * Returns a button to make a hidden course visible.
     *
     * @return string the HTML to be output.
     */
    public function show_course_button() {

        global $COURSE, $OUTPUT;

        $content = '';
        $coursecontext = context_course::instance($COURSE->id);

        if (!has_capability('moodle/course:update', $coursecontext)) {
            return $content;
        }        

        $showcourseurl = new moodle_url(
            '/theme/cul_boost/unhide_post.php', 
            [
                'cid' => $COURSE->id,
                'sesskey' => sesskey()
            ]
        );

        $showcoursetxt = get_string('showcourse', 'theme_cul_boost');       

        return $OUTPUT->single_button($showcourseurl, $showcoursetxt, 'post', ['class' => 'showcourse d-inline-block ml-4']);
    }

    /**
     * Returns menu item html
     * Serves requests to /theme/cul_boost/favourites_ajax.php
     *
     * @return array
     */
    public function favourites_ajax() {
        global $DB, $CFG;

        $content = '';
        $favouritescourses = self::get_user_favourites_courses();
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
            $context = $item->export_for_template($this);
            $id = strtolower($item->get_title());
            $id = str_replace(' ', '', $id);
            $id = 'theme_cul_boost_' . $id;
            $context->id = $id;
            $content .= $this->render_from_template('theme_cul_boost/custom_menu_item', $context);
        }

        return $content;
    }

    // Overriding to include missing aria-labelled by id.
    // Will be fixed by MDL-54596/MDL-56260/MDL-54674.

    /**
     * Renders a navigation node object.
     *
     * @param navigation_node $item The navigation node to render.
     * @return string HTML fragment
     */
    public function render_navigation_node(navigation_node $item, $arialabelledbyid = null) {
        $name = $item->get_content();
        $content = $item->get_content();
        $title = $item->get_title();
        if ($item->icon instanceof renderable && !$item->hideicon) {
            $icon = $this->render($item->icon);
            $content = $icon.$content; // use CSS for spacing of icons
        }
        if ($item->helpbutton !== null) {
            $content = trim($item->helpbutton).html_writer::tag('span', $content, array('class'=>'clearhelpbutton', 'tabindex'=>'0'));
        }
        if ($content === '') {
            return '';
        }
        // settingsblock.js replaces the site admin link with a span
        // and this loses the id we need for aria-labelledby.
        if ($item->key == 'siteadministration') {
            $attributes = array('tabindex'=>'0'); //add tab support to span but still maintain character stream sequence.
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if ($arialabelledbyid) {
                $attributes['id'] = $arialabelledbyid;
            }
            $content = html_writer::tag('span', $content, $attributes);
        } else if ($item->action instanceof action_link) {
            $link = $item->action;
            if ($item->hidden) {
                $link->add_class('dimmed');
            }
            if (!empty($content)) {
                // Providing there is content we will use that for the link content.
                $link->text = $content;
            }
            if ($arialabelledbyid) {
                $link->action->$attributes['id'] = $arialabelledbyid;
            }
            $content = $this->render($link);
        } else if ($item->action instanceof moodle_url) {
            $attributes = array();
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if ($arialabelledbyid) {
                $attributes['id'] = $arialabelledbyid;
            }
            $content = html_writer::link($item->action, $content, $attributes);

        } else if (is_string($item->action) || empty($item->action)) {
            $attributes = array('tabindex'=>'0'); //add tab support to span but still maintain character stream sequence.
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] = 'dimmed_text';
            }
            if ($arialabelledbyid) {
                $attributes['id'] = $arialabelledbyid;
            }
            $content = html_writer::tag('span', $content, $attributes);
        }

        //   if ($name == 'Site administration') {
        //     echo $content;die;
        // }
        return $content;
    }    
}