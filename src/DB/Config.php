<?php

namespace StellarWP\DB;

use StellarWP\DB\Database\Exceptions\DatabaseQueryException;

class Config {
	/**
	 * @var string
	 */
	protected static $databaseQueryException = DatabaseQueryException::class;

	/**
	 * @var string
	 */
	protected static $hookPrefix = '';

	/**
	 * Gets the DatabaseQueryException class.
	 *
	 * @return string
	 */
	public static function getDatabaseQueryException(): string {
		return static::$databaseQueryException;
	}

	/**
	 * Gets the hook prefix.
	 *
	 * @return string
	 */
	public static function getHookPrefix(): string {
		return static::$hookPrefix;
	}

	/**
	 * Resets this class back to the defaults.
	 */
	public static function reset() {
		static::$hookPrefix             = '';
		static::$databaseQueryException = DatabaseQueryException::class;
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
			throw new \InvalidArgumentException( 'The provided DatabaseQueryException class must be or must extend ' . DatabaseQueryException::class . '.' );
		}

		static::$databaseQueryException = $class;
	}

	/**
	 * Sets the hook prefix.
	 *
	 * @param string $prefix The prefix to add to hooks.
	 *
	 * @return void
	 */
	public static function setHookPrefix( string $prefix ) {
		static::$hookPrefix = $prefix;
	}
}
