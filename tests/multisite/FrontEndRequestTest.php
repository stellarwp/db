<?php
namespace StellarWP\DB;

use StellarWP\DB\Tests\DBTestCase;

class FrontEndRequestTest extends DBTestCase {
	/**
	 * Runs the fixture script in its own PHP process, on this suite's multisite network, and returns its decoded output.
	 *
	 * @param string $mode  Either `query` or `delta`.
	 * @param string $table Table name, without prefix, for the `delta` mode.
	 *
	 * @return array{is_multisite: bool, upgrade_loaded_before: bool, upgrade_loaded_after: bool, table_exists: bool|null}
	 */
	private function run_in_front_end_request( $mode, $table = '' ) {
		global $table_prefix;

		$network = get_network();
		$env     = [
			'WORDPRESS_DB_HOST'      => DB_HOST,
			'WORDPRESS_DB_NAME'      => DB_NAME,
			'WORDPRESS_DB_USER'      => DB_USER,
			'WORDPRESS_DB_PASSWORD'  => DB_PASSWORD,
			'WORDPRESS_TABLE_PREFIX' => $table_prefix,
			'WORDPRESS_CONFIG_EXTRA' => sprintf(
				"define( 'MULTISITE', true ); define( 'SUBDOMAIN_INSTALL', false ); define( 'DOMAIN_CURRENT_SITE', %s ); define( 'PATH_CURRENT_SITE', %s ); define( 'SITE_ID_CURRENT_SITE', %d ); define( 'BLOG_ID_CURRENT_SITE', %d );",
				var_export( $network->domain, true ),
				var_export( $network->path, true ),
				$network->id,
				get_main_site_id()
			),
		];

		$command = '';
		foreach ( $env as $name => $value ) {
			$command .= $name . '=' . escapeshellarg( $value ) . ' ';
		}
		$command .= implode(
			' ',
			array_map(
				'escapeshellarg',
				[ PHP_BINARY, codecept_data_dir( 'run-in-front-end-request.php' ), ABSPATH, $network->domain, $mode, $table ]
			)
		);

		exec( $command . ' 2>&1', $output, $exit_code );
		$output = implode( "\n", $output );

		$this->assertSame( 0, $exit_code, $output );

		$result = json_decode( substr( $output, strrpos( $output, '{' ) ), true );

		$this->assertIsArray( $result, $output );
		$this->assertTrue( $result['is_multisite'], 'The fixture must load WordPress as multisite.' );
		$this->assertFalse( $result['upgrade_loaded_before'], 'The fixture must start without upgrade.php loaded.' );

		return $result;
	}

	/**
	 * @test
	 */
	public function should_not_fatal_when_ms_php_is_required_after_a_query() {
		$result = $this->run_in_front_end_request( 'query' );

		$this->assertFalse( $result['upgrade_loaded_after'] );
	}

	/**
	 * @test
	 */
	public function should_create_a_table_with_delta_when_upgrade_php_is_not_loaded() {
		$result = $this->run_in_front_end_request( 'delta', 'stellarwp_db_delta_test_' . wp_generate_password( 6, false ) );

		$this->assertTrue( $result['upgrade_loaded_after'] );
		$this->assertTrue( $result['table_exists'] );
	}
}
