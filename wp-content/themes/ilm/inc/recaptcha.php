<?php

/*

// Define your keys here
define( 'RECAPTCHA_SITE_KEY', '6LfGQC8UAAAAAIYVfcgXVW4u5QZ_ww-dWmZLP5sH' );
define( 'RECAPTCHA_SECRET_KEY', '6LfGQC8UAAAAAC5Z08PbQ8d7ImcDnMoFs16R9VC9' );

// Enqueue Google reCAPTCHA scripts
add_action( 'wp_enqueue_scripts', 'ilm_recaptcha_scripts' );
function ilm_recaptcha_scripts() {
	wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );
}

// Add reCAPTCHA to the job submission form
// If you disabled company fields, the submit_job_form_end hook can be used instead from version 1.24.1 onwards
add_action( 'submit_job_form_company_fields_end', 'ilm_recaptcha_field' );
function ilm_recaptcha_field() {
	?>
	<fieldset>
		<label>Are you human?</label>
		<div class="field">
			<div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
		</div>
	</fieldset>
	<?php
}
// Validate
add_filter( 'submit_job_form_validate_fields', 'ilm_validate_recaptcha_field' );
function ilm_validate_recaptcha_field( $success ) {
	$response = wp_remote_get( add_query_arg( array(
		'secret'   => RECAPTCHA_SECRET_KEY,
		'response' => isset( $_POST['g-recaptcha-response'] ) ? $_POST['g-recaptcha-response'] : '',
		'remoteip' => isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']
	), 'https://www.google.com/recaptcha/api/siteverify' ) );
	if ( is_wp_error( $response ) || empty( $response['body'] ) || ! ( $json = json_decode( $response['body'] ) ) || ! $json->success ) {
		return new WP_Error( 'validation-error', '"Are you human" check failed. Please try again.' );
	}
	return $success;
}
*/