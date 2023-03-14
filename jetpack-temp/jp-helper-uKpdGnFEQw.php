<?php /* Jetpack Backup Helper Script */
define( 'JP_EXPIRES', 1678719163 );
define( 'JP_SECRET', 'tmg1HX2ZJIgqZXuq6AhDVuIHTQYffbPU' );
define( 'WP_PATH', '[wp_path]' );
ini_set( 'error_reporting', 0 );

// Error codes
define( 'COMMS_ERROR',        128 );
define( 'MYSQLI_ERROR',       129 );
define( 'MYSQL_ERROR',        130 );
define( 'NOT_FOUND_ERROR',    131 );
define( 'READ_ERROR',         132 );
define( 'INVALID_TYPE_ERROR', 133 );
define( 'MYSQL_INIT_ERROR',   134 );
define( 'CREDENTIALS_ERROR',  135 );
define( 'WRITE_ERROR',        136 );
define( 'EXPIRY_ERROR',       137 );

// disable various swift-performance-lite features that interfere with us
// this caches things so aggressively that even helper script responses over SSH are cached!
// in theory there are filters defined in the plugin that should allow us to turn it off like that
// however, I was unable to get them to do anything, so we're stuck with this jank
$_GET['swift-no-cache'] = 1;
define( 'SWIFT_PERFORMANCE_THREAD', false );

// Ensure no output buffering
while ( ob_get_level() ) {
	ob_end_clean();
}

// Ported from wp-includes/compat.php so we don't have to load WP for things that don't need it
if ( ! function_exists( 'hash_equals' ) ) :
/**
	* Timing attack safe string comparison
	*
	* Compares two strings using the same time whether they're equal or not.
	*
	* This function was added in PHP 5.6.
	*
	* Note: It can leak the length of a string when arguments of differing length are supplied.
	*
	* @since 3.9.2
	*
	* @param string $a Expected string.
	* @param string $b Actual, user supplied, string.
	* @return bool Whether strings are equal.
	*/
function hash_equals( $a, $b ) {
		$a_length = strlen( $a );
		if ( $a_length !== strlen( $b ) ) {
				return false;
		}
		$result = 0;

		// Do not attempt to "optimize" this.
		for ( $i = 0; $i < $a_length; $i++ ) {
				$result |= ord( $a[ $i ] ) ^ ord( $b[ $i ] );
		}

		return $result === 0;
}
endif;

// Added in PHP 5.3; stub it out if we don't have it
if ( ! function_exists( 'json_last_error' ) ) :
function json_last_error() {
	return "JSON error information not available due to PHP version";
}
endif;

// Unpack arguments; support CLI or web.
$is_cli = ( 'cli' === php_sapi_name() );
if ( $is_cli ) {
	if ( count( $argv ) !== 3 ) {
		fatal_error( COMMS_ERROR, 'Invalid args', 400 );
	}

	list( $script, $action, $base64_args ) = $argv;
} else {
	$action      = $_POST['action'];
	$base64_args = $_POST['args'];
	$salt        = $_POST['salt'];
	$signature   = (string) $_POST['signature'];
}

$json_args = base64_decode( $base64_args );

if ( ! $is_cli ) {
	// Check expiry
	if ( time() > JP_EXPIRES ) {
		fatal_error( EXPIRY_ERROR, 'Expired', 419 );
	}

	// Check signature.
	if ( ! authenticate( $action, $json_args, $salt, $signature ) ) {
		fatal_error( COMMS_ERROR, 'Forbidden', 403 );
	}

	// Set an opaque Content-Type by default, to avoid tripping up broken web servers.
	header( 'Content-Type: application/octet-stream' );
}

// Execute action.
$args = (array)json_decode( $json_args );
jpr_action( $action, $args );
exit( 0 );

function fatal_error( $code, $message, $http_code = 200 ) {
	global $is_cli;

	if ( $is_cli ) {
		fwrite( STDERR, "\n" . json_encode( array(
			'code'    => $code,
			'message' => $message,
		) ) . "\n" );
		die( $code );
	} else {
		header( 'X-VP-Ok: 0', true, $http_code );
		header( 'X-VP-Error-Code: ' . $code );
		header( 'X-VP-Error: ' . base64_encode( $message ) );
		exit;
	}
}

function success_header() {
	global $is_cli;

	if ( ! $is_cli ) {
		header( 'X-VP-Ok: 1', true );
	}
}

function authenticate( $action, $json_args, $salt, $incoming_signature ) {
	$to_sign   = "{$action}:{$json_args}:{$salt}";
	$signature = hash_hmac( 'sha1', $to_sign, JP_SECRET );

	return hash_equals( $signature, $incoming_signature );
}

function jpr_action( $action, $args ) {
	$actions = array(
		'db_results'                  => 'action_db_results',
		'db_dump'                     => 'action_db_dump',
		'db_upload'                   => 'action_db_upload',
		'db_import'                   => 'action_db_import',
		'count_files'                 => 'action_count_files',
		'ls'                          => 'action_ls',
		'grep'                        => 'action_grep',
		'stat'                        => 'action_stat',
		'test'                        => 'action_test',
		'info'                        => 'action_info',
		'paths'                       => 'action_paths',
		'cleanup_helpers'             => 'action_cleanup_helpers',
		'cleanup_restore'             => 'action_cleanup_restore',
		'walk'                        => 'action_walk',
		'flush'                       => 'action_flush',
		'trigger_jp_sync'             => 'action_trigger_jp_sync',
		'delete_tree'                 => 'action_delete_tree',
		'get_active_theme'            => 'action_get_active_theme',
		'symlink'                     => 'action_symlink',
		'validate_theme'              => 'action_validate_theme',
		'woocommerce_install'         => 'action_woocommerce_install',
		'get_file'                    => 'action_get_file',
		'enable_jetpack_sso'          => 'action_enable_jetpack_sso',
		'transfer_jetpack_connection' => 'action_transfer_jetpack_connection',
		'install_extension'           => 'action_install_extension',
		'check_file_existence'        => 'action_check_file_existence',
		'upgrade_extension'           => 'action_upgrade_extension',
		'remove_waf_blocklog'         => 'action_remove_waf_blocklog',
	);

	if ( empty( $actions[ $action ] ) ) {
		fatal_error( COMMS_ERROR, 'Invalid method', 405 );
	}

	call_user_func( $actions[ $action ], $args );
}

function get_wordpress_location() {
	if ( '[wp_' . 'path]' !== WP_PATH ) {
		return rtrim( WP_PATH, '/\\' );
	} else {
		return dirname( __DIR__ );
	}
}

function localize_path( $path ) {
	return preg_replace( '/^{\$ABSPATH\}/', get_wordpress_location(), $path );
}

function load_wp( $with_plugins = false, $error_func = 'fatal_error' ) {
	if ( ! defined( 'WP_INSTALLING' ) && ! $with_plugins ) {
		define( 'WP_INSTALLING', true );
	}

	$wp_directory = get_wordpress_location();
	$wp_load_path = $wp_directory . '/wp-load.php';
	if ( ! file_exists( $wp_load_path ) ) {
		call_user_func( $error_func, CREDENTIALS_ERROR, "Could not find WordPress in {$wp_directory}" );
	}

	if ( ! is_readable( $wp_load_path ) ) {
		call_user_func( $error_func, CREDENTIALS_ERROR, "Can not read wp-load.php in {$wp_directory}" );
	}

	ob_start();
	require_once( $wp_load_path );
	ob_end_clean();
}

function encode_json_with_check( $obj ) {
	$json_options = 0;
	if ( defined( 'JSON_PARTIAL_OUTPUT_ON_ERROR' ) ) {
		// since PHP 5.5.0; allows us to handle more weird characters without completely failing
		// since PHP 5.4.0; gives us better output for some unicode characters that don't seem to escape otherwise
		$json_options = JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE;
	} elseif ( defined( 'JSON_UNESCAPED_UNICODE' ) ) {
		// since PHP 5.4.0; gives us better output for some unicode characters that don't seem to escape otherwise
		$json_options = JSON_UNESCAPED_UNICODE;
	}

	$json = json_encode( $obj, $json_options );
	if ( false === $json ) {
		fatal_error( COMMS_ERROR, 'JSON error: ' . json_last_error() );
	}

	return $json;
}

function send_json_with_check( $obj, $with_newline = true, $flush_afterwards = true ) {
	$json = encode_json_with_check( $obj );

	echo $json;
	if ( $with_newline ) {
		echo "\n";
	}

	if ( $flush_afterwards ) {
		// Some webservers and PHP configurations are made to buffer the output of scripts, and we both:
		//
		// 1. Expect to get the output of various commands (e.g. SQL imports) live, and
		// 2. Don't want the webserver or some middleware (e.g. Cloudflare) to time out idle connections (which might
		//    become idle if the webserver is buffering output and not sending anything back).
		//
		flush();
	}
}

function action_test( $args ) {
	success_header();
	echo json_encode( array( 'ok' => true ) );
	exit;
}

function action_flush( $args ) {
	load_wp();

	delete_option( 'rewrite_rules' );

	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();

		success_header();
		echo json_encode( array( 'ok' => true ) );
		exit;
	}

	if ( function_exists( 'wp_cache_clean_cache' ) ) {
		global $file_prefix;
		wp_cache_clean_cache( $file_prefix, true );
	}

	fatal_error( COMMS_ERROR, 'wp_cache_flush() not loaded' );
}

function action_trigger_jp_sync( $args ) {
	load_wp( true ); // need plugins so we get the JP functions

	if ( is_callable( array( 'Automattic\Jetpack\Sync\Actions', 'do_full_sync' ) ) ) {
		\Automattic\Jetpack\Sync\Actions::do_full_sync();

		success_header();
		echo json_encode( array( 'ok' => true, 'legacy_sync_call' => false ) );
		exit;
	}

	// this call is deprecated since jetpack-7.5,
	// but we still need to use it for sites running older versions of plugin
	if ( is_callable( array( 'Jetpack_Sync_Actions', 'do_full_sync' ) ) ) {
		Jetpack_Sync_Actions::do_full_sync();

		success_header();
		echo json_encode( array( 'ok' => true, 'legacy_sync_call' => true ) );
		exit;
	}

	fatal_error( COMMS_ERROR, 'Neither Automattic\Jetpack\Sync\Actions::do_full_sync() nor Jetpack_Sync_Actions::do_full_sync() loaded' );
}

function action_upgrade_extension( $args ) {
	load_wp( true );

	$slug = $args['slug'];
	$type = $args['type'];

	if ( function_exists( 'jetpack_require_lib' ) ) {
		jetpack_require_lib( 'plugins' );
	}

	if ( class_exists( 'Automatic_Upgrader_Skin' ) ) {
		$skin = new Automatic_Upgrader_Skin();

		if ( $type === 'plugin' ) {
			$upgrader = new Plugin_Upgrader( $skin );
			$extension_path = get_plugin_path_from_slug( $slug );
		} else {
			$upgrader = new Theme_Upgrader( $skin );
			$extension_path = $slug;
		}

		$result = $upgrader->upgrade( $extension_path );

		if ( ! $result ) {
			$error_messages = print_r( $skin->get_upgrade_messages(), true );
			fatal_error( COMMS_ERROR, 'Could not upgrade extension: ' . $error_messages );
		}

		if ( is_wp_error( $result ) ) {
			fatal_error( COMMS_ERROR, 'Could not upgrade extension: ' . $result->get_error_message() );
		}

		success_header();
		echo json_encode( array( 'ok' => true ) );
		exit;
	}

	fatal_error( COMMS_ERROR, 'Automatic_Upgrader_Skin not loaded' );
}

function action_remove_waf_blocklog( $args ) {
	load_wp( true );

	$contentPath = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : $absPath . '/wp-content';

	@unlink( $contentPath . '/jetpack-waf/waf-blocklog');

	success_header();
	echo json_encode( array( 'ok' => true ) );
	exit;
}

function action_install_extension( $args ) {
	load_wp( true );

	if ( function_exists( 'jetpack_require_lib' ) ) {
		jetpack_require_lib( 'plugins' );
	}

	$slug = $args['slug'];
	$type = $args['type'];

	if ( class_exists( 'Jetpack_Automatic_Install_Skin' ) ) {
		$skin = new Jetpack_Automatic_Install_Skin();

		if ( $type === 'plugin' ) {
			$upgrader = new Plugin_Upgrader( $skin );
			$zipUrl = "https://downloads.wordpress.org/plugin/$slug.latest-stable.zip";
		} else {
			$upgrader = new Theme_Upgrader( $skin );
			$zipUrl = "https://downloads.wordpress.org/theme/$slug.latest-stable.zip";
		}

		$result = $upgrader->install( $zipUrl );

		if ( ! $result ) {
			fatal_error( COMMS_ERROR, 'Could not install extension' );
		}

		if ( is_wp_error( $result ) ) {
			fatal_error( COMMS_ERROR, 'Could not install extension: ' . $result->get_error_message() );
		}

		success_header();
		echo json_encode( array( 'ok' => true ) );
		exit;
	}

	fatal_error( COMMS_ERROR, 'Jetpack_Plugins::install_plugin() not loaded' );
}

function action_info( $args ) {
	load_wp();
	global $wpdb, $wp_version, $wp_theme_directories;

	// get installed themes.
	$themes = array();
	$current_theme = wp_get_theme();
	foreach ( wp_get_themes() as $key => $theme ) {
		$themes[ $key ] = array(
			'Name' => $theme['Name'],
			'ThemeURI' => $theme->get( 'ThemeURI' ),
			'Version' => $theme['Version'],
			'Author' => $theme->get( 'Author' ), // use get() to get the raw value; array access uses display() not get()
			'AuthorURI' => $theme->get( 'AuthorURI'),
			'path' => base64_encode( $theme->get_stylesheet_directory() . '/style.css' ),
			'status' => $theme['Name'] === $current_theme['Name'] ? 'active': 'inactive',
		);
	}

	// get installed plugins.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$plugins = get_plugins();

	// post-process so these are by slug too, like themes.
	$plugins_by_slug = array();
	foreach ( $plugins as $path => $plugin ) {
		if ( false === strpos( $path, '/' ) ) {
			$slug = explode( '.php', $path );
			$slug = $slug[0];
		} else {
			$slug = explode( '/', $path );
			$slug = $slug[0];
		}
		$plugins_by_slug[ $slug ] = $plugin;
		$plugins_by_slug[ $slug ]['path'] = base64_encode( WP_PLUGIN_DIR . '/' . $path );
		$plugins_by_slug[ $slug ]['status'] = is_plugin_active( $path ) ? 'active' : 'inactive';
	}

	// grab some useful constants
	$useful_constants = array( 'IS_PRESSABLE', 'VIP_GO_ENV' );
	$constant_values = array();
	foreach ( $useful_constants as $constant ) {
		if ( defined( $constant ) ) {
			$constant_values[ $constant ] = constant( $constant );
		}
	}

	// get info about foreign key constraints
	$fks = $wpdb->get_results( $wpdb->prepare( "
		SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
		FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
		WHERE REFERENCED_TABLE_SCHEMA = %s
		AND TABLE_NAME LIKE %s",
		$wpdb->dbname,
		$wpdb->esc_like( $wpdb->prefix ) . '%'
	) );

	$absPath = get_wordpress_location();
	$contentPath = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : $absPath . '/wp-content';
	$pluginsPath = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : $contentPath . '/plugins';
	$uploadsPath = wp_upload_dir()['basedir'];
	if ( ! is_dir( $uploadsPath ) ) {
		$uploads_path_option = trim( get_option( 'upload_path' ) );
		if ( ! empty( $uploads_path_option ) && is_dir( $absPath . '/' . $uploads_path_option ) ) {
			$uploadsPath = $absPath . '/' . $uploads_path_option;
		} else if ( defined( 'UPLOADS' ) && is_dir( $absPath . '/' . UPLOADS ) ) {
			$uploadsPath = $absPath . '/' . UPLOADS;
		} else {
			$uploadsPath = $contentPath . '/uploads';
		}
	}

	$theme_paths = $wp_theme_directories;
	if ( ! is_array( $wp_theme_directories ) ) {
		$theme_paths = array( $contentPath . '/themes' );
	}

	// If the content directory is considered "under" the abspath it won't be
	// explicitly walked.  If it's encountered as a symlink during normal walking
	// it will be skipped if it's not under abspath.  We resolve it so that it
	// will be explicitly walked if it resolves outside of abspath.
	if ( is_link( $contentPath ) ) {
		$symlinkContent = $contentPath;
		$contentPath = realpath( $contentPath );

		if ( strpos( $pluginsPath, $symlinkContent ) == 0 ) {
			$pluginsPath = str_replace( $symlinkContent, $contentPath, $pluginsPath );
		}
		if ( strpos( $uploadsPath, $symlinkContent ) == 0 ) {
			$uploadsPath = str_replace( $symlinkContent, $contentPath, $uploadsPath );
		}
		$idx = 0;
		foreach( $theme_paths as $theme_path ) {
			if ( strpos( $theme_path, $symlinkContent ) == 0 ) {
				$theme_paths[$idx] = str_replace( $symlinkContent, $contentPath, $theme_path );
			}
			++$idx;
		}
	}

	success_header();
	send_json_with_check( array(
		'wp_version' => $wp_version,
		'php_version' => phpversion(),
		'php_settings' => array(
			'memory_limit' => ini_get( 'memory_limit' ),
			'max_execution_time' => ini_get( 'max_execution_time' ),
		),
		'locale' => get_locale(),
		'table_prefix' => $wpdb->prefix,
		'themes' => $themes,
		'plugins' => $plugins_by_slug,
		'constants' => $constant_values,
		'foreign_keys' => $fks,
		'multisite' => is_multisite(),
		'themePaths' => array_map( 'base64_encode', $theme_paths ),
		'pluginsPath' => base64_encode( $pluginsPath ),
		'contentPath' => base64_encode( $contentPath ),
		'uploadsPath' => base64_encode( $uploadsPath ),
		'abspath' => base64_encode( $absPath ),
		'baseUrl' => get_site_url(),
	), false );
}

/**
 * This is a simplified version of info() method above that returns paths-related fields only.
 *
 * It's used by toPhpPath() function in Transport Server.
 *
 * This method applies a fallback for cases where wp-config.php file is not present on the site.
 */
function action_paths( $args ) {
	$is_wp_loaded = false;
	$absPath = get_wordpress_location();

	if ( file_exists( $absPath . '/wp-load.php' ) ) {
		global $wp_theme_directories;

		load_wp();
		$is_wp_loaded = true;

		$contentPath = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : $absPath . '/wp-content';
		$pluginsPath = defined( 'WP_PLUGIN_DIR' ) ? WP_PLUGIN_DIR : $contentPath . '/plugins';
		$uploadsPath = wp_upload_dir()['basedir'];

		$themePaths = $wp_theme_directories;
		if ( ! is_array( $wp_theme_directories ) ) {
			$themePaths = array( $contentPath . '/themes' );
		}
	}
	else {
		// apply a fallback for cases where wp-config.php is not there
		$contentPath = $absPath . '/wp-content';
		$pluginsPath = $contentPath . '/plugins';
		$uploadsPath = $contentPath . '/uploads';
		$themePaths  = array( $contentPath . '/themes' );
	}

	success_header();
	send_json_with_check( array(
		'is_wp_loaded' => $is_wp_loaded,
		'abspath' => base64_encode( $absPath ),
		'pluginsPath' => base64_encode( $pluginsPath ),
		'contentPath' => base64_encode( $contentPath ),
		'uploadsPath' => base64_encode( $uploadsPath ),
		'themePaths' => array_map( 'base64_encode', $themePaths ),
	), false );
}

function db_query( $sql, $error_func = 'fatal_error' ) {
	global $wpdb;

	if( ! is_object( $wpdb ) ) {
		load_wp( false, $error_func );
	}

	if ( ! $wpdb->dbh ) {
		call_user_func( $error_func, MYSQL_INIT_ERROR, 'MySQL not initialized' );
	}

	if ( $wpdb->use_mysqli ) {
		$result = mysqli_query( $wpdb->dbh, $sql, MYSQLI_USE_RESULT );
		if ( ! $result ) {
			call_user_func( $error_func, MYSQLI_ERROR, mysqli_error( $wpdb->dbh ) );
		}
	} else {
		$result = mysql_unbuffered_query( $sql, $wpdb->dbh );
		if ( ! $result ) {
			call_user_func( $error_func, MYSQL_ERROR, mysql_error( $wpdb->dbh ) );
		}
	}

	return $result;
}

function action_db_results( $args ) {
	global $wpdb;

	$args = array_merge( array(
		'query' => null,
	), $args );

	$query = db_query( $args['query'] );

	success_header();

	$fields = null;
	$types = [];

	$flush_every_x_rows = 100;
	$row_number = 0;

	while ( ! empty( $row = ( $wpdb->use_mysqli ? mysqli_fetch_assoc( $query ) : mysql_fetch_assoc( $query ) ) ) ) {
		// First row; detect the names of the fields, send as an array.
		if ( empty( $fields ) ) {
			$fields = array_keys( $row );
			send_json_with_check( array_map( 'base64_encode', $fields ), true, false );

			// detect columns types to provide better typing of dumped values
			// for instance do not base64-encode numeric values in BIT columns
			if ( $wpdb->use_mysqli ) {
				foreach ( $fields as $field_mame ) {
					// @see https://www.php.net/manual/en/mysqli-result.fetch-field.php
					$info = mysqli_fetch_field( $query );

					if ( is_object( $info ) && ( $field_mame === $info->name ) ) {
						$types[$info->name] = [
							'flags'      => $info->flags,
							// for, now apply the numeric encoding to BIT columns only
							// @see https://www.php.net/manual/en/mysqli.constants.php
							'is_numeric' => in_array( $info->type, [
								16, // MYSQLI_TYPE_BIT
							] ),
						];
					}
				}
			}
		}

		$values = array_map(
			function( $field ) use ( &$row, $types ) {
				if ( is_null( $row[$field] ) ) {
					return null;
				}

				if ( !empty( $types[ $field ][ 'is_numeric'] ) ) {
					return (int) $row[$field];
				}

				return base64_encode( $row[$field] );
			},
			$fields
		);
		send_json_with_check( $values, true, false );

		++$row_number;
		if ( 0 === ( $row_number % $flush_every_x_rows ) ) {
			flush();
		}
	}

	flush();

	if ( $wpdb->use_mysqli ) {
		@mysqli_free_result( $query );
	} else {
		@mysql_free_result( $query );
	}
}

/**
 * This method is similar to action_db_results but adds a prefix "WPFIELDTYPE" with the type of the field.
 * This can be used by the received to take decisions about how to read it.
 *
 * @param array $args Array with the info of the query to run
 */
function action_db_dump( $args ) {
	global $wpdb;

	$args = array_merge( array(
		'query' => null,
	), $args );

	$query = db_query( $args['query'] );

	success_header();

	$fields = null;

	$flush_every_x_rows = 100;
	$row_number = 0;

	while ( ! empty( $row = ( $wpdb->use_mysqli ? mysqli_fetch_assoc( $query ) : mysql_fetch_assoc( $query ) ) ) ) {
		// First row; detect the names of the fields, send as an array.
		if ( empty( $fields ) ) {
			$fields = array_keys( $row );
			send_json_with_check( array_map( 'base64_encode', $fields ), true, false );
		}

		$index = 0;
		$values = [];
		foreach ( $fields as $field_key ) {
			$field = mysqli_fetch_field_direct( $query, $index );

			if ( is_null( $row[ $field_key ] ) ) {
				$values[] = null;
			} else {
				$values[] = base64_encode( 'WPFIELDTYPE' . $field->type . '_' . $row[ $field_key ] . '' );
			}
			$index++;
		}

		send_json_with_check( $values, true, false );

		++$row_number;
		if ( 0 === ( $row_number % $flush_every_x_rows ) ) {
			flush();
		}
	}

	flush();

	if ( $wpdb->use_mysqli ) {
		@mysqli_free_result( $query );
	} else {
		@mysql_free_result( $query );
	}
}

function action_db_upload( $args ) {
	$args = array_merge( array(
		'sql' => null,
	), $args );

	foreach ( explode( ";\n", $args['sql'] ) as $line ) {
		$line = trim( $line );
		if ( empty( $line ) ) {
			continue;
		}

		db_query( $line );
	}

	success_header();
	echo "Success\n";
}

function streamed_error( $code, $message ) {
	// no cli/http switch, just send the content in whatever stream this is
	send_json_with_check( array( 'error' => true, 'code' => $code, 'message' => $message ) );
	exit;
}

function action_db_import( $args ) {
	// start reporting back right away, we stream back errors and status via the body
	success_header();

	if ( empty( $args['importPath'] ) ) {
		streamed_error( COMMS_ERROR, 'Invalid path' );
	}

	$import_path = localize_path( base64_decode( $args['importPath'] ) );

	if ( ! file_exists( $import_path ) ) {
		streamed_error( NOT_FOUND_ERROR, "File not found: {$import_path}" );
	}

	$handle = fopen( $import_path, 'rb' );
	if ( false === $handle ) {
		streamed_error( READ_ERROR, "Failed to open file {$import_path} for import." );
	}
	$buf = '';
	$notify_every = 8; // progress every 8 seconds
	$lines = 0;
	$last_notify = time();

	while ( true ) {
		$read = fread( $handle, 1024 );
		if ( false == $read ) {
			break;
		}
		$buf .= $read;

		// we use regexps here to handle optional \r\n style newlines as well as sane \n newlines
		// mysqldump generates these, sadly
		while ( 1 === preg_match( "/;\r?\n/", $buf ) ) {
			$split = preg_split( "/;\r?\n/", $buf, 2 );
			$line = trim( $split[0] );
			$buf = $split[1];
			if ( empty( $line ) ) {
				continue;
			}

			db_query( $line, 'streamed_error' );
			$lines++;
			if ( time() - $last_notify >= $notify_every ) {
				send_json_with_check( array( 'error' => false, 'message' => 'Imported ' . $lines . ' queries' ) );
				$last_notify = time();
			}
		}
		if ( time() - $last_notify >= $notify_every ) {
			send_json_with_check( array( 'error' => false, 'message' => 'Imported ' . $lines . ' queries' ) );
			$last_notify = time();
		}
	}

	$buf = trim( $buf );
	if ( strlen( $buf ) > 0 ) {
		db_query( $buf, 'streamed_error' );
		$lines++;
	}

	fclose( $handle );
	send_json_with_check( array( 'error' => false, 'message' => 'Success, ' . $lines . ' queries overall' ) );
}

function clean_pathname_string( $path ) {
	// paths are arbitrary bytes, send them in base-64 so JSON doesn't choke on them
	return base64_encode( $path );
}

function get_username( $stat ) {
	$info = false;
	if ( function_exists( 'posix_getpwuid' ) ) {
		$info = posix_getpwuid( $stat['uid'] );
	}

	if ( $info ) {
		return $info['name'];
	} else {
		return $stat['uid'];
	}
}

function get_groupname( $stat ) {
	$info = false;
	if ( function_exists( 'posix_getgrgid' ) ) {
		$info = posix_getgrgid( $stat['gid'] );
	}

	if ( $info ) {
		return $info['name'];
	} else {
		return $stat['gid'];
	}
}

function get_ls_entry( &$args, $path, $file, $skip_hashes = false ) {
	$full_path = $path . '/' . $file;
	$entry = array(
		'name' => clean_pathname_string( $file ),
	);

	if ( is_link( $full_path ) ) {
		$entry['is_link'] = 1;
		$entry['canonical'] = clean_pathname_string( readlink( $full_path ) );
		$entry['absolute'] = clean_pathname_string( realpath( $full_path ) );
	} else {
		$entry['canonical'] = clean_pathname_string( realpath( $full_path ) );
		$entry['absolute'] = $entry['canonical'];
	}

	// TODO: Replace this with the special path (ie. {$PLUGINS}/...)
	$entry['relative'] = clean_pathname_string( str_replace( get_wordpress_location(), '', $full_path ) );

	if ( ! is_readable( $full_path ) ) {
		$entry['unreadable'] = true;
	}

	if ( ! empty( $args['stat'] ) ) {
		$entry['stat'] = stat( $full_path );
		$entry['stat']['username'] = get_username( $entry['stat'] );
		$entry['stat']['groupname'] = get_groupname( $entry['stat'] );
	}

	if ( ! empty( $args['lstat'] ) ) {
		$entry['stat'] = lstat( $full_path );
		$entry['stat']['username'] = get_username( $entry['stat'] );
		$entry['stat']['groupname'] = get_groupname( $entry['stat'] );
	}

	// Remove duplicate data from stat.  We reference the associative values only.
	$numeric_stat_array_total = 13;
	for ( $i = 0; $i < $numeric_stat_array_total; $i++ ) {
		unset( $entry['stat'][$i] );
	}

	if ( isset( $args['window'] ) && floatval( $args['window'] > 1 ) ) {
		// if the caller is windowing the hashes, let them know that the file is unchanged in that window
		// thus, they will not expect the hash to be set for it
		$entry['unchanged'] = ( $entry['stat']['mtime'] < floatval( $args['window'] ) );
	}

	if ( is_dir( $full_path ) ) {
		$entry['is_dir'] = 1;
	} else if ( ! $skip_hashes ) {
		if ( ! is_array( $args['hashes'] ) ) {
			if ( ! empty( $args['hashes'] ) ) {
				$args['hashes'] = array( $args['hashes'] );
			} else {
				$args['hashes'] = array();
			}
		}

		if ( ! $args['window'] || ! $entry['unchanged'] ) {
			// only hash files if the caller didn't specify a window to do that in, or if the file changed in the window
			foreach ( $args['hashes'] as $algo ) {
				if ( in_array( $algo, hash_algos(), true ) ) {
					$entry[ $algo ] = hash_file( $algo, $full_path );
				}
			}
		}
	}

	if ( ! empty( $entry['is_dir'] ) ) {
		// check if this is a WP directory
		if ( is_file( $full_path . '/wp-config.php' ) && is_dir( $full_path . '/wp-content' ) ) {
			$entry['is_wp_root'] = 1;
		}

		// check if this directory contains a donotbackup file
		if ( is_file( $full_path . '/.donotbackup' ) ) {
			$entry['do_not_backup'] = 1;
		}
	}

	return $entry;
}

function locale_safe_basename( $path ) {
	$parts = explode( '/', $path );
	$ret = $parts[ count( $parts ) - 1 ]; // last element
	if ( $ret === '' && count( $parts ) > 1 ) {
		// path ended with a slash; to match dirname() + basename() we need to return the last directory element, not blank
		// make sure we have another choice though
		$ret = $parts[ count( $parts ) - 2 ];
	}
	return $ret;
}

function action_check_file_existence( $args ) {
	$args = array_merge( array(
		'path'   => '/',
		'hashes' => array(),
	), $args );

	$path = localize_path( base64_decode( $args['path'] ) );

	if ( ! file_exists( $path ) ) {
		success_header();
		send_json_with_check( array(
			'found' => false,
		) );
		exit;
	}

	if ( ! $args['lstat'] ) {
		$args['stat'] = true;
	}
	$entry = get_ls_entry( $args, dirname( $path ), locale_safe_basename( $path ) );

	$output = array( 'found' => true );

	foreach ( $args['hashes'] as $algo ) {
			$output[ $algo ] = $entry[ $algo ];
	}

	success_header();
	send_json_with_check( $output, false );
	exit;
}

function action_stat( $args ) {
	$args = array_merge( array(
		'path'   => '/',
		'hashes' => array(),
		'window' => false,
		'lstat' => false,
	), $args );

	$path = localize_path( base64_decode( $args['path'] ) );

	if ( ! file_exists( $path ) ) {
		fatal_error( NOT_FOUND_ERROR, "File not found: {$path}" );
	}

	if ( ! $args['lstat'] ) {
		$args['stat'] = true;
	}
	$entry = get_ls_entry( $args, dirname( $path ), locale_safe_basename( $path ) );

	success_header();
	send_json_with_check( $entry, false );
	exit;
}

function delete_tree( $path ) {
	$entries_deleted = 1;

	if ( ! is_dir( $path ) ) {
		fatal_error( INVALID_TYPE_ERROR, 'Not a directory: ' . $path );
	}

	foreach ( scandir( $path ) as $name ) {
		if ( $name == '.' || $name == '..' ) {
			continue;
		}

		$child = $path . '/' . $name;
		if ( is_dir( $child ) ) {
			$entries_deleted += delete_tree( $child );
		} else {
			if ( ! @unlink( $child ) ) {
				fatal_error( WRITE_ERROR, "Failed to delete file: {$child}" );
			}
			$entries_deleted++;
		}
	}

	if ( ! @rmdir( $path ) ) {
		fatal_error( WRITE_ERROR, "Failed to delete folder: {$path}" );
	}

	return $entries_deleted;
}

function action_symlink( $args ) {
	if ( empty( $args['path'] ) ) {
		fatal_error( INVALID_TYPE_ERROR, 'Invalid path' );
	}

	if ( empty( $args['target'] ) ) {
		fatal_error( INVALID_TYPE_ERROR, 'Invalid target' );
	}

	$path = localize_path( base64_decode( $args['path'] ) ); // name of the symlink
	$target = localize_path( base64_decode( $args['target'] ) ); // where it points

	$ret = symlink( $target, $path );

	if ( ! $ret ) {
		fatal_error( WRITE_ERROR, 'Symlink failed' );
	}

	success_header();
	echo json_encode( array( 'ok' => true ) );
	exit;
}

function action_delete_tree( $args ) {
	if ( empty( $args['path'] ) ) {
		fatal_error( INVALID_TYPE_ERROR, 'Invalid path' );
	}

	$path = localize_path( base64_decode( $args['path'] ) );
	$entries_deleted = delete_tree( $path );

	success_header();
	send_json_with_check( array(
		'entries' => $entries_deleted,
	) );
	exit;
}

function action_count_files( $args ) {
	if ( empty( $args['path'] ) ) {
		fatal_error( COMMS_ERROR, 'Missing $args[\'path\']' );
	}

	$queue = [ localize_path( base64_decode( $args['path'] ) ) ];
	$result = 0;

	while ( count( $queue ) > 0 ) {
		$parent = array_shift( $queue );
		$dh = opendir( $parent );

		if ( ! $dh ) {
			// not fatal (like /filesystem/walk)
			continue;
		}

		while ( ( $child = readdir( $dh ) ) !== false ) {
			if ( '.' === $child || '..' === $child ) {
				continue;
			}

			$result++;
			$path = "$parent/$child";

			if ( is_dir( $path ) && ! is_link( $path ) ) {
				if ( ! in_array( $path, $queue ) ) {
					array_push( $queue, $path );
				}
			}
		}

		closedir( $dh );
	}

	success_header();
	send_json_with_check( [ 'count' => $result ], false );
	exit;
}

function action_ls( $args ) {
	$args = array_merge( array(
		'path'   => '/',
		'hashes' => array(),
		'stat'   => false,
		'window' => false,
		'include_special_dirs' => false,
	), $args );

	$path = localize_path( base64_decode( $args['path'] ) );

	if ( ! is_dir( $path ) ) {
		fatal_error( INVALID_TYPE_ERROR, "Not a directory: {$path}" );
	}

	$dh = opendir( $path );
	if ( ! $dh ) {
		fatal_error( READ_ERROR, "Failed to read directory: {$path}" );
	}

	success_header();
	while ( ( $file = readdir( $dh ) ) !== false ) {
		if ( ( '.' === $file || '..' === $file ) && ! $args['include_special_dirs'] ) {
			continue;
		}

		$entry = get_ls_entry( $args, $path, $file );

		send_json_with_check( $entry );
	}

	closedir( $dh );
	exit;
}

function action_grep( $args ) {
	$args = array_merge( array(
		'phrase' => '',
		'stat'   => false,
	), $args );

	$phrase = escapeshellarg( base64_decode( $args['phrase'] ) );
	$wp_path = get_wordpress_location() . '/*';
	$output = [];

	exec( "grep --recursive --files-with-matches --exclude-dir=jetpack-temp {$phrase} {$wp_path}", $output );

	if ( false === $output ) {
		fatal_error( READ_ERROR, "Failed to run grep" );
	}

	success_header();
	foreach ( $output as $file ) {
		$absolute_path = dirname( $file );
		$filename = basename( $file );

		$entry = get_ls_entry( $args, $absolute_path, $filename );

		send_json_with_check( $entry );
	}

	exit;
}

function action_walk( $args ) {
	global $is_cli;

	$args = array_merge( array(
		'root'              => '/',
		'paths'             => array(),
		'hashes'            => array(),
		'stat'              => false,
		'window'            => false,
		'skip_large_hashes' => false,
		'limit'             => 0,
		'offset'            => 0,
	), $args );

	$paths = array_map( 'base64_decode', $args['paths'] );
	$root = localize_path( base64_decode( $args['root'] ) );
	$soft_limit = 3000;

	// Use execution time to scale up soft time limit
	// It's possible for ini_get to return false
	$max_execution_time    = ini_get('max_execution_time') ? intval( ini_get('max_execution_time') ) : 0;
	$soft_time_window_base = 7;
	$soft_time_window      = $max_execution_time > 30 ? floor( $max_execution_time / 30 * $soft_time_window_base ) : $soft_time_window_base;
	$soft_time_limit       = time() + 7;
	$entries               = 0;
	$first_path            = true;
	success_header();

	// TODO: Rename this through the stack to be more clear.  This deals more with aggregating file sizes
	// and we have a simiarly named client callback that works differently and is unrelated.  We also
	// $large_file_threshold that actually skips individual large file hashes.
	$skip_large_hashes = $args['skip_large_hashes'];

	// Theshold of files considered "small"; 500kb.
	// Files smaller than this are not affected by the hash limit.
	$small_file_threshold = 500 * 1024;

	// Threshold of files considered "large"; 200MB
	// This is used to explicitly exclude large files and have them individually hashed
	// so they won't impact walk limits.  Should be based on the larger 1% of files but
	// 200MB was the initial guess, can be adjusted when we have more data.
	// This is checked against regardless of $skip_large_hashes, which applies to total
	// accumulated file hashes.
	$large_file_threshold = 200 * 1024 * 1024;

	// Track how much data to hash before giving up on non-small-files.
	// CLI can have much more generous timeouts, as its executed over SSH.
	if ( $is_cli ) {
		$hash_limit = 1 * 1024 * 1024 * 1024; // 1 GB
	} else {
		$hash_limit = 200 * 1024 * 1024; // 200 MB
	}

	if ( substr( $root, -1 ) != '/' ) {
		$root .= '/';
	}

	while ( count( $paths ) > 0 && ( $first_path || time() < $soft_time_limit ) ) {
		$relative_path = array_shift( $paths );
		$absolute_path = $root . $relative_path;

		// Fetch information about the path, prepare a header for it.
		$path_details = get_ls_entry( $args, dirname( $absolute_path ), locale_safe_basename( $absolute_path ) );
		$path_details['ls'] = clean_pathname_string( $relative_path );
		$path_header = encode_json_with_check( $path_details ) . "\n";

		$dh = opendir( $absolute_path );
		if ( ! $dh ) {
			echo $path_header . json_encode( array( 'error' => 'Failed to read ' . $absolute_path ) ) . "\n";
			continue;
		}

		// Sort files for pagination
		$files = array();
		while ( false !== ( $file = readdir( $dh ) ) ) {
			if ( '.' === $file || '..' === $file ) {
				continue;
			}

			array_push( $files, $file );
		}

		sort($files);
		closedir( $dh );

		// Apply limits to first directory only, additional directories will be limited by soft_limits
		// Default limit of 0 lists all files
		if ( $first_path ) {
			$start = intval( $args['offset'] );
			if ( 0 === intval( $args['limit'] ) ) {
				$end = count( $files );
			} else {
				$end = min( $start + intval( $args['limit'] ), count( $files ) );
			}
		} else {
			$start = 0;
			$end = count( $files );
		}

		// Send a header for the first path after successfully reading an entry.
		// Don't send if we're not at the start of pagination
		// Prevents masking errors behind successful-looking headers.
		if ( $first_path && 0 === $start && ! empty( $path_header ) ) {
			echo $path_header;
			$path_header = '';
		}

		for ( $i = $start; $i < $end; $i++ ) {
			$file = $files[ $i ];

			// Figure out if this file should not be hashed (ie; if it's too big).
			$size = filesize( $absolute_path . '/' . $file );

			$skip_hash = (
				( $skip_large_hashes && $size > $small_file_threshold && $hash_limit < $size ) ||
				$size > $large_file_threshold
			);
			if ( ! $skip_hash && $hash_limit > 0 ) {
				$hash_limit -= $size;
			}

			// Apply soft-limits on all but the first path (including bailing if we hit the hash limit)
			$entries++;
			if ( ! $first_path && ( $hash_limit < 0 || $entries > $soft_limit || time() > $soft_time_limit ) ) {
				closedir( $dh );
				$entry_buffer = array();
				flush();
				return;
			}

			$entry = get_ls_entry( $args, $absolute_path, $file, $skip_hash );

			if ( $skip_hash && ! empty( $args[ 'hashes' ] ) && empty( $entry['unchanged' ] ) ) {
				$entry['hash_skipped'] = 1;
			}

			// Keep track of paths to auto-recurse into
			// not symlinks or unreadable dirs
			$is_readable = ! ( isset( $entry['unreadable'] ) && $entry['unreadable'] );
			if ( ( isset( $entry['is_dir'] ) && $entry['is_dir'] ) && $is_readable && ( isset( $entry['is_link'] ) && ! $entry['is_link'] ) && count( $paths ) < 1000 && $entries < $soft_limit ) {
				// Do not track into .donotbackup folders.
				if ( empty( $entry['do_not_backup'] ) ) {
					$explore_path = empty( $relative_path ) ? $file : $relative_path . '/' . $file;
					if ( ! in_array( $explore_path, $paths ) ) {
						array_push( $paths, $explore_path );
					}
				}
			}

			// Buffer all but the first path, to help enforce soft-limits
			if ( $first_path ) {
				send_json_with_check( $entry, true, false );
			} else{
				array_push( $entry_buffer, $entry );
			}
		}

		// If buffering, output entry buffer; finished a directory before hitting a soft-limit.
		if ( ! $first_path ) {
			echo $path_header;
			foreach ( $entry_buffer as $entry ) {
				send_json_with_check( $entry, true, false );
			}
		}

		// End of directory or just end of batch?
		$end_of_batch = false;
		if ( $end === count( $files ) ) {
			send_json_with_check( [ 'eod' => true ], true, false );
		} else {
			$end_of_batch = true;
			send_json_with_check( [ 'next' => $end ], true, false );
		}

		// Send a footer at the bottom of each directory to confirm it is complete.
		flush();

		$first_path = false;
		$entry_buffer = array();

		if ( $entries > $soft_limit || $end_of_batch ) {
			exit;
		}
	}
}

function action_cleanup_restore( $args ) {
	$files   = glob( localize_path( '{$ABSPATH}/vp-sql-upload-*.sql' ) );
	$deleted = 0;

	foreach ( $files as $file ) {
		if ( @unlink( $file ) ) {
			$deleted++;
		}
	}

	success_header();
	echo json_encode( array(
		'found'   => count( $files ),
		'deleted' => $deleted,
	) );
}

function action_cleanup_helpers( $args ) {
	$args = array_merge( array(
		'ageThreshold' => 21600, // 6 hours
	), $args );

	$dir = opendir( __DIR__ );
	if ( ! is_resource( $dir ) ) {
		fatal_error( READ_ERROR, 'Failed to open directory: ' . __DIR__ );
	}

	$self = realpath( __FILE__ );

	// Find leftover old helpers and delete them.
	$helpers_deleted = 0;
	$helpers_found = 0;
	while ( false !== ( $entry = readdir( $dir ) ) ) {
		// Skip files that don't look like helpers.
		if ( 0 != strncmp( $entry, 'jp-helper-', 10 ) ) {
			continue;
		}

		$helpers_found++;
		$full_path = realpath( implode( '/', array( __DIR__, $entry ) ) );

		// Skip entries that aren't files, or are myself.
		if ( $full_path == $self || ! is_file( $full_path ) ) {
			continue;
		}

		// Only delete helpers over the threshold
		$age = time() - filemtime( $full_path );
		if ( $age < $args['ageThreshold'] ) {
			continue;
		}

		// Check file header
		if ( file_get_contents( $full_path, false, NULL, 0, 40 ) !== '<?php /* Jetpack Backup Helper Script */' ) {
			continue;
		}

		// Finally delete.
		$helpers_deleted++;
		unlink( $full_path );
	}

	success_header();
	echo json_encode( array(
		'found'   => $helpers_found,
		'deleted' => $helpers_deleted,
	 ) );
}

function action_get_active_theme() {
	load_wp();

	$theme = wp_get_theme();

	if ( $theme ) {
		success_header();
		echo json_encode( array(
			'slug' => $theme->get_template(),
			'path' => $theme->get_theme_root(),
		) );
	} else {
		fatal_error( READ_ERROR, 'wp_get_theme() failed' );
	}
}

function action_validate_theme() {
	// Forces a theme switch if necessary (we may have deleted active theme).
	load_wp( true );
	$theme_validated = validate_current_theme();

	success_header();
	send_json_with_check( array(
		'theme_validated' => $theme_validated,
	) );
	exit;
}

function action_woocommerce_install() {
	load_wp( true );

	global $wpdb;

	success_header();

	if ( class_exists( 'WC_Install' ) && method_exists( 'WC_Install', 'install' ) ) {
		$sql = sprintf( '
			ALTER TABLE `vp_backup_%swc_download_log`
			DROP FOREIGN KEY `fk_%swc_download_log_permission_id`',
			$wpdb->prefix,
			$wpdb->prefix
		);

		$wpdb->query( $sql );

		WC_Install::install();

		echo json_encode( array( 'installed' => true, 'sql' => $sql ) );
	} else {
		echo json_encode( array( 'installed' => false, 'message' => 'woocommerce not available' ) );
	}
}

function action_get_file( $args ) {
	$args = array_merge( array(
		'path'              => null,
		'previous_attempts' => [],
	), $args );

	if ( empty( $args['path'] ) ) {
		fatal_error( COMMS_ERROR, 'Invalid args', 400 );
	}

	$path = localize_path( base64_decode( $args['path'] ) );

	if ( ! file_exists( $path ) ) {
		fatal_error( NOT_FOUND_ERROR, 'File not found: ' . $args['path'] );
	}

	$handle = fopen( $path, 'r' );
	if ( ! $handle ) {
		fatal_error( READ_ERROR, 'Unable to open file' );
	}

	// Fast forward past previous attempts, checking their hashes match.
	foreach ( $args['previous_attempts'] as $attempt ) {
		fast_forward_handle( $handle, intval( $attempt->size ), $attempt->hash );
	}

	$filesize = filesize( $path );

	success_header();
	header( 'Content-Length: ' . ( $filesize - ftell( $handle ) + 1 ) );
	header( 'x-file-size: ' . $filesize );

	// Send an extra prepended byte to avoid WAFs/reverse proxies from treating the HTTP body as a truthy value.
	echo 'b';

	$result = pass_through( $handle );
	if ( false === $result ) {
		fatal_error( READ_ERROR, 'File read error' );
	}

	exit;
}

function action_enable_jetpack_sso( $args ) {
	load_wp( true );

	if ( ! \Jetpack::is_module_active( 'sso' ) ) {
		\Jetpack::activate_module( 'sso', false, false );
		if ( ! \Jetpack::is_module_active( 'sso' ) ) {
			fatal_error( WRITE_ERROR, 'Failed to activate Jetpack SSO module.' );
		}
	}

	if ( intval( get_option( 'jetpack_sso_match_by_email' ) ) !== 1 ) {
		if ( ! update_option( 'jetpack_sso_match_by_email', 1 ) ) {
			fatal_error( WRITE_ERROR, 'Failed to set Jetpack SSO to match by email.' );
		}
	}

	success_header();
	echo json_encode( [
		'ok' => true,
	] );
}

function action_transfer_jetpack_connection( $args ) {
	global $wpdb;

	// Ensure required args have been specified
	if ( empty( $args['imported_prefix'] ) || empty( $args['master_user_id'] ) ) {
		fatal_error( COMMS_ERROR, 'Imported prefix and new master_user_id required to transfer Jetpack connection' );
	}
	$imported_prefix = $args['imported_prefix'];
	$new_master_user_id = intval( $args['master_user_id'] );

	load_wp();

	// Verify specified master_user_id is present and an administrator.
	$get_roles_query = $wpdb->prepare( "select meta_value from `{$imported_prefix}usermeta` where user_id = %d and meta_key like %s limit 1;", $new_master_user_id, '%capabilities' );
	$new_master_roles_serialized = $wpdb->get_var( $get_roles_query );
	if ( empty( $new_master_roles_serialized ) ) {
		fatal_error( NOT_FOUND_ERROR, 'Unable to determine roles for new master user' );
	}

	$new_master_roles = maybe_unserialize( $new_master_roles_serialized );
	if ( ! is_array( $new_master_roles ) || ! in_array( 'administrator', array_keys( $new_master_roles ) ) ) {
		fatal_error( COMMS_ERROR, 'Specified master_user_id does not have the administrator role' );
	}

	// Gather current Jetpack settings together for import.
	$jetpack_options = get_option( 'jetpack_options' );
	$jetpack_private_options = get_option( 'jetpack_private_options' );
	if ( ! is_array( $jetpack_options ) || ! is_array( $jetpack_private_options ) || empty( $jetpack_options['master_user'] ) ) {
		fatal_error( NOT_FOUND_ERROR, 'Jetpack Options not found for connection transfer' );
	}
	$old_master_user_id = intval( $jetpack_options['master_user'] );

	// Modify Jetpack Options to reflect new master_user_id.
	if ( $old_master_user_id !== $new_master_user_id ) {
		// Update master user in Jetpack Options and user tokens, if they have changed.
		$jetpack_options['master_user'] = $new_master_user_id;

		// Replace the master user id in the tokens array.
		if ( empty( $jetpack_private_options['user_tokens'][ $old_master_user_id ] ) ) {
			fatal_error( NOT_FOUND_ERROR, 'Master User tokens not found in Jetpack Private Options' );
		}

		$old_token = $jetpack_private_options['user_tokens'][ $old_master_user_id ];
		$new_token = replace_user_id_in_user_token( $old_token, $new_master_user_id );
		$jetpack_private_options['user_tokens'] = [ $new_master_user_id => $new_token ];
	}

	// Write options to the newly imported tables.
	$wpdb->update( $imported_prefix . 'options', [ 'option_value' => serialize( $jetpack_options ) ], [ 'option_name' => 'jetpack_options' ] );
	$wpdb->update( $imported_prefix . 'options', [ 'option_value' => serialize( $jetpack_private_options ) ], [ 'option_name' => 'jetpack_private_options' ] );

	success_header();
	echo json_encode( [
		'old_master_user_id' => $old_master_user_id,
		'new_master_user_id' => $new_master_user_id,
	] );
}

function fast_forward_handle( $handle, $size, $md5 ) {
	$ctx = hash_init( 'md5' );
	$read = 0;

	while ( $read < $size ) {
		$data = fread( $handle, min( 2048, $size - $read ) );
		if ( false == $data ) {
			fatal_error( READ_ERROR, 'Unable to read to fast forward point' );
		}

		$read += strlen( $data );
		hash_update( $ctx, $data );
	}

	$hash = hash_final( $ctx );
	if ( $hash !== $md5 ) {
		fatal_error( READ_ERROR, 'Hash mis-match while fast-forwarding file handle' );
	}
}

function pass_through( $handle ) {
	while ( ! feof( $handle ) ) {
		$data = fread( $handle, 2048 );
		if ( false === $data ) {
			return false;
		}

		print $data;
	}

	return true;
}

function replace_user_id_in_user_token( $user_token, $new_user_id ) {
	$user_token_without_suffix = strip_user_id_from_user_token( $user_token );
	return add_user_id_to_user_token( $user_token_without_suffix, $new_user_id  );
}

function strip_user_id_from_user_token( $user_token ) {
	$pos_of_last_period = strrpos( $user_token, '.' );
	return substr( $user_token, 0, $pos_of_last_period );
}

function add_user_id_to_user_token( $user_token_without_suffix, $user_id ) {
	return $user_token_without_suffix . '.' . $user_id;
}

function get_plugin_path_from_slug( $slug ) {
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugins = get_plugins();

	if ( strstr( $slug, '/' ) ) {
		// The slug is already a plugin path.
		return $slug;
	}

	foreach ( $plugins as $plugin_path => $data ) {
		$path_parts = explode( '/', $plugin_path );
		if ( $path_parts[0] === $slug ) {
			return $plugin_path;
		}
	}

	return false;
}
