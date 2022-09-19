<?php

namespace StellarWP\DB;

/**
 * @method static string getDatabaseQueryException()
 * @method static string getHookPrefix()
 * @method static void setDatabaseQueryException(string $class)
 * @method static void setHookPrefix(string $prefix)
 */
class Config {
	/**
	 * @var Contracts\ConfigComponents
	 */
	protected static $configComponents;

	/**
	 * Sets the ConfigComponents for this class.
	 *
	 * @param Contracts\ConfigComponents|null $configComponents Config components.
	 */
	public static function setConfigComponents( ?Contracts\ConfigComponents $configComponents = null ) {
		static::$configComponents = $configComponents;
	}

	/**
	 * Magic method which calls the methods from an instance.
	 *
	 * @since 1.0.1
	 *
	 * @param string $name Name of method being called statically.
	 * @param array $arguments Arguments passed to the method.
	 *
	 * @return mixed
	 */
	public static function __callStatic( string $name, array $arguments ) {
		if ( static::$configComponents === null ) {
			static::$configComponents = new ConfigComponents();
		}

		return static::$configComponents->$name( ...$arguments );
	}
}
