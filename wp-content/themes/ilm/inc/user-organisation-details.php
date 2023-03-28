<?php

/* Account - redirect to user organisational details form if not yet populated */
if ($_SERVER['REQUEST_URI'] == '/account/') {
    $previousForm = get_page_by_title(do_shortcode('[user field=serial]'), OBJECT, 'organisation_details');
	if (is_null($previousForm)) {
        wp_redirect('/account/organisation/', 302);
        exit;
	}    
}

/* Shortcode - user organisational details form */
add_shortcode( 'user_organisation_details', 'ilm_user_organisation_details' );
function ilm_user_organisation_details( $atts ) {
	ob_start();
	
	$form = [
		'id' => 'user_organisation_details',
    	'field_groups' => [11847],
		'post_title' => false,
		'updated_message' => 'Changes saved, thank you.',
		'html_updated_message' => '<div id="org-details-message"><p>%s &nbsp;Continue to <a href="/account/">your account page ›</a></p></div>',
		'submit_value'	=> 'Submit'
	];

	/* Get post id of the form belonging to the user, based on CRM ID in their cookie */
    $previousForm = get_page_by_title(do_shortcode('[user field=serial]'), OBJECT, 'organisation_details');
	
	if (is_null($previousForm)) {
    	/* New form */
    	$form['post_id'] = 'new_post';
		$form['new_post'] = [
			'post_type'	=> 'organisation_details',
			'post_status' => 'publish'
		];
	} else {
    	/* Existing form */
    	$form['post_id'] = $previousForm->ID;
	}
	
	acf_form($form);

    return ob_get_clean();
}
 
add_filter('acf/load_field/name=org_first_name', 'ilm_acf_set_field_value_firstname');   
function ilm_acf_set_field_value_firstname( $field ) {
    $field['default_value'] = do_shortcode('[user field=firstname]');
    return $field;
}

add_filter('acf/load_field/name=org_last_name', 'ilm_acf_set_field_value_lastname');
function ilm_acf_set_field_value_lastname( $field ) {
    $field['default_value'] = do_shortcode('[user field=lastname]');
    return $field;
}

add_filter('acf/load_field/name=org_email', 'ilm_acf_set_field_value_email');
function ilm_acf_set_field_value_email( $field ) {
    $field['default_value'] = do_shortcode('[user field=email]');
    return $field;
}

add_filter( 'wp_insert_post_data', 'ilm_new_organisation_details', '99', 2 );
function ilm_new_organisation_details( $data , $postarr ) {
    #exit(var_dump($postarr));
    if ($postarr['post_type'] == 'organisation_details' && $postarr['ID'] == 0) {
        $data['post_title'] = do_shortcode('[user field=serial]');
    }
    return $data;
}