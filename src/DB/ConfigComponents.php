<?php

namespace StellarWP\DB;

use StellarWP\DB\Contracts;
use StellarWP\DB\Database\Exceptions\DatabaseQueryException;

class ConfigComponents implements Contracts\ConfigComponents {
	/**
	 * @var string
	 */
	protected $databaseQueryException = DatabaseQueryException::class;

	/**
	 * @var string
	 */
	protected $hookPrefix = '';

	/**
	 * @inheritDoc
	 */
	public function getDatabaseQueryException(): string {
		return $this->databaseQueryException;
	}

	/**
	 * @inheritDoc
	 */
	public function getHookPrefix(): string {
		return $this->hookPrefix;
	}

	/**
	 * @inheritDoc
	 */
	public function setDatabaseQueryException( string $class ) {
		if ( ! is_a( $class, DatabaseQueryException::class, true ) ) {
			throw new \InvalidArgumentException( 'The provided DatabaseQueryException class must be or must extend ' . DatabaseQueryException::class . '.' );
		}

		$this->databaseQueryException = $class;
	}

	/**
	 * @inheritDoc
	 */
	public function setHookPrefix( string $prefix ) {
		$this->hookPrefix = $prefix;
	}
}
