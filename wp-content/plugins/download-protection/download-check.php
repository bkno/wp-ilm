<?php

require_once('../../../wp-config.php');
require_once('../../../wp-includes/wp-db.php');
require_once('../../../wp-includes/pluggable.php');

if (ilm_signed_in() || current_user_can('editor') || current_user_can('administrator')) {
	if (ilm_signed_in_member() || current_user_can('editor') || current_user_can('administrator')) {

		$file =  getcwd().'/../../../'.$_GET['file'];
		#exit($file);
		if (file_exists($file)) {
			/* File download */
			/*header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.urlencode($_GET['file']).'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			exit;*/
			/* File open in browser */
			header("Content-type: application/pdf"); 
  		  	header("Content-Length: " . filesize($file)); 
			readfile($file);
			exit();
		} else {
			exit('Unable to locate file.');
		}
	} else {
    	wp_redirect( '/access-denied?destination=' . urlencode( $_SERVER["REQUEST_URI"] ), 302 );
		exit;
	}
} else {
	wp_redirect( '/access-denied?destination=' . urlencode( $_SERVER["REQUEST_URI"] ), 302 );
	exit;
}
