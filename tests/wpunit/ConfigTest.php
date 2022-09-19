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

		// Ensure config is nice and fresh each test.
		Config::setConfigComponents( new ConfigComponents() );
	}

	public function tearDown() {
		parent::tearDown();

		// Ensure config is nice and fresh after each test.
		Config::setConfigComponents( new ConfigComponents() );
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

	/**
	 * @test
	 */
	public function should_be_extendable() {
		$class = new class extends ConfigComponents {
			public function getDatabaseQueryException() : string {
				return ValidDatabaseQueryException::class;
			}

			public function getHookPrefix() : string {
				return 'bork';
			}
		};

		Config::setConfigComponents( $class );

		$this->assertEquals( 'bork', Config::getHookPrefix() );
		$this->assertEquals( ValidDatabaseQueryException::class, Config::getDatabaseQueryException() );
	}

	/**
	 * @test
	 */
	public function should_be_mockable() {
		$class = new class implements Contracts\ConfigComponents {
			public function getDatabaseQueryException() : string {
				return ValidDatabaseQueryException::class;
			}

			public function getHookPrefix() : string {
				return 'bork';
			}

			public function setDatabaseQueryException( string $class ) {}

			public function setHookPrefix( string $prefix ) {}
		};

		Config::setConfigComponents( $class );

		$this->assertEquals( 'bork', Config::getHookPrefix() );
		$this->assertEquals( ValidDatabaseQueryException::class, Config::getDatabaseQueryException() );
	}

	/**
	 * @test
	 */
	public function should_be_able_to_null_components() {
		Config::setConfigComponents( null );

		$this->assertEquals( '', Config::getHookPrefix() );
		$this->assertEquals( DatabaseQueryException::class, Config::getDatabaseQueryException() );
	}
}
