<?php

namespace StellarWP\DB\QueryBuilder\Clauses;

use StellarWP\DB\QueryBuilder\QueryBuilder;

/**
 * @since 1.0.0
 */
class From {
	/**
	 * @var string|RawSQL
	 */
	public $table;

	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @param  string|RawSQL  $table
	 * @param  string|null  $alias
	 */
	public function __construct( $table, $alias = null ) {
		$this->table = QueryBuilder::prefixTable( $table );
		$this->alias = is_null( $alias ) ? null : trim( $alias );
	}
}
