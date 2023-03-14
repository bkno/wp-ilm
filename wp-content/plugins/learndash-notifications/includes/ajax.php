<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * AJAX handler for 'ld_notifications_get_posts_list' action
 *
 * @return void
 */
function learndash_notifications_ajax_get_posts_list() {
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ld_notifications_nonce' ) ) {
        wp_die();
    }

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die();
    }

    // By default WP_Query search all post title, content, and excerpt. 
	// This filter modify it to only search in post title.
    add_filter( 'posts_search', function( $search, $wp_query ) {
        if ( isset( $wp_query->query['ld_notifications_action'] ) && $wp_query->query['ld_notifications_action'] === 'ld_notifications_get_posts_list' ) {
            $search = preg_replace( '/(OR)\s.*?post_(excerpt|content)\sLIKE\s.*?\)/', '', $search );
        }

        return $search;
    }, 10, 2 );

	$posts = array();
	$keyword   = isset( $_POST['keyword'] ) ? sanitize_text_field( $_POST['keyword'] ) : '';
	$post_type   = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : '';
	$group_id   = isset( $_POST['group_id'] ) ? sanitize_text_field( $_POST['group_id'] ) : null;
	$course_id   = isset( $_POST['course_id'] ) ? sanitize_text_field( $_POST['course_id'] ) : null;
	$lesson_id   = isset( $_POST['lesson_id'] ) ? sanitize_text_field( $_POST['lesson_id'] ) : null;
	$topic_id   = isset( $_POST['topic_id'] ) ? sanitize_text_field( $_POST['topic_id'] ) : null;
	$quiz_id   = isset( $_POST['quiz_id'] ) ? sanitize_text_field( $_POST['quiz_id'] ) : null;
	$parent_id   = isset( $_POST['parent_id'] ) ? sanitize_text_field( $_POST['parent_id'] ) : null;
	$parent_type = isset( $_POST['parent_type'] ) ? sanitize_text_field( $_POST['parent_type'] ) : null;

	switch ( $post_type ) {
		case 'groups':
			$label = LearnDash_Custom_Label::get_label( 'group' );
			break;

		case 'sfwd-courses':
			$label = LearnDash_Custom_Label::get_label( 'course' );
			break;
		
		case 'sfwd-lessons':
			$label = LearnDash_Custom_Label::get_label( 'lesson' );
			break;
		
		case 'sfwd-topic':
			$label = LearnDash_Custom_Label::get_label( 'topic' );
			break;
		
		case 'sfwd-quiz':
			$label = LearnDash_Custom_Label::get_label( 'quiz' );
			
			if ( $parent_id === 'all' && is_numeric( $lesson_id ) ) {
				$parent_id = $lesson_id;
			} elseif ( $parent_id === 'all' && is_numeric( $course_id ) ) {
				$parent_id = $course_id;
			}
			break;
	}

	if ( ! empty( $post_type ) ) {
		if ( 
			$parent_id === 'all'
			|| in_array(
				$post_type,
				array(
					learndash_get_post_type_slug( 'course' ),learndash_get_post_type_slug( 'group' )
				),
				true 
			) 
		) {
			if (
				in_array(
					$post_type,
					array(
						learndash_get_post_type_slug( 'course' ),learndash_get_post_type_slug( 'group' )
					),
					true 
				)
				|| $parent_type === 'course'
				|| $course_id === 'all'
			) {
				$posts = get_posts( array(
					'post_type' => $post_type,
					's' => $keyword,
					'posts_per_page' => 10,
					'post_status' => 'any',
					'orderby' => 'relevance',
					'order' => 'ASC',
					'suppress_filters' => false,
					'ld_notifications_action' => 'ld_notifications_get_posts_list',
				) );
			} elseif ( is_numeric( $course_id ) ) {
				$post_ids = learndash_course_get_steps_by_type( $course_id, $post_type );

				$posts = get_posts( array(
					'post_type' => $post_type,
					's' => $keyword,
					'post__in' => $post_ids,
					'posts_per_page' => 10,
					'post_status' => 'any',
					'orderby' => 'relevance',
					'order' => 'ASC',
					'suppress_filters' => false,
					'ld_notifications_action' => 'ld_notifications_get_posts_list',
				) );
			}
		} else {
			if ( intval( $course_id ) === intval( $parent_id ) ) {
				$post_ids = learndash_course_get_steps_by_type( $course_id, $post_type );
			} else {
				$post_ids = learndash_course_get_children_of_step( $course_id, $parent_id, $post_type );
			}

			if ( ! empty( $post_ids ) ) {
				$posts = get_posts( array(
					'post_type' => $post_type,
					's' => $keyword,
					'post__in' => $post_ids,
					'posts_per_page' => 10,
					'post_status' => 'any',
					'orderby' => 'relevance',
					'order' => 'ASC',
					'suppress_filters' => false,
					'ld_notifications_action' => 'ld_notifications_get_posts_list',
				) );
			}
		}
	}

    $results = [
		[
			'id' => 'all',
			'text' => sprintf( _x( 'Any %s', 'Any $post_type label', 'learndash-notifications' ), $label ),
		]
	];

    foreach ( $posts as $post ) {
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

        $results[] = array(
            'id'   => $post->ID,
            'text' => $post->post_title . '  (ID: ' . $post->ID . ')',
        );
    }

    echo wp_json_encode( $results );
    wp_die();
}

add_action( 'wp_ajax_ld_notifications_get_posts_list', 'learndash_notifications_ajax_get_posts_list' );
