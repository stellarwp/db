<?php

namespace StellarWP\DB\Contracts;

interface ConfigComponents {
	/**
	 * Gets the DatabaseQueryException class.
	 *
	 * @return string
	 */
	public function getDatabaseQueryException(): string;

	/**
	 * Gets the hook prefix.
	 *
	 * @return string
	 */
	public function getHookPrefix(): string;

	/**
	 * Sets the DatabaseQueryException class.
	 *
	 * @param string $class Class name of the DatabaseQueryException to use.
	 *
	 * @return void
	 */
	public function setDatabaseQueryException( string $class );

	/**
	 * Sets the hook prefix.
	 *
	 * @param string $prefix The prefix to add to hooks.
	 *
	 * @return void
	 */
	public function setHookPrefix( string $prefix );
}
