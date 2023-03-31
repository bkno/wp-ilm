<?php

/**
 * User preferences form
 * 
 * This is for opt-ins such as SMS and WhatsApp.
 */

add_shortcode( 'user_preferences', function( $atts ) {
	ob_start();
	
	if ( $user_id = get_current_user_id() ) {
		
		/* Get post id of the form belonging to the user based on their user_id */
		$post_id = null;
		
		$args = array(
			'author' => $user_id,
			'post_type' => 'preferences',
		);
		$q = new WP_Query( $args );
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$post_id = get_the_ID();
			}
		}
		wp_reset_postdata();

		$field_groups = acf_get_field_groups();
		$field_group_ids = array();
		foreach ( $field_groups as $field_group ) {
			/* User preferences */
			if ( $field_group['key'] == 'group_6426a8bbd99a5' ) {
				$field_group_ids[] = $field_group['ID'];
			}
		}

		$form = array(
			'id' => 'user_preferences',
			'field_groups' => $field_group_ids,
			'post_title' => false,
			'updated_message' => 'Changes saved, thank you.',
			'html_updated_message' => '<div class="alert">%s</div>',
			'submit_value'	=> 'Submit'
		);
		
		if ( is_null( $post_id ) ) {
			/* New form */
			$form['post_id'] = 'new_post';
			$form['new_post'] = array(
				'post_type'	=> 'preferences',
				'post_status' => 'publish'
			);
		} else {
			/* Existing form */
			$form['post_id'] = $post_id;
		}
		
		acf_form($form);
	} else {
		echo '<p>You need to be logged in to see this form.</p>';
	}

    return ob_get_clean();
} );
 
add_filter( 'wp_insert_post_data', function ( $data, $postarr ) {

    if ( $postarr['post_type'] == 'preferences' && $postarr['ID'] == 0 ) {
		$post_id = $postarr['ID'];
		$author_id = $postarr['post_author'];
		$author_firstname = get_the_author_meta( 'first_name', $author_id );
		$author_lastname = get_the_author_meta( 'last_name', $author_id );
		if ( $author_firstname && $author_lastname ) {
			$data['post_title'] = $author_firstname . ' ' . $author_lastname . ' (User ID '. $author_id . ')' ;
		}
    }
    return $data;
}, '99', 2 );