<?php

namespace StellarWP\DB;

use StellarWP\DB\Database\Exceptions\DatabaseQueryException;

/**
 * @method static string getDatabaseQueryException()
 * @method static string getHookPrefix()
 * @method static void setDatabaseQueryException(string $class)
 * @method static void setHookPrefix(string $prefix)
 */
class Config {
	/**
	 * Config instance.
	 *
	 * @var static
	 */
	protected static $instance;

	/**
	 * @var string
	 */
	protected $databaseQueryException = DatabaseQueryException::class;

	/**
	 * @var string
	 */
	protected $hookPrefix = '';

	/**
	 * Get an instance of this class.
	 *
	 * @param static $instance
	 *
	 * @return static
	 */
	public static function instance( $instance = null ) {

		if ( $instance ) {
			if ( ! $instance instanceof static ) {
				throw new \InvalidArgumentException( 'The provided instance must be or must be a child of ' . static::class . '.' );
			}

			static::$instance = $instance;
		} elseif ( static::$instance === null ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Constructor.
	 */
	final public function __construct() {}

	/**
	 * Gets the DatabaseQueryException class.
	 *
	 * @return string
	 */
	protected function getDatabaseQueryException(): string {
		return $this->databaseQueryException;
	}

	/**
	 * Gets the hook prefix.
	 *
	 * @return string
	 */
	protected function getHookPrefix(): string {
		return $this->hookPrefix;
	}

	/**
	 * Sets the DatabaseQueryException class.
	 *
	 * @param string $class Class name of the DatabaseQueryException to use.
	 *
	 * @return void
	 */
	protected function setDatabaseQueryException( string $class ) {
		if ( ! is_a( $class, DatabaseQueryException::class, true ) ) {
			throw new \InvalidArgumentException( 'The provided DatabaseQueryException class must be or must extend ' . DatabaseQueryException::class . '.' );
		}

		$this->databaseQueryException = $class;
	}

	/**
	 * Sets the hook prefix.
	 *
	 * @param string $prefix The prefix to add to hooks.
	 *
	 * @return void
	 */
	protected function setHookPrefix( string $prefix ) {
		$this->hookPrefix = $prefix;
	}

	/**
	 * Magic method which calls the methods from an instance.
	 *
	 * @since 1.0.1
	 *
	 * @param string $name Name of method being called statically.
	 * @param mixed $arguments Arguments passed to the method.
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		return static::instance()->$name( ...$arguments );
	}
}
