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

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_boost
 * @copyright  2012 Bas Brands, www.basbrands.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {

	public function global_search() {
	    global $CFG;

	    $id = 'id_q_' . uniqid();
	    $output = html_writer::start_tag('div', array('class' => 'slidersearchform d-flex flex-wrap align-items-center'));
	    $output .= html_writer::start_tag('form', array('action' => '' . $CFG->wwwroot . '/search/index.php', 'method' => 'get'));
	    $output .= html_writer::tag('label', get_string('enteryoursearchquery', 'search'),
            array('for' => $id, 'class' => 'accesshide'));
	    $output .= html_writer::empty_tag('input', array(
	    	'id' => $id,
	        'class' => 'w-100',
	        'type' => 'text',
	        'name' => 'q',
	        'alt' => get_string('searchfor','theme_cul_boost'),
	        'placeholder' => get_string('searchfor','theme_cul_boost')
	    ));
	    $output .= html_writer::tag('button',
	    	'<i class="fa fa-search"></i><span class="accesshide">Search</span>', array(
	        'type' => 'submit',
	        'class' => 'btn btn-primary p-0'
	    ));
	    $output .= html_writer::end_tag('form');
	    $output .= html_writer::end_tag('div');
	    // END Search

	    return $output;
	}

	/**
	 * Returns HTML to display a "Turn editing on/off" button in a form.
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

	protected function render_edit_button(single_button $button) {
	    $data = $button->export_for_template($this);
	    $data->state = $button->state;
	    return $this->render_from_template('theme_cul_boost/edit_button', $data);
	}

	/*
	 * This renders the navbar.
	 * Uses bootstrap compatible html.
	 */
	public function navbar() {
        $breadcrumbs = array();
        $items = $this->page->navbar->get_items();
        foreach ($items as $item) {
            $item->hideicon = true;
            $breadcrumbs[] = $this->render($item);
        }

        $hometext = html_writer::tag('b', get_string('home'), array('class' => 'showoncollapse'));
        $homelink = html_writer::link(new moodle_url('/'), '<i class="fa fa-home"></i><span class="accesshide">' . $hometext . '</span>', ['class'=>'d-flex align-items-center']);
        array_shift($breadcrumbs);
        array_unshift($breadcrumbs, $homelink);

        $listitems = '<li class="breadcrumb-item d-inline-flex flex-wrap align-items-center">' . join(' </li><li class="breadcrumb-item d-inline-flex flex-wrap align-items-center">', $breadcrumbs) . '</li>';
        $title = '<span class="accesshide">' . get_string('pagepath') . '</span>';
        return $title . '<ol class="breadcrumb d-flex flex-wrap align-items-center justify-content-center justify-content-md-start bg-transparent px-0 py-2 mb-0">'.$listitems.'</ul>';
    }

	public function page_heading($tag = 'h1') {
	    global $COURSE;
	    $heading = html_writer::tag($tag, $this->page->heading, array('class'=>'pageheading font-weight-normal mb-0'));
	    return $heading;
	}

	/**
	 * Construct a user menu, returning HTML that can be echoed out by a
	 * layout file.
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

	    $user = html_writer::link('javascript://void(0)', $avatarcontents, array('data-toggle'=>"dropdown", 'class'=>'usermenu_header d-flex flex-wrap align-items-center dropdown-toggle text-default'));

	    $content = html_writer::tag('div', $usertextcontents.$content, array('id'=>'usermenu_content', 'class'=>"usermenu_content m-0 pt-0 dropdown-menu dropdown-menu-right"));

	    return html_writer::div(
	        $user.$content,
	        $usermenuclasses.' dropdown ml-4'
	    );
	}

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
	 * City University help menu
	 */
	public function help_menu() {
	    global $CFG, $PAGE;

	    if (!empty($this->page->theme->settings->customhelpmenuitems)) {
	        $customhelpmenuitems = $this->page->theme->settings->customhelpmenuitems;
	        $helptxt = get_string('helptext', 'theme_cul_boost');
	        $helpmenu = new custom_menu($customhelpmenuitems);
	        return $helpmenu;
	    }

	    return false;
	}

	public function help() {
		global $CFG, $PAGE, $USER, $OUTPUT;

		$content = '';
		$showmenu = isloggedin() && !isguestuser();

		// Help & Support from CUL Theme Settings
		if ($showmenu) {
		    if ($helpmenu = $this->help_menu()) {
		        $content .= $this->render_custom_menu($helpmenu);
		    }
		}

		return $content;
	}

	public function help_mobile() {
		global $CFG, $PAGE, $USER, $OUTPUT;

		$content = '';
		$showmenu = isloggedin() && !isguestuser();

		// Help & Support from CUL Theme Settings
		if ($showmenu) {
		    if ($helpmenu = $this->help_menu()) {
		        $content .= $this->render_help_menu($helpmenu);
		    }
		}

		return $content;
	}

	/*
	 * This renders the bootstrap top menu.
	 *
	 * This renderer is needed to enable the Bootstrap style navigation.
	 */
	protected function render_custom_menu(custom_menu $menu) {
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
	        }
	        
	        $content .= $this->render_from_template('core/custom_menu_item', $context);
	    }

	    return $content;
	}

	public function render_help_menu(custom_menu $menu, $classes = 'nav d-flex flex-wrap align-items-stretch justify-content-center') {
	    global $COURSE, $PAGE, $CFG, $USER;

	    $content = '';
	    $content .= html_writer::start_tag('ul', array('class' => $classes, 'role' => 'menubar'));

	    foreach ($menu->get_children() as $item) {
	        $content .= $this->render_help_menu_item($item, 1);
	    }

	    $content .= html_writer::end_tag('ul');
	    return $content;
	}

	/**
	 * This code renders the custom menu items for the
	 * bootstrap dropdown menu.
	 */
	protected function render_help_menu_item(custom_menu_item $menunode, $level = 0 ) {
	    static $submenucount = 0;

	    $id = strtolower($menunode->get_title());
	    $id = str_replace(' ', '', $id);
	    $id = 'theme-cul_boost-' . $id;

	    if ($menunode->has_children()) {

	        if ($level == 1) {
	            $class = 'dropdown d-flex flex-wrap align-items-center justify-content-center py-2';
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
	            $content .= $this->render_help_menu_item($menunode, 0);
	        }

	        $content .= '</ul>';
	    } else {
	        
	        $class = 'dropdown-item d-flex flex-wrap align-items-center justify-content-center';

	        if (!$menunode->has_children() && $level == 1) {
	            $class = 'dropdown d-flex flex-wrap align-items-center justify-content-center py-2';
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
     * Prints a nice side block with an optional header.
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

	// Straight copy from the City University module menu with some visual differences
	public function favourite_course() {
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
			$coursecontext = \context_user::instance($COURSE->id);

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

		// @TODO template
		$favouritesrtxt = html_writer::tag('span', $favouritetxt, ['class' => 'accesshide']);
		
		$content = html_writer::link($favouriteurl, $favouritesrtxt, ['class'=>'text-white '.$class, 'data-toggle'=>'popover', 'data-content'=>$favouritetxt, 'data-placement'=>'left', 'data-trigger'=>'hover']);

		$content = html_writer::tag('div', $content, ['id'=>$id, 'class'=>'favourite-btn fixed-btn d-flex flex-wrap align-items-center justify-content-center bg-dark h4 m-0 text-white']);

		return $content;
	}

	/**
     * Returns a link to make a hidden course visible.
     *
     * @return string the HTML to be output.
     */
	public function show_course() {

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

        $attributes = array('src'=>$src, 'alt'=>$alt, 'class'=>$class, 'width'=>$size, 'height'=>$size);
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

    public function gradebook_disclaimer() {
	    $gradebookids = array (
	        'page-grade-report-user-index',
	        'page-grade-report-culuser-index',
	        'page-grade-report-overview-index',
	        'page-course-user'
	    );

	    $content = '';

	    if (in_array($this->page->bodyid, $gradebookids)) {
	        $disclaimer = html_writer::tag('p', get_string('gradebookdisclaimer', 'theme_cul_boost'));
	        $content = html_writer::tag('div', $disclaimer,
	            array('class' => 'alert alert-warning', 'role' => 'note'));
	    }

	    return $content;
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
}