<?php

namespace StellarWP\DB\QueryBuilder\Concerns;

use StellarWP\DB\DB;

/**
 * @since 1.0.0
 */
trait CRUD {
	/**
	 * @see https://developer.wordpress.org/reference/classes/wpdb/insert/
	 *
	 * @since 1.0.0
	 *
	 * @param  array|string  $format
	 *
	 * @param  array  $data
	 * @return false|int
	 *
	 */
	public function insert( $data, $format = null ) {
		return DB::insert(
			$this->getTable(),
			$data,
			$format
		);
	}

	/**
	 * @see https://developer.wordpress.org/reference/classes/wpdb/update/
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $data
	 * @param  array|string|null  $format
	 *
	 * @return false|int
	 *
	 */
	public function update( $data, $format = null ) {
		return DB::update(
			$this->getTable(),
			$data,
			$this->getWhere(),
			$format,
			null
		);
	}

	/**
	 * Upsert allows for inserting or updating a row depending on whether it already exists.
	 *
	 * @since 1.0.8
	 *
	 * @param array $data The data to insert or update.
	 * @param array $match The columns to match on.
	 * @param string|null $format
	 *
	 * @return false|int
	 */
	public function upsert( $data, $match = [], $format = null ) {
		// Build the where clause(s).
		foreach ( $match as $column ) {
			$this->where( $column, $data[$column] );
		}

		// If the row exists, update it.
		if ( $this->get() ) {
			return $this->update( $data, $format );
		}

		// Otherwise, insert it.
		return $this->insert( $data, $format );
	}

	/**
	 * @since 1.0.0
	 *
	 * @return false|int
	 *
	 * @see https://developer.wordpress.org/reference/classes/wpdb/delete/
	 */
	public function delete() {
		return DB::delete(
			$this->getTable(),
			$this->getWhere(),
			null
		);
	}

	/**
	 * Get results
	 *
	 * @since 1.0.0
	 *
	 * @param  string $output ARRAY_A|ARRAY_N|OBJECT|OBJECT_K
	 *
	 * @return array|object|null
	 */
	public function getAll( $output = OBJECT ) {
		return DB::get_results( $this->getSQL(), $output );
	}

	/**
	 * Get row
	 *
	 * @since 1.0.0
	 *
	 * @param  string $output ARRAY_A|ARRAY_N|OBJECT|OBJECT_K
	 *
	 * @return array|object|null
	 */
	public function get( $output = OBJECT ) {
		return DB::get_row( $this->getSQL(), $output );
	}

	/**
	 * @since 1.0.0
	 *
	 * @return string
	 */
	private function getTable() {
		return $this->froms[0]->table;
	}

	/**
	 * @since 1.0.0
	 *
	 * @return array[]
	 */
	private function getWhere() {
		$wheres = [];

		foreach ( $this->wheres as $where ) {
			$wheres[ $where->column ] = $where->value;
		}

		return $wheres;
	}
}
