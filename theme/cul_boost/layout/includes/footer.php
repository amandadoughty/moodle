<footer id="page-footer" class="footer mt-5 w-100 bg-primary text-white">
    <div class="container-fluid">
        <div class="footerlinks d-flex flex-wrap py-5">
            <?php

            // Footnote
            if ($footnote) {
                echo '<div class="footnote col-12 col-md-6 p-0">';
                    echo $footnote;
                echo '</div>';
            }

            $imageurl = $OUTPUT->image_url('footerlogo', 'theme');
            echo html_writer::empty_tag('img', ['class'=>'footerlogo ml-auto', 'src'=>$imageurl]);

            ?>
        </div>
    </div>

    <div class="footer-bottom py-3">
    	<div class="container-fluid d-flex flex-wrap align-items-between">
	    	<?php 

	    	// Copyright
	    	if ($copyright) {
	    	    echo html_writer::tag('div', '&copy; '.date("Y").' '.$copyright, ['class'=>'copyright']);
	    	}

            $icon = html_writer::tag('i', '', ['class'=>'fa fa-angle-up ml-2']);
	    	echo html_writer::tag('div', get_string('scrolltop', 'theme_cul_boost').$icon, ['class'=>'scrolltop mx-auto']);

	    	// Copyright
	    	if ($footerlinks) {
	    	    echo html_writer::tag('div', $footerlinks, ['class'=>'footerlinks']);
	    	}

	    	?>
    	</div>
    </div>
</footer>