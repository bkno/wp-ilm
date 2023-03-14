<?php

$version = '2022-10-04-002';

/* Account area sidebar */
register_sidebar([
	'id'          => 'account-sidebar',
	'name'        => 'Account Sidebar',
	'description' => 'The sidebar displayed on My Account area.',
	'before_widget' => '',
	'after_widget' => ''
]);

/* Site CSS */
add_action( 'wp_enqueue_scripts', 'ilm_site_styles' );
function ilm_site_styles() {
    global $version;
    wp_enqueue_style( 'fonts', 'https://fonts.googleapis.com/css?family=Montserrat:400,500,700&subset=latin' );
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css', [ 'fonts' ], $version );
    wp_enqueue_style( 'site-css', get_stylesheet_directory_uri() . '/css/site.css', [ 'fonts', 'parent-style' ], $version );
}

/* Site JS */
add_action( 'wp_enqueue_scripts', 'ilm_site_scripts' );
function ilm_site_scripts() {
    global $version;
    wp_enqueue_script( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.7.1/slick.min.js', [ 'jquery' ], $version);
    wp_enqueue_script( 'match-height', get_stylesheet_directory_uri() . '/js/jquery.matchHeight-min.js', [ 'jquery' ], $version);
    wp_enqueue_script( 'site-js', get_stylesheet_directory_uri() . '/js/site.js', [ 'jquery' ], $version);
}

/* Admin CSS */
add_action( 'admin_enqueue_scripts', 'ilm_admin_styles' );
function ilm_admin_styles() {
    global $version;
    wp_enqueue_style( 'admin-css', get_stylesheet_directory_uri() . '/css/admin.css', [], $version );
}

/* Admin JS */
add_action('admin_enqueue_scripts', 'ilm_admin_scripts');
function ilm_admin_scripts() {
    global $version;
    wp_enqueue_script( 'admin-js', get_stylesheet_directory_uri() . '/js/admin.js', [ 'jquery' ], $version);
}

/* Custom body class */
add_filter( 'body_class', 'ilm_body_classes' );
function ilm_body_classes( $classes ) {
    $classes[] = 'ilm';
    
	if ( is_user_logged_in() ) {
	    $user = wp_get_current_user();
		$classes[] = 'uid-' . $user->ID;
	    foreach ( $user->roles as $role ) {
			$classes[] = 'role-' . $role;
		}
	}
	
    if (ilm_signed_in()) {
        $classes[] = 'ilm-signed-in';
    } else {
        $classes[] = 'ilm-signed-out';
    }
	
    if ( ilm_signed_in_member() ) {
        $classes[] = 'ilm-member-signed-in';
    }
    
    if (strpos($_SERVER['REQUEST_URI'], '/account/') === 0) {
        $classes[] = 'account';
    }
    
	return $classes;
}

/* Remove ET TinyMCE additions - not working? */
add_action('admin_init', 'ilm_tinymce');
function ilm_tinymce() {
    remove_filter('mce_buttons', 'et_filter_mce_button');
    remove_filter('mce_external_plugins', 'et_filter_mce_plugin');
}

function ilm_pagination() {
    the_posts_pagination(['mid_size' => 4, 'prev_text' => '‹ Prev', 'next_text' => 'Next ›']);
}

/* Exclude members-only posts from news if someone is not a member */
add_action('pre_get_posts', 'ilm_exclude_members_only_category');
function ilm_exclude_members_only_category( $query ) {
    if ( strpos($_SERVER['REQUEST_URI'], '/news/') === 0 ) { // && ilm_signed_in()
        $query->set( 'cat', '-107' ); # Exclude "Members only" (members) category.
    }
}

add_action('template_redirect', 'ilm_members_only');
function ilm_members_only() {
    global $post;
    if ($post and (!ilm_signed_in_member() && has_category(107, $post->ID))) {
        if (!current_user_can('editor') && !current_user_can('administrator')) {
            wp_redirect( '/access-denied?destination=' . urlencode( $_SERVER["REQUEST_URI"] ) . '&='.time(), 302 );
            exit;
        }
    }
}

/* Add link to members area in WP admin menu */
function ilm_add_custom_admin_menu_page_url(){
    add_menu_page( "Members' Area", "Members' Area", 'manage_options', 'members-area', 'ilm_custom_menu_item_redirect_home_url', 'dashicons-screenoptions', 3 );
}
add_action( 'admin_menu', 'ilm_add_custom_admin_menu_page_url' );

function ilm_custom_menu_item_redirect_url() {
    $menu_redirect = isset($_GET['page']) ? $_GET['page'] : false;
    if ($menu_redirect == 'members-area' ) {
        wp_safe_redirect( '/members/' );
        exit();
    }
}
add_action( 'admin_init', 'ilm_custom_menu_item_redirect_url', 1 );

/* Divi Shop - display 'Add to basket' button */
add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 20 );

/* Divi Shop - display 'Short description' paragraph */
function woocommerce_after_shop_loop_item_title_short_description() {
	global $product;
	if ( ! $product->get_short_description() ) return; ?>
	<div class="product-description" itemprop="description">
	   <?php echo $product->get_short_description() ?>
	</div>
	<?php
}
add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_after_shop_loop_item_title_short_description', 5);

/* Divi Shop - remove product link */
remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

/* WooCommerce - remove 'related products' output */
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

/* WooCommerce - Remove the breadcrumbs */
add_action( 'init', 'woo_remove_wc_breadcrumbs' );
function woo_remove_wc_breadcrumbs() {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
}

/* WooCommerce - Account Funds */
function ilm_account_funds_content() {
	echo "<p>Whilst not required, you have the option of paying pay into your 'Learning Account' to use against future training purchases. This can be useful for large departments to pay for their training in one go.</p><p>You can top up your Learning Account through Credit Card or BACS, first enter the amount above to proceed.</p><p>The balance will be carried across from year to year.</p><p>In addition, organisations will be able to raise a single PO at the beginning of the year, and invoice against it. If you would like to discuss payment option further, please contact Tracey on <a href='mailto:support@legacymanagement.org.uk'>support@legacymanagement.org.uk</p>";
}
add_action( 'woocommerce_account_funds_content', 'ilm_account_funds_content' );

/* Output PDF of materials. Implements learndash-course-certificate-link-after action to show in addition to the certificate link. */
add_action( 'learndash-course-certificate-link-after', function( $course_id, $user_id ) {
	if ( learndash_course_completed ( $user_id, $course_id ) && get_field('course_pdf', $course_id) ) {
		echo '<div class="ld-alert ld-alert-success ld-alert-certificate"><div class="ld-alert-content"><div class="ld-alert-icon ld-icon ld-icon-content"></div><div class="ld-alert-messages">Now you have completed the course, a PDF copy of the materials is now available.</div></div><a class="ld-button" href="' . get_field('course_pdf') . '?uid=' . $user_id . '" download><span class="ld-icon ld-icon-download"></span>Download PDF</a></div>';
	}
}, 10, 2 );

/* LearnDash - assignment instructions */
add_action(
	'learndash-content-tabs-content-before',
	function( $post_id, $course_id, $user_id ) {
		if ( strpos( get_the_title( $post_id ), 'Written Assignment' ) !== false ) :
            echo '<p>A short written assignment to check that you have absorbed and understood the course contents and are able to apply them in practice.</p>';
            echo '<p>Answers should be 300 to 500 words long and written in full sentences. Once complete, your assignment can be uploaded in PDF or Word formats. You will receive feedback on your assignment within three days, and if successful you will be able to download and print your certificate.</p>';
            echo '<br><h3>Question:</h3>';
        endif;
	},
	10,
	3
);

/* LearnDash - change login URL to our front-end one. */
add_filter( 'learndash_login_url',
	function( $url = '' ) {
		$url = str_replace( '/cp/', '/login/', $url );
		$url = str_replace( 'redirect_to=', 'destination=', $url );
		$url = str_replace( 'https://legacymanagement.org.uk/', '/', $url );
		
		return $url;
	},
	30,
	1
);

 

/* LearnDash - prevent direct access to the lesson page if it is not an assignment */
add_action( 'template_redirect', function() {
	if ( is_user_logged_in() ) {
		$q_object = get_queried_object();
		if ( ( $q_object ) && ( is_a( $q_object, 'WP_Post' ) ) && ( $q_object->post_type == 'sfwd-lessons' ) && ( strpos( $q_object->post_title, 'Written Assignment' ) === false ) ) {
			$course_id = learndash_get_course_id();
			#$course_url = learndash_get_course_url( $course_id );
			wp_redirect( get_permalink( $course_id ) );
			die();
		}
	}
	
}, 1 );

/* LearnDash - remove the lesson link from breadcrumbs when on a topic page */
add_filter(
	'learndash_breadcrumbs',
	function( $breadcrumbs ) {
		if ( count( $breadcrumbs ) == 3 ) {
			unset( $breadcrumbs['lesson'] );
		}
	    return $breadcrumbs;
	}
);

/* LearnDash - reword the 'Back to Lesson' link to 'Back to Course', as we are redirecting the lesson page to the course page */
add_filter(
    'learndash_get_label_course_step_back',
    function( $step_label, $step_post_type, $plural ) {
		if ( $step_post_type == 'sfwd-lessons' ) {
			$step_label = 'Back to Course';
		}
        return $step_label;
    },
    10,
    3
);

/* Jetpack - copy post support */
function _jetpack_copy_post( $post_types ) {
    $post_types[] = 'event';
    return $post_types;
}
add_filter( 'jetpack_copy_post_post_types', '_jetpack_copy_post' );

/* Set email from address */
/*function ilm_email_from( $from_email ) {
    return 'support@legacymanagement.org.uk';
}
add_filter( 'wp_mail_from', 'ilm_email_from' );*/
 
/* Set email from name */
/*function ilm_email_from_name( $from_name ) {
    return 'Institute of Legacy Management';
}
add_filter( 'wp_mail_from_name', 'ilm_email_from_name' );*/

// Add used coupons to PDFs
add_action( 'wpo_wcpdf_after_order_details', '_wcpdf_coupons_used', 9, 2 );
function _wcpdf_coupons_used ( $template_type, $order ) {
	if ( $used_coupons = $order->get_used_coupons() ) {
		$coupons_list = implode( ', ', $used_coupons );
		echo '<p><strong>' . __('Coupons used') . '</strong></p>';
		echo '<p>' . $coupons_list . '</p>';
		echo '<br><br><br><br>';
	}
}

// Display user id on invoice
add_action( 'wpo_wcpdf_after_order_data', 'wpo_wcpdf_delivery_date', 10, 2 );
function wpo_wcpdf_delivery_date ( $template_type, $order ) {
    $document = wcpdf_get_document( $template_type, $order );
    if ( $template_type == 'invoice' ) {
        ?>
        <tr class="user-id">
            <th>Account ID:</th>
            <td><?php echo do_shortcode( '[user_id]' ); ?></td>
        </tr>
        <?php
    }
}

// Define the member discount for courses
$course_member_discount = 20;

// Display the course price fromm the related course
function lwp_learndash_get_course_price( $course_price ) {
	
	global $course_member_discount;
	
	echo '
	<style>
		.ld-course-status-price { width: 100% !important; max-width: 240px; }
		.lwp-price { display: flex; width: 100%; display: block; }
		.lpw-member-price, .lpw-nonmember-price, .lpw-price-divider { display: inline-block; padding: 0 10px; }
		.lpw-member-price { border-right: 0px solid #bbb; }
		.lwp-price-label { display: block; font-size: 12px; }
	</style>';
	
	// get the course object
	global $post;
	
	// Find products that are related to this course
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => -1,
	    'meta_query' => array(
	        array(
	        	'key' => '_related_course',
	            'value' => 'i:' . $post->ID . ';',
	            'compare' => 'LIKE',
	        )
	    ),
	);
	
	// The course could be related to multiple products, we need to check which one is ONLY related to the course
	$loop = new WP_Query( $args );
	if ( $loop->have_posts() ) {
		while ( $loop->have_posts() ) : $loop->the_post();
		
			$related_courses = get_metadata( 'post', $post->ID, '_related_course' );
			
			// If the product has just one related course, this is the price we want.
			if ( count( $related_courses ) === 1 ) {
				
				$product = wc_get_product( $post->ID );
				$course_price['price'] = $product->get_regular_price();
			}
		
		endwhile;
	} else {
		return $course_price;
	}
	wp_reset_postdata();
	
	// Add currency prefix and related CSS class to price
	$currency_symbol = get_woocommerce_currency_symbol();
	
	// Add our custom price here (can lookup with WooCommerce, etc.)
	$course_price['price'] = '<div class="lpw-member-price">' . $currency_symbol . $course_price['price'] . '<div class="lwp-price-label lwp-price-label-nonmember">Non-members</div>' . '</div>';
	
	// Check if user is a member
	if ( true || ilm_signed_in_member() ) {
		$member_price = $product->get_regular_price() - $course_member_discount;
		$course_price['price'] .= '<div class="lpw-nonmember-price">' . $currency_symbol . $member_price . '<div class="lwp-price-label lwp-price-label-nonmember">Members</div>' . '</div>';
	}
	
	$course_price['price'] = '<div class="lwp-price">' . $course_price['price'] . '</div>';
	
	return $course_price;
}
add_filter( 'learndash_get_course_price', 'lwp_learndash_get_course_price', 10, 1 );


function ldp_member_only_pricing( $price, $product ) {
	
	global $course_member_discount;

	if ( $product->is_type('course') && ilm_signed_in_member() ) {
		$price = $price - $course_member_discount;
	}

    return $price;

}
add_filter( 'woocommerce_product_get_price', 'ldp_member_only_pricing', 10, 2 );


function action_woocommerce_before_cart( ) {
	if ( !ilm_signed_in() ) {
		echo '<p>Please note: member prices will only display when signed in with your member account.</p><br>';
	}
}
add_action( 'woocommerce_before_cart', 'action_woocommerce_before_cart' );


/* Disable WordPress updates for non-developer admins */
$developerID = 1037;
function disable_updates() {
    global $wp_version;
    return (object) array( 'last_checked' => time(), 'version_checked' => $wp_version, );
}
add_action( 'init', function () {
	global $developerID;
	global $current_user;
    if ( $developerID != get_current_user_id() ) {
        add_filter( 'pre_site_transient_update_core', 'disable_updates' );     // Disable WordPress core updates
        add_filter( 'pre_site_transient_update_plugins', 'disable_updates' );  // Disable WordPress plugin updates
        add_filter( 'pre_site_transient_update_themes', 'disable_updates' );   // Disable WordPress theme updates
    }
} );
function my_restrict_access() {
	global $developerID;
	global $current_user;
    if ( $developerID != get_current_user_id() ) {
	    remove_menu_page( 'plugins.php' );
		remove_submenu_page( 'index.php', 'update-core.php' );
	}
}
add_action( 'admin_init', 'my_restrict_access' );

/* Disable email changed email notitication to users */
add_filter( 'send_email_change_email', '__return_false' );

/* Disable Map It link in gravity form confirmation emails */
add_filter( 'gform_disable_address_map_link', '__return_true' );

/* Autocomplete WooCommerce order if payment method is stripe */
add_action( 'woocommerce_thankyou', 'ilm_auto_complete_paid_order', 20, 1 );
function ilm_auto_complete_paid_order( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    
    if ( $order->has_status('processing') && $order->get_payment_method() == 'stripe' ) {
        $order->update_status( 'completed' );
    }
}

/* Includes */
include('inc/post-types.php');
include('inc/menus.php');
include('inc/shortcodes.php');
include('inc/user-organisation-details.php');
include('inc/bbpress.php');
include('inc/jobs.php');
include('inc/recaptcha.php');
