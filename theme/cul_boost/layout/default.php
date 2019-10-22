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

/**
 * A two column layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/setup.php');

$PAGE->set_popup_notification_allowed(false);
echo $OUTPUT->doctype() ?>

<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <?php 
    // Accessibility stuff.
    $PAGE->requires->skip_link_to('accessibility', get_string('toaccessibility', 'theme_cul_boost'));
    ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php require_once(dirname(__FILE__).'/includes/header.php'); ?>

<div id="page" class="position-relative">

    <?php
        if ($PAGE->pagelayout == 'course' && $COURSE->id != 1) {

                $blocks = '';
                if ($PAGE->blocks->region_has_content('side-post', $OUTPUT)) {
                    $left = html_writer::tag('i', '', ['class'=>'fa fa-angle-double-left', 'data-toggle'=>'popover', 'data-content'=>get_string('showblocks', 'theme_cul_boost'), 'data-placement'=>'left', 'data-trigger'=>'hover']);
                    $right = html_writer::tag('i', '', ['class'=>'fa fa-angle-double-right', 'data-toggle'=>'popover', 'data-content'=>get_string('hideblocks', 'theme_cul_boost'), 'data-placement'=>'left', 'data-trigger'=>'hover']);
                    $blocks = html_writer::tag('div', $left.$right, ['class'=>'toggleblocks-btn fixed-btn d-none d-xl-flex flex-wrap align-items-center justify-content-center bg-dark h3 m-0 text-white']);
                }
                
                $fixed = $OUTPUT->favourite_course();
                echo html_writer::tag('div', '', ['class'=>'fixed-anchor']);
                echo html_writer::tag('div', $blocks.$fixed, ['class'=>'fixed-buttons']);
        }
    ?>

    <?php

    require_once(dirname(__FILE__).'/includes/navbar_orig.php');

    echo $html->gradebookdisclaimer;

    if ($PAGE->pagelayout == 'course' && $COURSE->visible == 0) {
        $showcoursebtn = $OUTPUT->show_course();
        echo html_writer::tag('h2', get_string('coursehidden', 'theme_cul_boost') . $showcoursebtn, ['class'=>'module-hidden p-3 bg-light mb-4 text-center']);
        
    }

    require_once(dirname(__FILE__).'/includes/courseheader_orig.php');

    ?>

    <div class="container-fluid">        
        <div id="page-content" class="row justify-content-center">            
            <section id="region-main" class="<?php echo $regions['content']; ?>">
                <?php 
                if ($PAGE->pagelayout == 'mypublic') {
                    echo $OUTPUT->context_header(); 
                }
                ?>
                <?php
                    echo $OUTPUT->course_content_header();
                    echo $OUTPUT->main_content();
                    echo $OUTPUT->activity_navigation();
                    echo $OUTPUT->course_content_footer();
                ?>
            </section>

            <?php
                if ($knownregionpost) {
                    echo $OUTPUT->blocks('side-post', $regions['post']);
                }
            ?>
        </div>
    </div>
</div>

<?php echo $OUTPUT->standard_after_main_region_html() ?>

<?php require_once(dirname(__FILE__).'/includes/footer.php'); ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>

</body>
</html>