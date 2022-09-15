<?php

namespace StellarWP\DB;

use StellarWP\DB\Database\Exceptions\DatabaseQueryException;

class Config {
	/**
	 * @var string
	 */
	private static $databaseQueryException = DatabaseQueryException::class;

	/**
	 * @var string
	 */
	private static $hookPrefix = '';

	/**
	 * Gets the DatabaseQueryException class.
	 *
	 * @return string
	 */
	public static function getDatabaseQueryException(): string {
		return self::$databaseQueryException;
	}

	/**
	 * Gets the hook prefix.
	 *
	 * @return string
	 */
	public static function getHookPrefix(): string {
		return self::$hookPrefix;
	}

	/**
	 * Sets the DatabaseQueryException class.
	 *
	 * @param string $class Class name of the DatabaseQueryException to use.
	 *
	 * @return void
	 */
	public static function setDatabaseQueryException( string $class ) {
		if ( ! is_a( $class, DatabaseQueryException::class, true ) ) {
			throw new \InvalidArgumentException( 'The provided DatabaseQueryException class must be or must extend ' . __NAMESPACE__ . '\Database\Exceptions\DatabaseQueryException.' );
		}

		self::$databaseQueryException = $class;
	}

	/**
	 * Sets the hook prefix.
	 *
	 * @param string $prefix The prefix to add to hooks.
	 *
	 * @return void
	 */
	public static function setHookPrefix( string $prefix ) {
		self::$hookPrefix = $prefix;
	}
}
