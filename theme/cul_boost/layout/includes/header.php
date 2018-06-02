<?php

echo html_writer::start_tag('nav', ['id'=>'top', 'role'=>'header', 'class'=>'navbar navbar-default']);

    echo html_writer::start_tag('div', ['class'=>'topmenu']);
        echo html_writer::start_tag('div', ['class'=>'container-fluid']);
            echo $cityrenderer->city_global_navigation();
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', ['class'=>'main-navigation']);
        echo html_writer::start_tag('div', ['class'=>'container-fluid d-flex flex-wrap align-items-center position-relative py-4']);

            echo html_writer::start_tag('div', ['class'=>'right-float-menu d-flex flex-wrap']);
                echo $OUTPUT->help();
            echo html_writer::end_tag('div');

            $logo = html_writer::tag('div', $cityrenderer->get_logo(), ['class'=>'navbar-brand']);
            echo html_writer::tag('div', $logo, ['class'=>'navbar-header col py-2 pl-0']);

            if (isloggedin()) {
                echo html_writer::start_tag('div', ['class'=>'nav-outer-wrap ml-auto']);
                    echo html_writer::start_tag('div', ['class'=>'nav-wrap d-flex flex-wrap align-items-center']);
                        
                        echo $cityrenderer->get_white_logo();

                        echo html_writer::start_tag('div', ['class'=>'nav-inner d-flex flex-wrap align-items-center py-2 ml-auto']);
                            echo $citycore->custom_menu();
                            $search = $OUTPUT->global_search();
                            echo html_writer::tag('div', $search, ['class'=>'global_search d-flex flex-wrap justify-content-end p-0 ml-4']);
                            echo $OUTPUT->user_menu();
                        echo html_writer::end_tag('div');

                    echo html_writer::end_tag('div');
                echo html_writer::end_tag('div');
            }

        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

echo html_writer::end_tag('nav');