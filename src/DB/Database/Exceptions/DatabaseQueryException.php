<?php

namespace StellarWP\DB\Database\Exceptions;

use Throwable;

/**
 * Class DatabaseQueryException
 *
 * An exception for when errors occurred within the database while performing a query, which stores the SQL errors the
 * database returned
 *
 * @since 1.0.0
 */
class DatabaseQueryException extends \Exception {
	/**
	 * @var string[]
	 */
	private $queryErrors;

	/**
	 * @var string
	 */
	private $query;

	/**
	 * @since 1.0.0
	 */
	public function __construct(
		string $query,
		array $queryErrors,
		string $message = 'Database Query',
		$code = 0,
		Throwable $previous = null
	) {
		$this->query = $query;
		$this->queryErrors = $queryErrors;

		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Returns the query errors
	 *
	 * @since 1.0.0
	 *
	 * @return string[]
	 */
	public function getQueryErrors(): array {
		return $this->queryErrors;
	}

	public function getQuery(): string {
		return $this->query;
	}
}
