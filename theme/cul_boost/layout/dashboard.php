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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html(); ?>

<?php require_once(dirname(__FILE__).'/includes/header.php'); ?>

<?php 
    // if($PAGE->theme->settings->usefrontpageslider == 1) {
    //     require_once(dirname(__FILE__).'/includes/imageslider.php');
    // }  

    // require_once(dirname(__FILE__).'/includes/findamodule.php');

    echo html_writer::start_tag('div', ['class'=>'dashboard-navbar-wrap container-fluid']);
        require_once(dirname(__FILE__).'/includes/navbar.php');
    echo html_writer::end_tag('div');

    // require_once(dirname(__FILE__).'/includes/recentcourses.php');

    // if ($hasdashfull) {
    //     echo $OUTPUT->synergyblocks($dashf, 'w-100 d-flex flex-wrap align-items-stretch mb-5');
    // }
?>

<div id="page">
    <div class="container-fluid">
        <div id="page-content" class="row justify-content-center">
            
            <section id="region-main" class="<?php echo $regions['content']; ?>">
                <?php echo $OUTPUT->main_content(); ?>
            </section>

            <?php 

                echo html_writer::start_tag('div', ['class'=>'dashboard-blocks-wrap w-100']);
                    // if ($hasdashcontent) {
                    //     echo $OUTPUT->synergyblocks($dashc, 'col-12 col-md-8 d-flex flex-wrap align-items-stretch mb-8');
                    // }
                    echo html_writer::start_tag('div', ['class'=>'row']);
                    if ($hasdashleft) {
                        echo $OUTPUT->synergyblocks($dashl, 'col-12 col-md-4 d-flex flex-wrap align-items-stretch mb-4');
                    }
                    if ($hasdashmiddle) {
                        echo $OUTPUT->synergyblocks($dashm, 'col-12 col-md-4 d-flex flex-wrap align-items-stretch mb-4');
                    }
                    if ($hasdashright) {
                        echo $OUTPUT->synergyblocks($dashr, 'col-12 col-md-4 d-flex flex-wrap align-items-stretch mb-4');
                    }
                    echo html_writer::end_tag('div');
                echo html_writer::end_tag('div');
            ?>
        </div>
    </div>
</div>

<?php require_once(dirname(__FILE__).'/includes/footer.php'); ?>

<?php echo $OUTPUT->standard_end_of_body_html() ?>

</body>
</html>