function client_data(info) {
	if (info == 'width') {
		width_height_html = '<h4  class="right-bar">Current Screen Resolution</h4>';
		width = (screen.width) ? screen.width : '';
		height = (screen.height) ? screen.height : '';
		// check for windows off standard dpi screen res
		if (typeof(screen.deviceXDPI) == 'number') {
			width *= screen.deviceXDPI / screen.logicalXDPI;
			height *= screen.deviceYDPI / screen.logicalYDPI;
		}
		width_height_html += '<p class="right-bar">' + width + " x " + height + " pixels</p>";
		(width && height) ? document.write(width_height_html) : '';
	}

	else if (info == 'js' ) {
		document.write('<h4 class="right-bar">JavaScript</h4>');
		document.write('<p class="right-bar">JavaScript is enabled </p>');
	} else if ( info == 'cookies' ) {
		expires = '';
		Set_Cookie( 'cookie_test', 'it_worked' , expires, '', '', '' );
		string = '<h4  class="right-bar">Cookies</h4><p class="right-bar">';
		if ( Get_Cookie( 'cookie_test' ) ) {
			string += 'Cookies are enabled</p>';
		} else {
			string += 'Cookies are disabled</p>';
		}
		document.write( string );
	} else if ( info == 'popup' ) {

		popupstring = '<h4  class="right-bar">Pop-ups</h4><p class="right-bar">';

		document.write( popupstring);

		var pop_block = false;
		var win = window.open("", "newWin", "location=no, top=360, right=360, height=5, width=5" );
		if (win == null) {
			pop_block = true;
		} else {
			win.close();
		}

		if (!pop_block) {
			document.write('<p class="right-bar">You are not running a popup blocker, or it is disabled, or you have allowed popups for this site</p>');

		} else {
			document.write('<p class="right-bar">You are running a popup blocker. Please disable it!</p>');
		}

	} else if (info == 'adobe') {

		var got_acrobat = false;
		document.write('<h4  class="right-bar">Adobe</h4>');
		if ( got_acrobat ) {
           document.write('<p class="right-bar">The pdf plug-in is installed</p>');
        } else {
            document.write('<p class="right-bar">The pdf plug-in is not installed, you can download Free Acrobat Reader <a target="_blank" href="http://www.adobe.com/products/acrobat/readstep2.html">here</a></p>');
        }

	} else if (info == 'plugins') {

		var len = navigator.plugins.length;
		var flash = navigator.plugins['Shockwave Flash'];

		document.write('<h4  class="right-bar">Flash</h4>');

		if (flash === undefined) {
		// flash is not present
		return undefined;
		document.write('<p class="right-bar">The Flash Player plug-in is not installed, you can download Free Flash Player <a target="_blank" href="https://get.adobe.com/flashplayer/">here</a></p>');
		}
		document.write('<p class="right-bar">You have Shockwave Flash installed, version :');
		document.write( flash.version.toString() );
		document.write('</p>');

    } else if (info == 'java') {

    	document.write('<h4  class="right-bar">Java</h4>');

		var got_java = navigator.javaEnabled();
		if ( got_java ) {
           document.write('<td>The java plug-in is installed</td></tr>');
        } else {
            document.write('<td>The java plug-in is not installed</td></tr>');
        }

}

}
