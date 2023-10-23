<?php

namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\QueryBuilder\Clauses\Join;
use StellarWP\DB\QueryBuilder\Clauses\JoinCondition;
use StellarWP\DB\QueryBuilder\Clauses\RawSQL;
use StellarWP\DB\QueryBuilder\Types\JoinType;
use StellarWP\DB\QueryBuilder\Types\Operator;

/**
 * @since 1.0.0
 */
class JoinQueryBuilder {
	/**
	 * @var Join[]|JoinCondition[]|RawSQL[]
	 */
	private $joins = [];

	/**
	 * @param  string|RawSQL  $table
	 * @param  string|null  $alias
	 *
	 * @return $this
	 */
	public function leftJoin( $table, $alias = '' ) {
		return $this->join(
			JoinType::LEFT,
			$table,
			$alias
		);
	}

	/**
	 * @param  string|RawSQL  $table
	 * @param  string|null  $alias
	 *
	 * @return $this
	 */
	public function rightJoin( $table, $alias = '' ) {
		return $this->join(
			JoinType::RIGHT,
			$table,
			$alias
		);
	}

	/**
	 * @param  string|RawSQL  $table
	 * @param  string|null  $alias
	 *
	 * @return $this
	 */
	public function innerJoin( $table, $alias = '' ) {
		return $this->join(
			JoinType::INNER,
			$table,
			$alias
		);
	}

	/**
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  bool  $quote
	 *
	 * @return $this
	 */
	public function on( $column1, $column2, $quote = false ) {
		return $this->joinCondition(
			Operator::ON,
			$column1,
			$column2,
			$quote
		);
	}

	/**
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  bool  $quote
	 *
	 * @return $this
	 */
	public function andOn( $column1, $column2, $quote = null ) {
		return $this->joinCondition(
			Operator::_AND,
			$column1,
			$column2,
			$quote
		);
	}

	/**
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  bool  $quote
	 *
	 * @return $this
	 */
	public function orOn( $column1, $column2, $quote = null ) {
		return $this->joinCondition(
			Operator::_OR,
			$column1,
			$column2,
			$quote
		);
	}

	/**
	 * Add raw SQL JOIN clause
	 *
	 * @param string  $sql
	 * @param array<int,mixed> ...$args
	 */
	public function joinRaw( $sql, ...$args ) {
		$this->joins[] = new RawSQL( $sql, $args );
	}

	/**
	 * Add Join
	 *
	 * @param  string  $joinType
	 * @param  string|RawSQL  $table
	 * @param  string|null  $alias
	 *
	 * @return $this
	 */
	private function join( $joinType, $table, $alias = '' ) {
		$this->joins[] = new Join(
			$joinType,
			$table,
			$alias
		);

		return $this;
	}

	/**
	 * Add JoinCondition
	 *
	 * @param  string  $operator
	 * @param  string  $column1
	 * @param  string  $column2
	 * @param  bool  $quote
	 *
	 * @return $this
	 */
	private function joinCondition( $operator, $column1, $column2, $quote ) {
		$this->joins[] = new JoinCondition(
			$operator,
			$column1,
			$column2,
			$quote
		);

		return $this;
	}

	/**
	 * @return Join[]|JoinCondition[]|RawSQL[]
	 */
	public function getDefinedJoins() {
		return $this->joins;
	}
}
