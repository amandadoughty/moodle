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
 * Forgot password routine.
 *
 * Finds the user and calls the appropriate routine for their authentication type.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
global $CFG;

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$PAGE->https_required();
$PAGE->set_url('/local/culpages/browser_detect.php');
$PAGE->set_title('Browser Info Page Title');
$PAGE->requires->js('/local/culpages/js/javascript_your_computer.js', true);
$PAGE->verify_https_required();

echo $OUTPUT->header();
echo get_string('heading', 'local_culpages');



$os = '';
$os_starter = '<h4 class="right-bar">Operating System:</h4><p class="right-bar">';
$os_finish = '</p>';
$full = '';
$handheld = '';
$tablet = '';
// Change this to match your include path/and file name you give the script.
require($CFG->dirroot . '/local/culpages/browser_detect_original.php');
$browser_info = browser_detection('full');

if ( $browser_info[8] == 'mobile' ) {
    $handheld = '<h4 class="right-bar">Handheld Device:</h4><p class="right-bar">';
    $handheld = '<h4 class="right-bar">Handheld Device:</h4><p class="right-bar">';
    if ( $browser_info[13][8] ) {
        if ( $browser_info[13][0] ) {
            $tablet = ' (tablet)';
        } else {
            $handheld .= ucwords($browser_info[13][8]) . ' Tablet</br>';
        }
    }
    if ( $browser_info[13][0] ) {
        $handheld .= 'Type: ' . ucwords( $browser_info[13][0] );
        if ( $browser_info[13][7] ) {
            $handheld = $handheld  . ' v: ' . $browser_info[13][7];
        }
        $handheld = $handheld  . $tablet . '<br />';
    }
    if ( $browser_info[13][3] ) {
        // Detection is actually for cpu os here, so need to make it show what is expected.
        if ( $browser_info[13][3] == 'cpu os' ) {
            $browser_info[13][3] = 'ipad os';
        }
        $handheld .= 'OS: ' . ucwords( $browser_info[13][3] ) . ' ' .  $browser_info[13][4] . '<br />';
        // Don't write out the OS part for regular detection if it's null.
        if ( !$browser_info[5] ) {
            $os_starter = '';
            $os_finish = '';
        }
    }
    // Let people know OS couldn't be figured out.
    if ( !$browser_info[5] && $os_starter ) {
        $os_starter .= 'OS: N/A';
    }
    if ( $browser_info[13][1] ) {
        $handheld .= 'Browser: ' . ucwords( $browser_info[13][1] ) . ' ' .  $browser_info[13][2] . '<br />';
    }
    if ( $browser_info[13][5] ) {
        $handheld .= 'Server: ' . ucwords( $browser_info[13][5] . ' ' .  $browser_info[13][6] ) . '<br />';
    }
    $handheld .= '</p>';
}

switch ($browser_info[5]) {
    case 'win':
        $os .= 'Windows ';
        break;
    case 'nt':
        $os .= 'Windows<br />NT ';
        break;
    case 'lin':
        $os .= 'Linux<br /> ';
        break;
    case 'mac':
        $os .= 'Mac ';
        break;
    case 'iphone':
        $os .= 'Mac ';
        break;
    case 'unix':
        $os .= 'Unix<br />Version: ';
        break;
    default:
        $os .= $browser_info[5];
}

if ( $browser_info[5] == 'nt' ) {
    if ( $browser_info[5] == 'nt' ) {
        switch ( $browser_info[6] ) {
            case '5.0':
                $os .= '5.0 (Windows 2000)';
                break;
            case '5.1':
                $os .= '5.1 (Windows XP)';
                break;
            case '5.2':
                $os .= '5.2 (Windows XP x64 Edition or Windows Server 2003)';
                break;
            case '6.0':
                $os .= '6.0 (Windows Vista)';
                break;
            case '6.1':
                $os .= '6.1 (Windows 7)';
                break;
            case '6.2':
                $os .= '6.2 (Windows 8)';
                break;
            case '6.3':
                $os .= '6.3 (Windows 8.1)';
                break;
            case 'ce':
                $os .= 'CE';
                break;
            // Note: browser detection 5.4.5 and later return always
            // the nt number in <number>.<number> format, so can use it
            // safely.
            default:
                if ( $browser_info[6] != '' ) {
                    $os .= $browser_info[6];
                } else {
                    $os .= '(version unknown)';
                }
                break;
        }
    }
} else if ( $browser_info[5] == 'iphone' ) {
    $os .= 'OS X (iPhone)';
} else if ( ( $browser_info[5] == 'mac' ) && ( strstr( $browser_info[6], '10' ) ) ) {
    // Note: browser detection now returns os x version number if available, 10 or 10.4.3 style.
    $os .= 'OS X v: ' . $browser_info[6];
} else if ( $browser_info[5] == 'lin' ) {
    $os .= ( $browser_info[6] != '' ) ? 'Distro: ' . ucwords($browser_info[6] ) : 'Smart Move!!!';
} else if ( $browser_info[5] && $browser_info[6] ) {
    // Default case for cases where version number exists.
    $os .= " " . ucwords( $browser_info[6] );
} else if ( $browser_info[5] && $browser_info[6] == '' ) {
    $os .= ' (version unknown)';
} else if ( $browser_info[5] ) {
    $os .= ucwords( $browser_info[5] );
}
$os = $os_starter . $os . $os_finish;
$full .= $handheld . $os . '<h4 class="right-bar">Current Browser / UA:</h4><p class="right-bar">';

switch ( $browser_info[0] ) {
    case 'moz':
        $a_temp = $browser_info[10];// Use the moz array.
        $full .= ($a_temp[0] != 'mozilla') ? 'Mozilla/ ' . ucwords($a_temp[0]) . ' ' : ucwords($a_temp[0]) . ' ';
        $full .= $a_temp[1] . '<br />';
        $full .= 'ProductSub: ';
        $full .= ( $a_temp[4] != '' ) ? $a_temp[4] : 'Not Available';
        break;
    case 'ns':
        $full .= 'Browser: Netscape<br />';
        $full .= 'Full Version Info: ' . $browser_info[1];
        break;
    case 'webkit':
        $a_temp = $browser_info[11];// Use the webkit array.
        $full .= 'User Agent: ';
        $full .= ucwords($a_temp[0]) . ' ' . $a_temp[1];
        break;
    case 'ie':
        $full .= 'User Agent: ';
        $full .= strtoupper($browser_info[7]);
        // Note: $browser_info[14] will only be set if $browser_info[1] is also set.
        if ( $browser_info[14] ) {
            if ( $browser_info[14] != $browser_info[1] ) {
                $full .= '<br />(compatibility mode)';
                $full .= '<br />Actual Version: ' . number_format( $browser_info[14], '1', '.', '' );
                $full .= '<br />Compatibility Version: ' . $browser_info[1];
            } else {
                if ( is_numeric($browser_info[1]) && $browser_info[1] < 11 ) {
                    $full .= '<br />(standard mode)';
                }
                $full .= '<br />Full Version Info: ' . $browser_info[1];
            }
        } else {
            $full .= '<br />Full Version Info: ';
            $full .= ( $browser_info[1] ) ? $browser_info[1] : 'Not Available';
        }
        break;
    default:
        $full .= 'User Agent: ';
        $full .= ucwords($browser_info[7]);
        $full .= '<br />Full Version Info: ';
        $full .= ( $browser_info[1] ) ? $browser_info[1] : 'Not Available';
        break;
}

if ( $browser_info[1] != $browser_info[9] ) {
    $full .= '<br />Main Version Number: ' . $browser_info[9];
}
if ( $browser_info[17][0] ) {
    $full .= '<br />Layout Engine: ' . ucfirst( ( $browser_info[17][0] ) );
    if ( $browser_info[17][1] ) {
        $full .= '<br />Engine Version: ' . ( $browser_info[17][1] );
    }
}


echo $full . '</p>';



?>

<script type="text/javascript">
    client_data('width');
    client_data('js');
    client_data('popup');
    client_data('adobe');
    client_data('plugins');
    client_data('java');

</script>
<noscript>
     <h4 class="right-bar">JavaScript is disabled!</h4>
</noscript>

<?php
    echo $OUTPUT->footer();
