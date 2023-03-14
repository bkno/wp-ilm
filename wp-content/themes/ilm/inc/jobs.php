<?php

/* WP Jobs Manager customisations */

/**
 * Alter and add to form
 * @param  array $fields
 * @return array
 */
add_filter('submit_job_form_fields', 'ilm_jobs_frontend_fields');
function ilm_jobs_frontend_fields( $fields ) {
  $fields['job']['job_description']['label'] = "Description for email to members <br><small>(250 words max)</small>";
  $fields['job']['application']['label'] = 'Job web page URL';
  $fields['company']['company_name']['label'] = 'Company';
  $fields['company']['company_name']['placeholder'] = 'Organisation name';
  $fields['company']['company_name']['priority'] = 0;
  $fields['company']['company_contact_name'] = array(
    'label'       => 'Contact name',
    'type'        => 'text',
    'required'    => true,
    'placeholder' => 'Your full name',
    'priority'    => 10
  );
  $fields['company']['company_contact_email'] = array(
    'label'       => 'Contact email',
    'type'        => 'text',
    'required'    => true,
    'placeholder' => 'Your email address',
    'priority'    => 11
  );
  $fields['job']['job_po_number'] = array(
    'label'       => 'PO Number',
    'type'        => 'text',
    'required'    => false,
    'placeholder' => '',
    'priority'    => 12
  );
  return $fields;
}

add_filter( 'job_manager_job_listing_data_fields', 'ilm_jobs_admin_fields' );
function ilm_jobs_admin_fields( $fields ) {
  $fields['_application']['label'] = 'Job web page URL';
  $fields['_company_name']['label'] = 'Company';
  $fields['_company_contact_name'] = array(
    'label'       => 'Contact name',
    'type'        => 'text',
    'placeholder' => 'Your full name',
    'priority'    => 10
  );
  $fields['_company_contact_email'] = array(
    'label'       => 'Contact email',
    'type'        => 'text',
    'placeholder' => 'Your email address',
    'priority'    => 11
  );
  $fields['_job_po_number'] = array(
    'label'       => 'PO Number',
    'type'        => 'text',
    'placeholder' => '',
    'priority'    => 12
  );
  return $fields;
}

    
/**
 * Remove the preview step.
 * https://wpjobmanager.com/document/remove-the-preview-step/
 * @param  array $steps
 * @return array
 */
function ilm_jobs_submit_steps($steps) {
	unset( $steps['preview'] );
	return $steps;
}
add_filter( 'submit_job_steps', 'ilm_jobs_submit_steps' );

/**
 * Change button text.
 */
function ilm_jobs_change_button_text() {
	return __( 'Submit Job' );
}
add_filter( 'submit_job_form_submit_button_text', 'ilm_jobs_change_button_text' );

/**
 * Since we removed the preview step and it's handler, we need to manually publish jobs
 * @param  int $job_id
 */
function ilm_jobs_publish_submission( $job_id ) {
	$job = get_post( $job_id );
	if ( in_array( $job->post_status, array( 'preview', 'expired' ) ) ) {
		// Reset expirey
		delete_post_meta( $job->ID, '_job_expires' );
		// Update job listing
		$update_job                  = array();
		$update_job['ID']            = $job->ID;
		$update_job['post_status']   = get_option( 'job_manager_submission_requires_approval' ) ? 'pending' : 'publish';
		$update_job['post_date']     = current_time( 'mysql' );
		$update_job['post_date_gmt'] = current_time( 'mysql', 1 );
		wp_update_post( $update_job );
	}
}
add_action( 'job_manager_job_submitted', 'ilm_jobs_publish_submission' );

/**
 * Admin New Job Notification
 */
add_filter( 'job_manager_email_admin_new_job_from', function( $email ) {
    return 'support@legacymanagement.org.uk';
} );
add_filter( 'job_manager_email_admin_new_job_to', function( $email ) {
    return ['support@legacymanagement.org.uk', 'comms@legacymanagement.org.uk'];
} );