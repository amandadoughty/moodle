<?php

echo html_writer::start_tag('nav', ['id'=>'top', 'role'=>'header', 'class'=>'navbar navbar-default d-block']);

    echo html_writer::start_tag('div', ['class'=>'topmenu d-none d-md-block']);
        echo html_writer::start_tag('div', ['class'=>'container-fluid']);
            echo $cityrenderer->city_global_navigation();
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class'=>'main-navigation']);
        echo html_writer::start_tag('div', ['class'=>'container-fluid d-lg-flex flex-wrap align-items-center position-relative py-3 py-md-4']);

            echo html_writer::start_tag('div', ['class'=>'right-float-menu d-none d-lg-flex flex-wrap mx-auto mb-3']);
                echo $OUTPUT->help();
            echo html_writer::end_tag('div');

            echo html_writer::start_tag('div', ['class'=>'d-flex flex-wrap align-items-center w-100']);

                $logo = html_writer::tag('div', $cityrenderer->get_logo(), ['class'=>'navbar-brand']);
                echo html_writer::tag('div', $logo, ['class'=>'navbar-header col py-2 pl-0']);

                if (isloggedin()) {
                    echo html_writer::start_tag('div', ['class'=>'nav-outer-wrap ml-auto']);
                        echo html_writer::start_tag('div', ['class'=>'nav-wrap d-flex flex-wrap align-items-stretch']);
                            
                            echo $cityrenderer->get_white_logo();

                            echo html_writer::start_tag('div', ['class'=>'nav-inner d-none d-lg-flex flex-wrap align-items-stretch ml-lg-auto']);
                                echo $citycore->custom_menu();
                            echo html_writer::end_tag('div');

                            $search = $OUTPUT->global_search();
                            $extras = html_writer::tag('div', $search, ['class'=>'global_search d-none d-sm-flex flex-wrap justify-content-end p-0 ml-4']);
                            $extras .= $OUTPUT->user_menu();

                            $toggle = html_writer::tag('span', '', array('class'=>'icon-bar'));
                            $extras .= html_writer::tag('div', $toggle, array('class'=>'navbar-toggle d-lg-none ml-3'));

                            echo html_writer::tag('div', $extras, ['class'=>'nav-extras d-flex flex-wrap align-items-stretch ml-auto ml-lg-0']);

                        echo html_writer::end_tag('div');
                    echo html_writer::end_tag('div');
                }

            echo html_writer::end_tag('div');

        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

echo html_writer::end_tag('nav');

require_once(dirname(__FILE__).'/drawer.php');