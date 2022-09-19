<?php
namespace StellarWP\DB;

use StellarWP\DB\Database\Exceptions\DatabaseQueryException;
use StellarWP\DB\Tests\DBTestCase;
use StellarWP\DB\Tests\InvalidDatabaseQueryException;
use StellarWP\DB\Tests\ValidDatabaseQueryException;

class DBTest extends DBTestCase {
	public function setUp() {
		// before
		parent::setUp();

		// Ensure config is nice and fresh each test.
		Config::setConfigComponents( null );
	}

	public function tearDown() {
		parent::tearDown();

		// Ensure config is nice and fresh after each test.
		Config::setConfigComponents( null );
	}

	public function callWithPrefix() {
		return [
			[
				'method' => 'get_row',
				'prefix' => '',
			],
			[
				'method' => 'get_col',
				'prefix' => '',
			],
			[
				'method' => 'get_results',
				'prefix' => '',
			],
			[
				'method' => 'query',
				'prefix' => '',
			],
			[
				'method' => 'get_row',
				'prefix' => 'bork',
			],
			[
				'method' => 'get_col',
				'prefix' => 'bork',
			],
			[
				'method' => 'get_results',
				'prefix' => 'bork',
			],
			[
				'method' => 'query',
				'prefix' => 'bork',
			],
		];
	}

	/**
	 * @dataProvider callWithPrefix
	 * @test
	 */
	public function should_hook_action_on_method_call( $method, $prefix ) {
		Config::setHookPrefix( $prefix );
		$called_prefix = null;
		$called = false;
		$action = static function( $args, $hook_prefix ) use ( &$called, &$called_prefix ) {
			$called = true;
			$called_prefix = $hook_prefix;
		};

		add_action( 'stellarwp_db_pre_query', $action, 10, 2 );

		DB::$method( "SELECT 1" );

		$this->assertTrue( $called );
		$this->assertEquals( $prefix, $called_prefix );

		remove_action( 'stellarwp_db_pre_query', $action, 10, 2 );
	}

	/**
	 * @test
	 */
	public function should_throw_exception_on_query_error() {
		$this->expectException( DatabaseQueryException::class );

		DB::query( "SELECT * FROM bork" );
	}

	/**
	 * @test
	 */
	public function should_throw_custom_exception_on_query_error() {
		Config::setDatabaseQueryException( ValidDatabaseQueryException::class );
		$this->expectException( ValidDatabaseQueryException::class );

		DB::query( "SELECT * FROM bork" );
	}
}
