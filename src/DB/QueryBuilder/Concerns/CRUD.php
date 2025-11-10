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
	 * @param array<string, string|int|float|bool|null> $data The data to insert or update.
	 * @param array $match The columns to match on.
	 * @param string|array|null $format Array of formats to be mapped to each value in $data. If string, the format will be used for all values in $data.
	 *
	 * @return false|int Number of rows updated/inserted, false on error.
	 */
	public function upsert( $data, $match = [], $format = null ) {
		// Build the where clause(s).
		foreach ( $match as $column ) {
			$this->where( $column, $data[ $column ] );
		}

		// If the row exists, update it.
		if ( $this->get() ) {
			return $this->update( $data, $format );
		}

		// Otherwise, insert it.
		return $this->insert( $data, $format );
	}

	/**
	 * Delete rows from the database.
	 *
	 * Unlike WordPress's $wpdb->delete() method, this implementation generates and executes
	 * a DELETE SQL statement directly, which allows for advanced features like ORDER BY and LIMIT.
	 *
	 * Supports:
	 * - WHERE clauses (including whereLike, whereIn, whereBetween, etc.)
	 * - ORDER BY for controlling which rows are deleted first
	 * - LIMIT to restrict the number of rows deleted
	 * - Complex WHERE conditions (AND, OR, nested queries)
	 *
	 * Usage examples:
	 * ```php
	 * // Simple delete with WHERE
	 * DB::table('posts')->where('post_status', 'draft')->delete();
	 *
	 * // Delete with LIMIT (delete only 10 rows)
	 * DB::table('posts')->where('post_type', 'temp')->limit(10)->delete();
	 *
	 * // Delete oldest posts first using ORDER BY and LIMIT
	 * DB::table('posts')
	 *     ->where('post_status', 'trash')
	 *     ->orderBy('post_date', 'ASC')
	 *     ->limit(100)
	 *     ->delete();
	 *
	 * // Delete with LIKE pattern
	 * DB::table('posts')->whereLike('post_title', 'Draft:%')->delete();
	 *
	 * // Delete with multiple conditions
	 * DB::table('posts')
	 *     ->where('post_type', 'page')
	 *     ->where('post_status', 'auto-draft')
	 *     ->whereBetween('ID', 1, 1000)
	 *     ->delete();
	 * ```
	 *
	 * Restrictions:
	 * - Table aliases in the FROM clause may not be supported on older database versions
	 *   (MySQL < 8.0.24, MariaDB < 11.6). Avoid using table aliases with delete().
	 * - JOINs are not supported in DELETE statements with this implementation
	 *
	 * @since 1.0.0
	 *
	 * @return false|int Number of rows deleted, or false on error.
	 *
	 * @see QueryBuilder::deleteSQL() for the SQL generation logic
	 */
	public function delete() {
		return DB::query( $this->deleteSQL() );
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
