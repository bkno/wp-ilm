<?php
    
/*
    Fix for the shortcode [bbp-topic-form] not working on forum index for a weird permissions reason
    Source: https://bbpress.org/forums/topic/new-topic-form-shortcode-issue/#post-143134
*/
add_filter( 'bbp_current_user_can_access_create_topic_form', 'custom_bbp_access_topic_form' );
function custom_bbp_access_topic_form($retval) {
	if (bbp_is_forum_archive()) {
		$retval = bbp_current_user_can_publish_topics();
	}
	return $retval;
}
