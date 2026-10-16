<?php
namespace StellarWP\DB;

use StellarWP\DB\Tests\DBTestCase;

class FrontEndRequestTest extends DBTestCase {
	/**
	 * Runs the fixture script in its own PHP process and returns its decoded output.
	 *
	 * @param string $mode  Either `query` or `delta`.
	 * @param string $table Table name, without prefix, for the `delta` mode.
	 *
	 * @return array{upgrade_loaded_before: bool, upgrade_loaded_after: bool, table_exists: bool|null}
	 */
	private function run_in_front_end_request( $mode, $table = '' ) {
		$command = implode(
			' ',
			array_map(
				'escapeshellarg',
				[ PHP_BINARY, codecept_data_dir( 'run-in-front-end-request.php' ), ABSPATH, $mode, $table ]
			)
		);

		exec( $command . ' 2>&1', $output, $exit_code );
		$output = implode( "\n", $output );

		$this->assertSame( 0, $exit_code, $output );

		$result = json_decode( substr( $output, strrpos( $output, '{' ) ), true );

		$this->assertIsArray( $result, $output );

		return $result;
	}

	/**
	 * @test
	 */
	public function should_not_load_upgrade_php_when_running_a_query() {
		$result = $this->run_in_front_end_request( 'query' );

		$this->assertFalse( $result['upgrade_loaded_before'], 'The fixture must start without upgrade.php loaded.' );
		$this->assertFalse( $result['upgrade_loaded_after'] );
	}

	/**
	 * @test
	 */
	public function should_create_a_table_with_delta_when_upgrade_php_is_not_loaded() {
		$result = $this->run_in_front_end_request( 'delta', 'stellarwp_db_delta_test_' . wp_generate_password( 6, false ) );

		$this->assertFalse( $result['upgrade_loaded_before'], 'The fixture must start without upgrade.php loaded.' );
		$this->assertTrue( $result['upgrade_loaded_after'] );
		$this->assertTrue( $result['table_exists'] );
	}
}
