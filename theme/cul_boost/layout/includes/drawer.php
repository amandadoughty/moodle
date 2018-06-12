<?php

echo html_writer::start_tag('div', array('class'=>'drawer-wrap bg-white'));
    echo html_writer::start_tag('div', array('class'=>'drawer d-flex flex-wrap justify-content-center py-3'));

        echo html_writer::start_tag('div', array('class'=>'drawer-content w-100'));

            $toggle = html_writer::tag('span', '', array('class'=>'icon-bar'));
            echo html_writer::tag('div', $toggle, array('class'=>'navbar-toggle ml-auto mr-3'));

            echo html_writer::start_tag('div', array('class'=>'mainmenu-wrap'));
                echo $citycore->custom_menu();
            echo html_writer::end_tag('div');
            
        echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

?>