<?php
/**
 * Loads WordPress the way a front-end request does, runs one DB call and prints the result as JSON.
 *
 * Run as a separate process because the wpunit bootstrap loads every wp-admin/includes file up front.
 *
 * Usage: php run-in-front-end-request.php <abspath> <query|delta> [table]
 */

list( , $abspath, $mode ) = $argv;
$table = isset( $argv[3] ) ? $argv[3] : '';

require dirname( __DIR__, 2 ) . '/vendor/autoload.php';
require rtrim( $abspath, '/' ) . '/wp-load.php';

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

echo json_encode(
	[
		'upgrade_loaded_before' => $upgrade_loaded_before,
		'upgrade_loaded_after'  => in_array( rtrim( $abspath, '/' ) . '/wp-admin/includes/upgrade.php', get_included_files(), true ),
		'table_exists'          => $table_exists,
	]
);
