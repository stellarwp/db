<?php
namespace StellarWP\DB;

use StellarWP\DB\Database\Exceptions\DatabaseQueryException;
use StellarWP\DB\Tests\DBTestCase;
use StellarWP\DB\Tests\InvalidDatabaseQueryException;
use StellarWP\DB\Tests\ValidDatabaseQueryException;

class ConfigTest extends DBTestCase {
	public function setUp() {
		// before
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
		Config::reset();
	}

	/**
	 * @test
	 */
	public function should_set_hook_prefix() {
		Config::setHookPrefix( 'bork' );

		$this->assertEquals( 'bork', Config::getHookPrefix() );
	}

	/**
	 * @test
	 */
	public function should_set_exception_when_exception_is_valid() {
		Config::setDatabaseQueryException( ValidDatabaseQueryException::class );

		$this->assertEquals( ValidDatabaseQueryException::class, Config::getDatabaseQueryException() );
	}

	/**
	 * @test
	 */
	public function should_not_set_exception_when_exception_is_invalid() {

		try {
			Config::setDatabaseQueryException( InvalidDatabaseQueryException::class );
		} catch ( \Exception $e ) {
			$this->assertEquals( \InvalidArgumentException::class, get_class( $e ) );
		}

		$this->assertEquals( DatabaseQueryException::class, Config::getDatabaseQueryException() );
	}
}
