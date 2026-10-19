<?php
/**
 * Loads WordPress the way a front-end request does, runs one DB call and prints the result as JSON.
 *
 * Run as a separate process because the test bootstrap loads every wp-admin/includes file up front.
 * The database and multisite settings come from the environment, read by the slic `wp-config.php`.
 *
 * In `query` mode it then requires `wp-admin/includes/ms.php` the way other plugins and themes do. That
 * fatals with "Cannot redeclare check_upload_size()" when the query already loaded `ms.php` through
 * `upgrade.php`, so the JSON is printed only if the request survives.
 *
 * Usage: php run-in-front-end-request.php <abspath> <host> <query|delta> [table]
 */

list( , $abspath, $host, $mode ) = $argv;
$abspath = rtrim( $abspath, '/' );
$table   = isset( $argv[4] ) ? $argv[4] : '';

// ms-settings.php picks the site from the request.
$_SERVER['HTTP_HOST']   = $host;
$_SERVER['REQUEST_URI'] = '/';

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require $abspath . '/wp-load.php';

use StellarWP\DB\DB;

DB::init();

$upgrade_loaded_before = function_exists( 'dbDelta' );

if ( 'delta' === $mode ) {
	global $wpdb;
	$table_name = $wpdb->prefix . $table;
	DB::delta( "CREATE TABLE {$table_name} ( id bigint(20) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY  (id) ) {$wpdb->get_charset_collate()};" );
	$table_exists = $table_name === DB::get_var( DB::prepare( 'SHOW TABLES LIKE %s', $table_name ) );
	DB::query( "DROP TABLE IF EXISTS {$table_name}" );
} else {
	DB::get_var( 'SELECT 1' );
	$table_exists = null;
}

$upgrade_loaded_after = in_array( $abspath . '/wp-admin/includes/upgrade.php', get_included_files(), true );

if ( 'query' === $mode ) {
	require $abspath . '/wp-admin/includes/ms.php';
}

echo json_encode(
	[
		'is_multisite'          => is_multisite(),
		'upgrade_loaded_before' => $upgrade_loaded_before,
		'upgrade_loaded_after'  => $upgrade_loaded_after,
		'table_exists'          => $table_exists,
	]
);
