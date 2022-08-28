<?php
$slic = getenv( 'STELLAR_SLIC' );

// Paths
$constants = [
	'WP_CONTENT_DIR' => getenv( 'WP_ROOT_FOLDER' ) . '/wp-content',
	'ABSPATH'        => getenv( 'WP_ROOT_FOLDER' ) . '/',
];

foreach ( $constants as $key => $value ) {
	if ( defined( $key ) ) {
		continue;
	}

	define( $key, $value );
}
