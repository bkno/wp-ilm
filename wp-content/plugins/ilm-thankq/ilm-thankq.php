<?php
    
/**
 * Plugin Name: ILM ThankQ integration
 * Plugin URI:  https://digitalgarden.co
 * Description: Integrates ThankQ CRM widgets into ILM's WordPress.
 * Author:      DigitalGarden
 * Author URI:  https://digitalgarden.co
 * Version:     1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/*
    ========== PUBLIC WIDGETS ==========
    
    EventBookingWizard
    Used to allow public users to book onto events (requires the "EventID" for the event to be provided)
    
    EventBookingWizardReview
    This will show the details of the last event booked onto, it is advised that this page is only accessible from the EventBookingWizard as it will not work without the new Booking ID. Required Parameter: NewBookingID - This will be set by the event booking widget
    
    EventList
    Lists events that have been published to web and where applicable leads onto the EventBookingWizard widget. The event must be at booking stage, marked as publish to web,  with a publish until date in the future and there must be an attendee type available with spaces.Required Parameters: NextURL - Used after going through the booking process to set the final URL, we advise you have the EventBookingWizardReview widget on this page.
    
    InvoiceAddress
    Used to set up the address to be used for invoices
    
    Login 
    Used to log a user in
    
    MembershipList
    Lists membership types that have been published to the web, and where applicable feeds into the MembershipSignupWizard
     
    MembershipSignupExternalWizard 
    Used to allow users to sign up for a membership, the membership scheme being signed up to should be provided and the method payment also specified.
    
    MembershipSignupExternalWizardReview
    This is used to display the membership details of the member that has just signed up. Required Parameters: NewMembershipID - Provided by the MembershipSignUpWizard.
    
    MembershipPayConfirmation
    Used when coming back from the membership pay widget.
    
    MembershipPayReview
    Used after a member has renewed their membership via the My Memberships widget. This will display details of the membership the user has renewed. Required Parameters: NewMembershipDetailID - Provided during the renewal process.
    
    Register
    Allows people to sign up for a user account on the site

    RegisterReview
    Displays details about the new registration, this should follow the Register widget, this will display details about the new contact
    
    ResetPasswordRequest
    Used to send a password reset email to a user who has forgotten their password (requires the parameter NextURL which points to the page hosting the ResetPasswordResponse widget)
    
    ResetPasswordResponse
    This widget reads the reset email parameters and sends a new email out to the user with a newly generated password.
    
    SmallLogin
    A smaller login widget designed to be secondary on a page so a user may always log in
    
    ========== LOGGED IN WIDGETS ==========

    ChangePassword
    This widget is used to change a web user’s password. They must provide their current password.

    LoggedInUser
    Displays the logged in user's name
    
    Mailings
    Lists mailing groups that the logged in user belongs to
     
    MailingsEdit
    Allows the logged in user to add themselves to, or remove themselves from, a distribution group

    MyDetails     
    Displays the logged in user’s contact details
    
    MyDetailsEdit
    Allows the logged user to edit their contact details
    
    MyMemberships
    Displays currently held memberships and previously held memberships relating to the logged in user. If appropriate it allows the user to pay for their next instalment by credit card
    
    MyEventBookings
    Displays events booked onto, both current and past.
*/

#$ilm_widget_url = 'https://thankqlegacymanagementtrain.accessacloud.com'; # STAGING
$ilm_widget_url = 'https://thankqlegacymanagement.accessacloud.com'; # PRODUCTION
$ilm_widget_id = 'c419dd23-d0b1-43bf-bc4f-867daf861e09';

// Include widget JS
function ilm_widget_enqueue() {
    global $ilm_widget_url;
    wp_enqueue_script('thankq_widget', $ilm_widget_url.'/widget/widgetInit.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'ilm_widget_enqueue');

// Initialise widget JS
function ilm_widget_js() {
    global $ilm_widget_id;
    echo '<script>thankQPortalInit("'.$ilm_widget_id.'");</script>';
    /*echo '<!--'.var_dump(do_shortcode('[user field=all]')).'-->';*/
}
add_action('wp_footer', 'ilm_widget_js', 100);

// Widget logout JS
function ilm_logout_js() {
    global $post;
    if ($post and $post->post_name == 'sign-out') {
        echo "<script>thankQLogout('/')</script>";
		wp_logout();
    }
}
add_action('wp_footer', 'ilm_logout_js', 101);

function ilm_add_query_vars( $vars ){
  $vars[] = "destination";
  return $vars;
}
add_filter( 'query_vars', 'ilm_add_query_vars' );

// Multi-purpse widget shortcode
// Example: [ILM widget=Login nexturl=account]
function ilm_widget_shortcode($atts, $content = null) {
    $a = shortcode_atts([
        'widget' => '',
        'class' => '',
        'height' => 'auto',
        'nexturl' => '', # TODO allow for auto from URL param
        'typeid' => '',
        'paymenttype' => '',
        'eventid' => '',
        'bandid' => '',
        'costid' => '',
        'restrict' => '', # this is our custom way of restricting a widget (values: user or member)
    ], $atts);
    
    if ($a['widget'] != '') {
        $widget = " data-thankq='{$a['widget']}'";
        $height = " data-thankq-height='{$a['height']}'";
        $nexturl = $a['nexturl'];
        $typeid = $a['typeid'];
        $paymenttype = $a['paymenttype'];
        $eventid = $a['eventid'];
        $bandid = $a['bandid'];
        $costid = $a['costid'];
        if ($a['nexturl'] != '') {
            $destination = get_query_var('destination', false);
            $destination = str_replace( 'https://legacymanagement.org.uk/', '/', $destination );
            if ($destination) {
                $a['nexturl'] = trim($destination, '/').'?t='.time();
            }
            $baseUrl = get_site_url();
            $nexturl = " data-thankq-nexturl='{$baseUrl}/{$a['nexturl']}/'";
        }
        if ($a['typeid'] != '') {
            $typeid = " data-thankq-typeid='".$a['typeid']."'";
        }
        if ($a['paymenttype'] != '') {
            $paymenttype = " data-thankq-paymenttype='".$a['paymenttype']."'";
        }
        if ($a['eventid'] != '') {
            $typeid = " data-thankq-eventid='".$a['eventid']."'";
        }
        if ($a['bandid'] != '') {
            $bandid = " data-thankq-bandid='".$a['bandid']."'";
        }
        if ($a['costid'] != '') {
            $costid = " data-thankq-costid='".$a['costid']."'";
        }
    }
    $class = 'widget';
    if ($a['class'] != '') {
        $class .= ' '.$a['class'];
    }
    
    ob_start();
    if ($a['restrict'] == 'member' && !ilm_signed_in_member()) {
        echo '<div class="'.$class.'">';
        get_template_part( 'partials/prompt-members-only');
        echo '</div>';
    } else if ($a['restrict'] == 'user' && !ilm_signed_in()) {
        echo '<div class="'.$class.'">';
        get_template_part( 'partials/prompt-user-only');
        echo '</div>';
    } else if ($a['widget'] != '') {
        $class .= ' '.strtolower($a['widget']). ' WidgetLoading';
        echo "<div class='{$class}'{$widget}{$height}{$nexturl}{$typeid}{$paymenttype}{$eventid}{$bandid}{$costid}></div>";
    } else if (!is_null($content)) {
        echo '<div class="'.$class.'">'.$content.'</div>';
    }
    return ob_get_clean();
}
add_shortcode('ILM', 'ilm_widget_shortcode');

function ilm_signed_in() {
    if (isset($_COOKIE['tqCombinedLogin']) && isset($_COOKIE['thankQWidgetCookie'])) {
    
        // Hide admin bar for non-admin/non-editor
        if ( !current_user_can('editor') && !current_user_can('administrator') ) {
            add_filter('show_admin_bar', '__return_false');
        }
        
        // If user is logged into widgets, but not WP, sign them in programatically
        if ( !is_user_logged_in() ) {
            $crmUser = [
                'email' => do_shortcode('[user field=email]'),
                'firstname' => do_shortcode('[user field=firstname]'),
                'lastname' => do_shortcode('[user field=lastname]'),
                //'crm_id' => do_shortcode('[user field=serial]'),
            ];
            $crmUser['username'] = $crmUser['firstname'].$crmUser['lastname'];
            $crmUser['display_name'] = $crmUser['firstname'].' '.$crmUser['lastname'];
        
            if ( $userId = email_exists( $crmUser['email']) ) {
                // If email address already linked to a WP account, log them in
                $userData = get_userdata( $userId );
                programmatic_login( $userData->user_login );
            } else if ( $userId = username_exists( $crmUser['email']) ) {
                // If email address already linked to a WP account, log them in
                $userData = get_userdata( $userId );
                programmatic_login( $userData->user_login );
            } else {
                // If email not in WP, create new user then log them in
                $password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                $userId = wp_create_user( $crmUser['email'], $password, $crmUser['email'] );
                $userData = get_userdata( $userId );
                // Set display name, first name, last name
                wp_update_user( [
                    'ID' => $userId,
                    'first_name' => $crmUser['firstname'],
                    'last_name' => $crmUser['lastname'],
                    'display_name' => $crmUser['display_name'],
                ] );
                programmatic_login( $userData->user_login );
            }
        }
        return true;
    }
    return false;
}

function ilm_signed_in_member() {
	#return true;
    if (isset($_COOKIE['tqCombinedLogin']) && isset($_COOKIE['thankQWidgetCookie'])) {
        if (do_shortcode('[user field=active]') == 'Active') { /*  || do_shortcode('[user field=active]') == 'Lapsed' */
            return true;
        } else {
            return false;
        }
    }
    return false;
}

add_action('template_redirect', 'ilm_dashboard_redirect');
function ilm_dashboard_redirect() {
    if (strpos($_SERVER['REQUEST_URI'], '/register/') === 0) {
        if (ilm_signed_in_member() || ilm_signed_in() || current_user_can('editor') || current_user_can('administrator')) {
            wp_redirect('/dashboard/', 302);
            exit;
        }
    }
    if (strpos($_SERVER['REQUEST_URI'], '/dashboard/') === 0) {
        if ( ilm_signed_in_member() || current_user_can('editor') || current_user_can('administrator')) {
            wp_redirect('/members/', 302);
            exit;
        } else if ( ilm_signed_in() || current_user_can('editor') || current_user_can('administrator')) {
            wp_redirect('/account/', 302);
            exit;
        } else {
            wp_redirect('/access-denied?destination=' . urlencode( $_SERVER["REQUEST_URI"] ) . '&t='.time(), 302);
            exit;
        }
    }
}

add_action('template_redirect', 'ilm_errors');
function ilm_errors() {
    if ( !ilm_signed_in_member() and strpos($_SERVER['REQUEST_URI'], '/members/') === 0 ) {
        if (!current_user_can('editor') && !current_user_can('administrator')) {
            wp_redirect( '/access-denied?destination=' . urlencode( $_SERVER["REQUEST_URI"] ) . '&t='.time(), 302 );
            exit;
        }
    } else if ( !ilm_signed_in() and strpos($_SERVER['REQUEST_URI'], '/account/') === 0 ) {
        if (!current_user_can('editor') && !current_user_can('administrator')) {
            status_header(403);
            get_template_part('403');
            exit;
        }
    }
}

function ilm_user_shortcode($atts) {
    $a = shortcode_atts([
        'field' => 'all'
    ], $atts);
    if (isset($_COOKIE['tqCombinedLogin'])) {
        $cookie = explode('~', urldecode($_COOKIE['tqCombinedLogin']));
        //return var_dump($cookie); // DEBUG
        $additional = explode('|', $cookie[2]);
        $data = [
            'serial' => $cookie[0],
            'date' => $cookie[1],
            'firstname' => $additional[0],
            'lastname' => $additional[1],
            'email' => str_replace(' ', '+', $additional[2]),
            'hash' => $cookie[3]
        ];
        if ($additional[3])
            $data['id'] = $additional[3];
        if (isset($additional[4]))
            $data['product'] = $additional[4];
        if (isset($additional[5]))
            $data['active'] = $additional[5];
        if (isset($additional[6]))
            $data['join_date'] = $additional[6];
        if (isset($additional[7]))
            $data['renewal_date'] = $additional[7];
        if ($a['field'] == 'all') {
	        $debug = "<p>Logged into CRM:</p>";
            $debug .=  '<p>'.var_export($data, true).'</p>';
            if ( is_user_logged_in() ) {
	            $debug .= "<br><p>Logged into WordPress:</p>";
	            $userDebug = wp_get_current_user();
	            $debug .= '<p>'.var_export($userDebug, true).'</p>';
            } else {
	            $debug .= "<p>Logged out of WordPress.</p>";
            }
            $debug .= "<br><p>ilm_signed_in_member():</p>";
            $debug .= '<p>'.var_export(ilm_signed_in_member(), true).'</p>';
            return $debug;
        }
        if (isset($data)) {
            return $data[$a['field']];
        }
    } else if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        if ($a['field'] == 'firstname') {
            return $current_user->user_firstname;
        } elseif ($a['field'] == 'lastname') {
            return $current_user->user_lastname;                            
        }
    }
    return '';
}
add_shortcode('user', 'ilm_user_shortcode');

/*add_filter( 'gform_field_value', 'ilm_populate_gform_org_fields', 10, 3 );
function ilm_populate_gform_org_fields( $value, $field, $name ) {
 
    $values = array(
        'email' => do_shortcode('[user field=email]'),
        'firstname' => do_shortcode('[user field=firstname]'),
        'lastname' => do_shortcode('[user field=lastname]'),
        'crm_id' => do_shortcode('[user field=serial]'),
    );
 
    return isset( $values[ $name ] ) ? $values[ $name ] : $value;
}*/

/*function ilm_member_directory_rewrite() {
	add_rewrite_rule( '^members/directory/?name=([^/]*)$', 'index.php?name=$matches[1]', 'top' );
    add_rewrite_endpoint( 'name', EP_PERMALINK );
	add_rewrite_rule( '^members/directory/?organisation=([^/]*)$', 'index.php?organisation=$matches[1]', 'top' );
    add_rewrite_endpoint( 'organisation', EP_PERMALINK );
}
add_action('init', 'ilm_member_directory_rewrite');*/

function ilm_member_directory_query_vars($vars) {
	$vars[] = 'person';
	$vars[] = 'organisation';
	$vars[] = 'order_by';
	return $vars;
}
add_filter('query_vars', 'ilm_member_directory_query_vars');

/* Members directory shortcode */
function ilm_member_directory_shortcode($atts) {
    $a = shortcode_atts([
    ], $atts);
    
    global $wpdb;
    $query = "SELECT * FROM ilm_directory WHERE status = 'Active'";
    if ( get_query_var('person') ) {
        $query = $wpdb->prepare($query.' AND name LIKE %s', '%%'.get_query_var('person').'%%');
    }
    if ( get_query_var('organisation') ) {
        $query = $wpdb->prepare($query.' AND organisation LIKE %s', '%%'.get_query_var('organisation').'%%');
    }

    if ( get_query_var('order_by') === 'organisation' || !get_query_var('order_by') ) {
		$query .= " ORDER BY CASE WHEN organisation = '' THEN 1 ELSE 0 END, organisation ASC, TRIM(lastname) ASC";
	} else {
		$query .= " ORDER BY TRIM(lastname) ASC";
	}

    $results = $wpdb->get_results($query);
    
    $out = '<form id="directory-search" action="/members/directory/" method="GET">';
    $out .= '    <h3>Database search</h3>';
    $out .= '    <div class="directory-filter"><label>Name</label><input type="text" name="person" value="'.get_query_var('person').'"></div>';
    $out .= '    <div class="directory-filter"><label>Organisation</label><input type="text" name="organisation" value="'.get_query_var('organisation').'"></div>';
    $out .= '    <div class="directory-filter"><label>Order by</label><select name="order_by">';
    if ( get_query_var('order_by') == 'organisation' ) {
	    $out .= '        <option value="organisation" selected>Organisation name</option>';
	} else {
	    $out .= '        <option value="organisation">Organisation name</option>';
	}
    if ( get_query_var('order_by') == 'name' ) {
	    $out .= '        <option value="name" selected>Last name</option>';
	} else {
	    $out .= '        <option value="name">Last name</option>';
	}
    $out .= '    </select></div>';
    $out .= '    <div class="directory-action"><a class="et_pb_button alt" href="/members/directory/">Reset</a><button class="et_pb_button">Submit</button></div>';
    $out .= '</form>';
    
    foreach ($results as $key => $person) {
	    if ($person->email == 'ben@digitalgarden.co') {
		    unset($results[$key]);
	    }
	}
    
    
    $out .= '<div class="directory-count">Showing ' . count($results) . ' members</div>';
    
    foreach ($results as $person) {
        $out .= '<div class="directory-item">';
        $data = [];
        if ($person->name != '') {
            $data[] = '<!--Name: -->' . $person->firstname . ' ' . $person->lastname;
            if ($person->job != '' and $person->job != 'Other') {
                $data[] = '<!--Job title: -->' . $person->job;
            }
            if ($person->organisation != '' && $person->organisation != $person->name) {
                $data[] = '<!--Organisation: -->' . $person->organisation;
            }
            if ($person->email != '') {
                $data[] = '<!--Email: --><a href="mailto:' . $person->email . '">' . $person->email . '</a>';
            }
            if ($person->phone != '') {
                $data[] = '<!--Phone: -->' . trim($person->phone, "'");
            }
            if ($person->website != '') {
                $data[] = '<!--Website: --><a href="' . $person->website . '">' . $person->website . '</a>';
            }
        }
        $out .= implode('<br>', $data);
        $out .= '</div>';
    }
    
    return $out;
}
add_shortcode('member_directory', 'ilm_member_directory_shortcode');


add_action('wp_footer', 'ilm_footer');
function ilm_footer() {
    /* Hide user details in forum background for JS */
    if (strpos($_SERVER['REQUEST_URI'], '/members/forum/') === 0) {
        $out = '<div id="ilm-forum-user" style="display:none !important" data-name="'.do_shortcode('[user field=firstname]').' '.do_shortcode('[user field=lastname]').'" data-email="'.do_shortcode('[user field=email]').'"></div>';
        echo $out;
    }
    /* Determine eligible membership level */
    if (strpos($_SERVER['REQUEST_URI'], '/membership/individual/join/') === 0) {
        $out = '<script>';
        $previousForm = get_page_by_title(do_shortcode('[user field=serial]'), OBJECT, 'organisation_details');
        if (get_field('org_sector_years', $previousForm->ID)) {
            $sectorYears = get_field('org_sector_years', $previousForm->ID);
        } else {
            $sectorYears = -1;
        }
        $out .= 'var sectorYears = '.$sectorYears.';';
        $out .='</script>';
        echo $out;
    }
}


/**
 * Programmatically logs a user in
 * 
 * @param string $username
 * @return bool True if the login was successful; false if it wasn't
 */
function programmatic_login( $username ) {
	
	if ( is_user_logged_in() ) {
		wp_logout();
	}
	
	add_filter( 'authenticate', 'allow_programmatic_login', 10, 3 );	// hook in earlier than other callbacks to short-circuit them
	$user = wp_signon( array( 'user_login' => $username ) );
	remove_filter( 'authenticate', 'allow_programmatic_login', 10, 3 );
	
	if ( is_a( $user, 'WP_User' ) ) {
		wp_set_current_user( $user->ID, $user->user_login );
		
		if ( is_user_logged_in() ) {
			return true;
		}
	}

	return false;
}

/**
 * An 'authenticate' filter callback that authenticates the user using only the username.
 *
 * To avoid potential security vulnerabilities, this should only be used in the context of a programmatic login,
 * and unhooked immediately after it fires.
 * 
 * @param WP_User $user
 * @param string $username
 * @param string $password
 * @return bool|WP_User a WP_User object if the username matched an existing user, or false if it didn't
 */
function allow_programmatic_login( $user, $username, $password ) {
	return get_user_by( 'login', $username );
}


// Write to the error log nicely
if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( $log, true ) );
      } else {
         error_log( $log );
      }
   }
}


/* WooCommerce Member Only Coupons */

$member_coupons = array(
	'member20',
	'summer25',
	'newmem30',
);

function ilm_coupon_validation( $result, $coupon ) {
	global $member_coupons;
	if ( in_array( strtolower( $coupon->code ), $member_coupons ) ) {
		if ( !ilm_signed_in_member() ) {
			$result = false;
		}
	}
	return $result;
}
add_filter( 'woocommerce_coupon_is_valid', 'ilm_coupon_validation', 10, 2 );

function ilm_coupon_error_message( $err, $err_code, $coupon ) {
	global $member_coupons;
	if ( in_array( strtolower( $coupon->code ), $member_coupons ) && intval( $err_code ) === WC_COUPON::E_WC_COUPON_INVALID_FILTERED ) {
		return __( "Sorry, this coupon is valid only for ILM members" );
	}
	return $err;
}
add_filter( 'woocommerce_coupon_error', 'ilm_coupon_error_message', 10, 3 );

// WooCommerce - Redirect the default logout URL to our ThankQ one
/*function ilm_woo_my_account_logout( $wp ) {
	
    if ( is_user_logged_in() ) {
        if ( strpos( $wp->request, 'account/customer-logout' ) === 0 ) {
            wp_redirect( '/account/sign-out/' );
            exit();
        }
    }
}
add_action( 'parse_request', 'ilm_woo_my_account_logout' );*/