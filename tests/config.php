<?php
$travis = getenv( 'TRAVIS' );

// Don't override config vars if running on Travis-CI
if ( ! empty( $travis ) ) {
	return;
}

$slic = getenv( 'STELLAR_SLIC' );

// Set up differently if running in slic
if ( ! empty( $slic ) ) {
	// Paths
	$constants = [
		'WP_CONTENT_DIR' => getenv( 'WP_ROOT_FOLDER' ) . '/wp-content',
		'ABSPATH'        => getenv( 'WP_ROOT_FOLDER' ) . '/',
	];
} else {
	// Paths
	$constants = [
		'WP_CONTENT_DIR' => dirname( __FILE__ ) . '/../../../../wp-content',
		'ABSPATH'        => dirname( __FILE__ ) . '/../../../../wp',
	];
}

foreach ( $constants as $key => $value ) {
	if ( defined( $key ) ) {
		continue;
	}

	define( $key, $value );
}
