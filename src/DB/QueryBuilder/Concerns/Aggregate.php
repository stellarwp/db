<?php

namespace StellarWP\DB\QueryBuilder\Concerns;

use StellarWP\DB\QueryBuilder\Clauses\RawSQL;

/**
 * @since 1.0.0
 */
trait Aggregate {
	/**
	 * Returns the number of rows returned by a query
	 *
	 * @since 1.0.0
	 * @param  null|string  $column
	 *
	 * @return int
	 */
	public function count( $column = null ) {
		$column = ( ! $column || $column === '*' ) ? '1' : trim( $column );

		$this->selects[] = new RawSQL( 'SELECT COUNT(%1s) AS count', $column );

		$result = $this->get();

		if ( is_array( $result ) ) {
			return +$result['count'];
		}

		return +$result->count;
	}

	/**
	 * Returns the total sum in a set of values
	 *
	 * @since 1.0.0
	 * @param  string  $column
	 *
	 * @return int|float
	 */
	public function sum( $column ) {
		$this->selects[] = new RawSQL( 'SELECT SUM(%1s) AS sum', $column );

		$result = $this->get();

		if ( is_array( $result ) ) {
			return +$result['sum'];
		}

		return +$result->sum;
	}

	/**
	 * Get the average value in a set of values
	 *
	 * @since 1.0.0
	 * @param  string  $column
	 *
	 * @return int|float
	 */
	public function avg( $column ) {
		$this->selects[] = new RawSQL( 'SELECT AVG(%1s) AS avg', $column );

		$result = $this->get();

		if ( is_array( $result ) ) {
			return +$result['avg'];
		}

		return +$result->avg;
	}

	/**
	 * Returns the minimum value in a set of values
	 *
	 * @since 1.0.0
	 * @param  string  $column
	 *
	 * @return int|float
	 */
	public function min( $column ) {
		$this->selects[] = new RawSQL( 'SELECT MIN(%1s) AS min', $column );

		$result = $this->get();

		if ( is_array( $result ) ) {
			return +$result['min'];
		}

		return +$result->min;
	}

	/**
	 * Returns the maximum value in a set of values
	 *
	 * @since 1.0.0
	 * @param  string  $column
	 *
	 * @return int|float
	 */
	public function max( $column ) {
		$this->selects[] = new RawSQL( 'SELECT MAX(%1s) AS max', $column );

		$result = $this->get();

		if ( is_array( $result ) ) {
			return +$result['max'];
		}

		return +$result->max;
	}
}
