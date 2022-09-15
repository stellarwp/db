<?php

namespace StellarWP\DB;

use Database\Exceptions\DatabaseQueryException;

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
	public static function getDatabaseQueryException() {
		return static::$databaseQueryException;
	}

	/**
	 * Gets the hook prefix.
	 *
	 * @return string
	 */
	public static function getHookPrefix() {
		return static::$hookPrefix;
	}

	/**
	 * Sets the DatabaseQueryException class.
	 *
	 * @param string $class Class name of the DatabaseQueryException to use.
	 *
	 * @return void
	 */
	public static function setDatabaseQueryException( string $class ) {
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
