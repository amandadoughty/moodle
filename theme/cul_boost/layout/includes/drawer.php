<?php

echo html_writer::start_tag('div', array('class'=>'drawer-wrap bg-white'));
    echo html_writer::start_tag('div', array('class'=>'drawer d-flex flex-wrap justify-content-center pt-3'));

        echo html_writer::start_tag('div', array('class'=>'drawer-content d-flex flex-wrap flex-column justify-content-stretch w-100'));

            $toggle = html_writer::tag('span', '', array('class'=>'icon-bar'));
            echo html_writer::tag('div', $toggle, array('class'=>'navbar-toggle ml-auto mr-3'));

            echo html_writer::start_tag('div', array('class'=>'mainmenu-wrap d-flex flex-column col'));
                echo $citycore->custom_menu();
            	
            	$help = $OUTPUT->help_mobile();
            	echo html_writer::tag('div', $help, ['class'=>'help-menu-wrap bg-light mt-auto py-2 bg-light']);

            echo html_writer::end_tag('div');
            
        echo html_writer::end_tag('div');

    echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

?>