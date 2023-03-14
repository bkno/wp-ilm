<?php

// Course/Lesson Ordering Debug
add_shortcode( 'ilm_member_price', 'ilm_member_price_shortcode' );
function ilm_member_price_shortcode() {
	$date = date('Ymd');
	if ( $date >= '20230201' ) {
		return '£160';
	} else {
		return '£150';
	}
}

// Course/Lesson Ordering Debug
add_shortcode( 'ilm_course_debug', 'ilm_shortcode_course_debug' );
function ilm_shortcode_course_debug() {
	ob_start();
	?>
	<?php
		$args = array(
			'post_type' => 'sfwd-courses',
			'posts_per_page' => -1,
			'order' => 'ASC',
			'orderby' => 'guid',
			'post_status' => 'publish'
		);
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) :
				$loop->the_post();
				echo 'Course: <b>' . get_the_title() . '</b> ('.get_the_ID().')<br>';
				$lessons = learndash_get_course_lessons_list( get_the_ID() );
				foreach ( $lessons as $lesson ) {
					echo '- Lesson: ' . $lesson['post']->post_title . ' (' . $lesson['post']->ID . ')<br>';
					$topics = learndash_get_topic_list( $lesson['post']->ID );
					foreach ( $topics as $topic ) {
						echo '- - Topic: ' . $topic->post_title . ' (' . $topic->ID . ')<br>';
					}
					$quizes = learndash_get_lesson_quiz_list( $lesson['post']->ID );
					foreach ( $quizes as $quiz ) {
						echo '- - Quiz: ' . $quiz['post']->post_title . ' (' . $quiz['post']->ID . ')<br>';
						$questions = learndash_get_quiz_questions( $quiz['post']->ID );
						foreach ( $questions as $question_key => $question_value ) {
							echo '- - - Question: ' . get_the_title($question_key) . ' (' . $question_key . ')<br>';
							echo '- - - - ' . strip_tags( get_the_content( null, false, $question_key ) ) . '<br>';
						}
					}
				}
				echo '<br>';
			endwhile;
		} else {
			echo __( 'No products found' );
		}
		wp_reset_postdata();
	?>
	<?php
    return ob_get_clean();
}

// Shortcode [ilm_course_products category=""]
add_shortcode( 'ilm_course_products', 'ilm_shortcode_course_products' );
function ilm_shortcode_course_products( $atts ) {
	global $course_member_discount;
	$a = shortcode_atts( array(
		'category' => null,
	), $atts );
	ob_start();
	?>
	<style>
		.ilm .ilm-product-list-item { margin: 0 0 10px; padding: 25px 0 25px; border-bottom: 1px solid #ddd; }
		.ilm .ilm-product-list-item:last-child { padding-bottom: 0; border-bottom: none; }
		.ilm h3.ilm-product-list-title { font-weight: 700; font-size: 20px; text-transform: none; }
		.ilm p.ilm-product-list-excerpt { padding-bottom: 10px; }
		.ilm div.ilm-product-list-price { font-weight: 700; font-size: 16px; color: #4a3041 !important; }
		.ilm div.ilm-product-list-actions { margin: 5px 0; }
		.ilm div.ilm-product-list-actions .et_pb_button { margin: 10px 10px 10px 0; }
	</style>
	<div class="ilm-course-product-list">
	<?php
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'product_cat' => $a['category'],
			'order' => 'ASC',
			'orderby' => 'title',
		);
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) :
				$loop->the_post();
				global $product;
				$related_course = get_post_meta( get_the_ID(), '_related_course' );
				$course_url = false;
				if ( is_array( $related_course ) && count( $related_course ) === 1) {
#					var_dump($related_course[0]);
					$course_url = get_the_permalink( reset( maybe_unserialize( $related_course[0] ) ) );
				}
				?>
				<div class="ilm-product-list-item">
					<h3 class="ilm-product-list-title">
						<?php if ( $course_url ) : ?>
							<a href="<?php echo $course_url; ?>"><?php the_title(); ?></a>
						<?php else: ?>
							<?php the_title(); ?>
						<?php endif; ?>
					</h3>
					<p class="ilm-product-list-excerpt">
						<?php echo get_the_excerpt(); ?>
					</p>
					<div class="ilm-product-list-price">
						<span style="font-weight: 600; font-size: 0.95em;">Members</span> £<?php echo ( $product->get_regular_price() - $course_member_discount ); ?><br>
						<span style="font-weight: 600; font-size: 0.95em;">Non-Members</span> £<?php echo $product->get_regular_price(); ?>
					</div>
					<div class="ilm-product-list-actions">
						<?php if ( $course_url ) : ?>
							<a class="et_pb_button" href="<?php echo $course_url; ?>">Course Details</a>
						<?php endif; ?>
						<a class="et_pb_button alt" href="<?php echo $product->add_to_cart_url(); ?>">Add to basket</a>
					</div>
				</div>
				<?php
			endwhile;
		} else {
			echo __( 'No products found' );
		}
		wp_reset_postdata();
	?>
	</div>
	<?php
    return ob_get_clean();
}
        
/* Shortcode - partner logos */
add_shortcode( 'partner_logos', 'ilm_shortcode_partner_logos' );
function ilm_shortcode_partner_logos( $atts ) {
	ob_start();
    #get_template_part( 'partials/partners' );
    $posts = new WP_Query([
        'post_type' => 'partner',
        'post_status' => 'publish',
        'orderby' => 'rand',
        'order' => 'ASC',
        'posts_per_page' => -1,
    ]);
    if ($posts->have_posts()):
	    echo '<div class="logo-carousel">';
        while ($posts->have_posts()): $posts->the_post();
            if (has_post_thumbnail()):
            	echo '<div class="slide">';
				echo '<a href="'.get_the_permalink().'" style="background-image: url(\''.get_the_post_thumbnail_url().'\');" title="'.get_the_title().'"></a>';
                echo '</div>';
            endif;
        endwhile;
        echo '</div>';
        wp_reset_postdata();
    endif;

    return ob_get_clean();
}

/* Load the ACF form header call in. Normal way doesn't work with Divi. */
add_action( 'get_header', 'ilm_acf_form_header' );
function ilm_acf_form_header( $name ) {
    if (strpos($_SERVER['REQUEST_URI'], '/account/organisation/') === 0) {
	    acf_form_head();
	}
}

/* Shortcode - events recent */
add_shortcode( 'events_calendar', 'ilm_shortcode_events_calendar' );
function ilm_shortcode_events_calendar( $atts ) {
    $paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
    $posts = new WP_Query([
	    'has_password' => false,
        'post_type' => 'event',
        'orderby' => 'date',
        'posts_per_page' => 6,
        'paged' => $paged,
        'meta_query' => [
            'date_clause' => [
                'key' => 'event_date', 
                'value' => date('Ymd'),
                'type' => 'DATE',
                'compare' => '>'
            ]
        ],
        'orderby' => [
            'date_clause' => 'ASC'
        ],
        'facetwp' => true,
    ]);
	ob_start();
    if ($posts->have_posts()):
        $index = 0;
        echo '<div class="items items-events clearfix">';
        while ($posts->have_posts()): $posts->the_post();
            $index++;
            $class = 'item item-event item-'.$index;
            if (get_field('event_featured')):
                $class .= ' item-featured';
            endif;
            echo '<div class="'.$class.'">';
            if (has_post_thumbnail()):
                echo '<img src="'.get_the_post_thumbnail_url().'" alt="">';
            #else:
            #    echo '<img src="http://via.placeholder.com/800x600" alt="">';
            endif;
            echo '<h2><a href="'.get_the_permalink().'">'.get_the_title().'</a></h2>';
            echo '<p>';
            /*if (get_field('event_type')):
                echo get_field('event_type');
            endif;*/
            if (get_field('event_date')):
                echo /*' <span class="separator">|</span> '.*/get_field('event_date');
            endif;
            if (get_field('event_venue')):
                echo ' <span class="separator">|</span> '.get_field('event_venue');
            endif;
            if (get_field('event_members_only')):
                echo ' <span class="separator">|</span> Available only to ILM Members';
            endif;
            echo '</p>';
            echo '</div> <!-- .item .item-event -->';
        endwhile;
        echo '</div> <!-- .items .items-events -->';
        $GLOBALS['wp_query']->max_num_pages = $posts->max_num_pages;
        //ilm_pagination(); // using FacetWP pagination instead
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}

/* Shortcode - events recent */
add_shortcode( 'events_recent', 'ilm_shortcode_events_recent' );
function ilm_shortcode_events_recent( $atts ) {
    $posts = new WP_Query([
	    'has_password' => false,
        'post_type' => 'event',
        'orderby' => 'date',
        'posts_per_page' => 6,
        'meta_query' => [
            'date_clause' => [
                'key' => 'event_date', 
                'value' => date('Ymd'), 
                'type' => 'DATE',
                'compare' => '<'
            ]
        ],
        'orderby' => [
            'date_clause' => 'DESC'
        ]
    ]);
	ob_start();
    if ($posts->have_posts()):
        echo '<div class="items items-events clearfix">';
        while ($posts->have_posts()): $posts->the_post();
            $class = 'item item-event';
            echo '<div class="'.$class.'">';
            if (has_post_thumbnail()):
                echo '<img src="'.get_the_post_thumbnail_url().'" alt="">';
            #else:
            #    echo '<img src="http://via.placeholder.com/800x600" alt="">';
            endif;
            echo '<h3><a href="'.get_the_permalink().'">'.get_the_title().'</a></h3>';
            echo '<p>';
            if (get_field('event_date')):
                echo get_field('event_date');
            endif;
            if (get_field('event_venue')):
                echo '<br>'.get_field('event_venue');
            endif;
            echo '</p>';
            echo '</div> <!-- .item .item-event -->';
        endwhile;
        echo '</div> <!-- .items .items-events -->';
        wp_reset_postdata();
    endif;
    return ob_get_clean();
}


/* Shortcode - email notification of event registration */
add_shortcode( 'email_notification', 'ilm_notification_emails' );
function ilm_notification_emails( $atts ) {
    
    $a = shortcode_atts([
        'type' => null
    ], $atts);
    
    // Attempt to get user's details
	$userName = do_shortcode('[user field=firstname]') . ' ' . do_shortcode('[user field=lastname]');
	$userEmail = do_shortcode('[user field=email]');
    
	if ($userEmail != '') {
	    if ($a['type'] == 'event') {
		    $to = [
			    #'ben@digitalgarden.co',
		    	'training@legacymanagement.org.uk',
		    	'membership@legacymanagement.org.uk',
		    	'support@legacymanagement.org.uk',
		    ];
		    $subject = 'Event booking notification';
		    $message = "<p>Hi, </p>";
		    $message .= "<p>The event booking confirmation page was accessed, <a href='http://thankqlegacymanagement.accessacloud.com'>please check the CRM</a> for a booking submission. </p>";
		    $message .= "<p>The logged in user was: </p><p>" . $userName . "<br>" . $userEmail . " </p>";
			$message .= "<p>Note: if the user reloads the confirmation page, a repeat notification will be sent. </p>";
			$message .= "<p>This is an automated message, please do not reply. </p>";
			$message .= "<p>Thank you, </p>";
			$message .= "<p>ILM Website </p>";
		    $headers = ['Content-Type: text/html; charset=UTF-8'];
		    wp_mail($to, $subject, $message, $headers);
	    } else if ($a['type'] == 'membership') {
		    $to = [
			    #'ben@digitalgarden.co',
		    	'membership@legacymanagement.org.uk',
		    	'support@legacymanagement.org.uk',
		    ];
		    $subject = 'Membership notification';
		    $message = "<p>Hi, </p>";
		    $message .= "<p>The membership confirmation page was accessed, <a href='http://thankqlegacymanagement.accessacloud.com'>please check the CRM</a> for a membership application. </p>";
		    $message .= "<p>The logged in user was: </p><p>" . $userName . "<br>" . $userEmail . " </p>";
			$message .= "<p>Note: if the user reloads the confirmation page, a repeat notification will be sent. </p>";
			$message .= "<p>This is an automated message, please do not reply. </p>";
			$message .= "<p>Thank you, </p>";
			$message .= "<p>ILM Website </p>";
		    $headers = ['Content-Type: text/html; charset=UTF-8'];
		    wp_mail($to, $subject, $message, $headers);		    
		}
	}
}

add_shortcode('login_with_redirect', 'shortcode_login_with_redirect');
function shortcode_login_with_redirect($atts) {
    $loginPath = "/login/?t=" . time();
    if (array_key_exists('destination', $_GET)) {
        $loginPath .= "&destination=" . $_GET["destination"];
    }
    return $loginPath;
    /*return "/login/?destination=" . $_GET["destination"] .'&t='.time(); # V1 backup*/
};

/*
 * Shortcode to display the year
 */
add_shortcode( 'year', 'ilm_year' );
function ilm_year() {
    return date( 'Y' );
}

// Multi-purpse widget shortcode
// Example: [ILM widget=Login nexturl=account]
function ilm_panel_label_shortcode($atts, $content = null) {
	$a = shortcode_atts([
		'text' => '',
		'colour' => '',
	], $atts);
	return '<div class="panel-label-content panel-label-bg-' . $a['colour'] . '">' . $a['text'] . '</div>';
}
add_shortcode('panel-label', 'ilm_panel_label_shortcode');

/*
 * Shortcode to display the user id
 */
add_shortcode( 'user_id', '_shortcode_user_id' );
function _shortcode_user_id() {
	$member_id = sprintf( "%07s", get_current_user_id() );
	return $member_id;
}
        
/* Shortcode - Home page event calendar feed */
add_shortcode( 'home_event_feed', 'ilm_home_event_feed' );
function ilm_home_event_feed( $atts ) {
	ob_start();
    $posts = new WP_Query([
	    'has_password' => false,
        'post_type' => 'event',
        'orderby' => 'date',
        'posts_per_page' => 4,
        'paged' => $paged,
        'meta_query' => [
            'date_clause' => [
                'key' => 'event_date', 
                'value' => date('Ymd'),
                'type' => 'DATE',
                'compare' => '>'
            ]
        ],
        'orderby' => [
            'date_clause' => 'ASC'
        ],
    ]);
    if ($posts->have_posts()):
        echo '<div class="items items-events clearfix">';
        while ($posts->have_posts()): $posts->the_post();
            $index++;
            $class = 'item item-event item-event-compact item-'.$index;
            echo '<div class="'.$class.'">';
            echo '<h3><a href="'.get_the_permalink().'">'.get_the_title().'</a></h3>';
            echo '<p>';
            if (get_field('event_date')):
                echo /*' <span class="separator">|</span> '.*/get_field('event_date');
            endif;
            if (get_field('event_venue')):
                echo ' <span class="separator">|</span> '.get_field('event_venue');
            endif;
            if (get_field('event_members_only')):
                echo ' <span class="separator">|</span> Available only to ILM Members';
            endif;
            echo '</p>';
            echo '</div> <!-- .item .item-event -->';
        endwhile;
        echo '</div> <!-- .items .items-events -->';
        echo '<div class="items-more"><a href="/training-and-events/calendar/">View full calendar</a> &rsaquo;</div>';
    endif;

    return ob_get_clean();
}