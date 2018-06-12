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
use coursecat;
use course_in_list;
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

	    $output = html_writer::start_tag('div', array('class' => 'slidersearchform d-flex flex-wrap align-items-center'));
	    $output .= html_writer::start_tag('form', array('action' => '' . $CFG->wwwroot . '/search/index.php', 'method' => 'get'));
	    $output .= html_writer::empty_tag('input', array(
	        'class' => 'w-100',
	        'type' => 'text',
	        'name' => 'q',
	        'alt' => get_string('searchfor','theme_cul_boost'),
	        'placeholder' => get_string('searchfor','theme_cul_boost')
	    ));
	    $output .= html_writer::tag('button', '<i class="fa fa-search"></i>', array(
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
        $homelink = html_writer::link(new moodle_url('/'), '<i class="fa fa-home"></i>', ['class'=>'d-flex align-items-center']);
        array_shift($breadcrumbs);
        array_unshift($breadcrumbs, $homelink);

        $listitems = '<li class="breadcrumb-item d-inline-flex flex-wrap align-items-center">' . join(' </li><li class="breadcrumb-item d-inline-flex flex-wrap align-items-center">', $breadcrumbs) . '</li>';
        $title = '<span class="accesshide">' . get_string('pagepath') . '</span>';
        return $title . '<ol class="breadcrumb d-flex flex-wrap align-items-center">'.$listitems.'</ul>';
    }

	public function page_heading($tag = 'h2') {
	    global $COURSE;
	    $heading = html_writer::tag($tag, $this->page->heading, array('class'=>'pageheading pull-left font-weight-light mb-4'));
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
	            $opts->metadata['rolename'],
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

	                $content .= html_writer::link($item->url, $icon.$title, array('class'=>'menu-link d-block dropdown-item px-2'));
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
		$showmuenu = isloggedin() && !isguestuser();

		// Help & Support from CUL Theme Settings
		if ($showmuenu) {
		    if ($helpmenu = $this->help_menu()) {
		        $content .= $this->render_custom_menu($helpmenu);
		    }
		}

		return $content;
	}

	public function recent_courses($courses) {
	    global $CFG, $DB, $USER;

	    require_once($CFG->dirroot.'/course/renderer.php');
	    include_once($CFG->dirroot.'/lib/coursecatlib.php');

	    if (!empty($USER->currentcourseaccess)) {
	    	$courses = $USER->currentcourseaccess;
	    } else if (!empty($USER->lastcourseaccess)) {
	    	$courses = $USER->lastcourseaccess;
	    } else {
	    	return '';
	    }


	    arsort($courses);

	    $content = '';

	    $i = 0;

	    $content .= html_writer::start_tag('div', ['class'=>'featured-courses col-12 col-lg-7']);
	    $content .= html_writer::start_tag('div', ['class'=>'row h-100']);

	   	foreach ($courses as $course => $date) {

	   		$i++;

	   		if ($i >= 6) {
	   			break;
	   		}

		    $lastcourse = $DB->get_record('course', array('id'=>$course));
		    
		    $course = new course_in_list($lastcourse);

		    $courseinfo = new stdClass();
		    $courseinfo->name = $course->fullname;

		    $category = coursecat::get($lastcourse->category);
		    $courseinfo->category = $category->name;
		    
		    $courselink = new moodle_url('/course/view.php', array('id'=>$course->id));
		    $courseinfo->url = $courselink->out();
		    
		    $shortsummary = $course->summary;
		    $courseinfo->summary = format_text((strlen($shortsummary) > 200) ? substr($shortsummary, 0, 200) . '&hellip;' : $shortsummary);
		    
		    $url = '';
		    foreach ($course->get_course_overviewfiles() as $file) {
		        $isimage = $file->is_valid_image();
		        $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
		                '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
		                $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
		    }

		    $courseinfo->hasimage = false;
		    
		    if (!empty($url)) {
		        $courseinfo->hasimage = true;
		    }

		    $courseinfo->image = $url;
		    $courseinfo->lastaccess = date('d/m/y', $date);

		    $courseinfo->featured = false;

		    if ($i < 3) {
		    	$courseinfo->featured = true;
		    }

		    if ($i == 3) {
		    	$content .= html_writer::end_tag('div');
		    	$content .= html_writer::end_tag('div');
		    	$content .= html_writer::start_tag('div', ['class'=>'recent-courses col-12 col-lg-5 py-3']);
		    	$content .= html_writer::start_tag('div', ['class'=>'recent-courses-inner']);
		    }

		    $content .= $this->render_from_template('theme_cul_boost/lastcourse', $courseinfo);

		}

		$content .= html_writer::end_tag('div');
		$content .= html_writer::end_tag('div');

		$content = html_writer::tag('div', $content, ['class'=>'row']);

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

		global $CFG, $PAGE, $COURSE;
		
		$content = '';

		// Add Favourite url
		$favourites = null;

		if (!is_null($favourites = get_user_preferences('culcourse_listing_course_favourites'))) {
		    $favourites = unserialize($favourites);
		}

		if ($favourites && in_array($COURSE->id, $favourites)) {
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
		
		$content = html_writer::link($favouriteurl, '', ['class'=>'text-white p-3 '.$class, 'data-toggle'=>'popover', 'data-content'=>$favouritetxt, 'data-placement'=>'left', 'data-trigger'=>'hover']);

		$content = html_writer::tag('div', $content, ['id'=>$id, 'class'=>'favourite-btn fixed-btn d-flex flex-wrap align-items-center justify-content-center bg-dark h4 m-0 text-white']);

		return $content;
	} 

}