<?php

namespace StellarWP\DB;

use Closure;
use Exception;
use Generator;
use StellarWP\DB\Database\Exceptions\DatabaseQueryException;
use StellarWP\DB\QueryBuilder\Clauses\RawSQL;
use StellarWP\DB\QueryBuilder\QueryBuilder;
use WP_Error;

/**
 * Class DB
 *
 * A static decorator for the $wpdb class and decorator function which does SQL error checking when performing queries.
 * If a SQL error occurs a DatabaseQueryException is thrown.
 *
 * @method static int|bool query( string $query )
 * @method static int|false insert( string $table, array $data, array|string|null $format = null )
 * @method static int|false delete( string $table, array $where, array|string|null $where_format = null )
 * @method static int|false update( string $table, array $data, array $where, array|string|null $format = null, array|string|null $where_format = null )
 * @method static int|false replace( string $table, array $data, array|string|null $format = null )
 * @method static null|string get_var( string|null $query = null, int $x = 0, int $y = 0 )
 * @method static array|object|null|void get_row( string|null $query = null, string $output = OBJECT, int $y = 0 )
 * @method static array get_col( string|null $query = null, int $x = 0 )
 * @method static array|object|null get_results( string|null $query = null, string $output = OBJECT )
 * @method static string get_charset_collate()
 * @method static string esc_like( string $text )
 * @method static string remove_placeholder_escape( string $text )
 * @method static Config config()
 */
class DB {
	/**
	 * Is this library initialized?
	 *
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * The Database\Provider instance.
	 *
	 * @var Database\Provider
	 */
	private static $provider;

	/**
	 * Initializes the service provider.
	 *
	 * @since 1.0.0
	 */
	public static function init(): void {
		if ( ! self::$initialized ) {
			return;
		}

		self::$provider = new Database\Provider();
		self::$provider->register();
		self::$initialized = true;
	}

	/**
	 * Runs the dbDelta function and returns a WP_Error with any errors that occurred during the process
	 *
	 * @since 1.0.0
	 *
	 * @param $delta
	 *
	 * @return array
	 * @throws DatabaseQueryException
	 * @see   dbDelta() for parameter and return details
	 *
	 */
	public static function delta( $delta ) {
		return self::runQueryWithErrorChecking(
			function () use ( $delta ) {
				return dbDelta( $delta );
			}
		);
	}

	/**
	 * A convenience method for the $wpdb->prepare method
	 *
	 * @since 1.0.0
	 *
	 * @param string $query
	 * @param mixed  ...$args
	 *
	 * @return false|mixed
	 * @see   WPDB::prepare() for usage details
	 *
	 */
	public static function prepare( $query, ...$args ) {
		global $wpdb;

		return $wpdb->prepare( $query, ...$args );
	}

	/**
	 * Magic method which calls the static method on the $wpdb while performing error checking
	 *
	 * @since 1.0.0 add givewp_db_pre_query action
	 * @since 1.0.0
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 * @throws DatabaseQueryException
	 */
	public static function __callStatic( $name, $arguments ) {
		return self::runQueryWithErrorChecking(
			static function () use ( $name, $arguments ) {
				global $wpdb;

				if ( in_array( $name, [ 'get_row', 'get_col', 'get_results', 'query' ], true ) ) {
					$hook_prefix = Config::getHookPrefix();

					/**
					 * Allow for hooking just before query execution.
					 *
					 * @since 1.0.0
					 *
					 * @param string $argument    First argument passed to the $wpdb method.
					 * @param string $hook_prefix Prefix for the hook.
					 */
					do_action( 'stellarwp_db_pre_query', current( $arguments ), $hook_prefix );

					if ( $hook_prefix ) {
						/**
						 * Allow for hooking just before query execution.
						 *
						 * @since 1.0.0
						 *
						 * @param string $argument    First argument passed to the $wpdb method.
						 * @param string $hook_prefix Prefix for the hook.
						 */
						do_action( "{$hook_prefix}_stellarwp_db_pre_query", current( $arguments ), $hook_prefix );
					}
				}

				return call_user_func_array( [ $wpdb, $name ], $arguments );
			}
		);
	}

	/**
	 * Get last insert ID
	 *
	 * @since 1.0.0
	 * @return int
	 */
	public static function last_insert_id() {
		global $wpdb;

		return $wpdb->insert_id;
	}

	/**
	 * Prefix given table name with $wpdb->prefix
	 *
	 * @param string $tableName
	 *
	 * @return string
	 */
	public static function prefix( $tableName ) {
		global $wpdb;

		return $wpdb->prefix . $tableName;
	}

	/**
	 * Create QueryBuilder instance
	 *
	 * @param string|RawSQL $table
	 * @param string|null   $alias
	 *
	 * @return QueryBuilder
	 */
	public static function table( $table, $alias = '' ) {
		$builder = new QueryBuilder();
		$builder->from( $table, $alias );

		return $builder;
	}

	/**
	 * Runs a transaction. If the callable works then the transaction is committed. If the callable throws an exception
	 * then the transaction is rolled back.
	 *
	 * @since 1.0.0
	 *
	 * @param callable $callback
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function transaction( callable $callback ) {
		self::beginTransaction();

		try {
			$callback();
		} catch ( Exception $e ) {
			self::rollback();
			throw $e;
		}

		self::commit();
	}

	/**
	 * Manually starts a transaction
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function beginTransaction() {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
	}

	/**
	 * Manually rolls back a transaction
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function rollback() {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
	}

	/**
	 * Manually commits a transaction
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public static function commit() {
		global $wpdb;
		$wpdb->query( 'COMMIT' );
	}

	/**
	 * Used as a flag to tell QueryBuilder not to process the provided SQL
	 * If $args are provided, we will assume that dev wants to use DB::prepare method with raw SQL
	 *
	 * @param string $sql
	 * @param array  ...$args
	 *
	 * @return RawSQL
	 */
	public static function raw( $sql, ...$args ) {
		return new RawSQL( $sql, ...$args );
	}

	/**
	 * Runs a query callable and checks to see if any unique SQL errors occurred when it was run
	 *
	 * @since 1.0.0
	 *
	 * @param Callable $queryCaller
	 *
	 * @return mixed
	 * @throws DatabaseQueryException
	 */
	private static function runQueryWithErrorChecking( $queryCaller ) {
		global $wpdb, $EZSQL_ERROR;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$errorCount    = is_array( $EZSQL_ERROR ) ? count( $EZSQL_ERROR ) : 0;
		$hasShowErrors = $wpdb->hide_errors();

		$output = $queryCaller();

		if ( $hasShowErrors ) {
			$wpdb->show_errors();
		}

		$wpError = self::getQueryErrors( $errorCount );

		if ( ! empty( $wpError->errors ) ) {
			/** @var DatabaseQueryException */
			$exception_class = Config::getDatabaseQueryException();

			throw new $exception_class( $wpdb->last_query, $wpError->errors );
		}

		return $output;
	}

	/**
	 * Retrieves the SQL errors stored by WordPress
	 *
	 * @since 1.0.0
	 *
	 * @param int $initialCount
	 *
	 * @return WP_Error
	 */
	private static function getQueryErrors( $initialCount = 0 ) {
		global $EZSQL_ERROR;

		$wpError = new WP_Error();

		if ( is_array( $EZSQL_ERROR ) ) {
			for ( $index = $initialCount, $indexMax = count( $EZSQL_ERROR ); $index < $indexMax; $index ++ ) {
				$error = $EZSQL_ERROR[ $index ];

				if ( empty( $error['error_str'] ) || empty( $error['query'] ) || 0 === strpos(
						$error['query'],
						'DESCRIBE '
					) ) {
					continue;
				}

				$wpError->add( 'db_delta_error', $error['error_str'] );
			}
		}

		return $wpError;
	}

	/**
	 * Get all the results from a query in batches.
	 *
	 * @since TBD
	 *
	 * @param string  $query      The SQL query.
	 * @param int     $batch_size The number of results to return in each batch.
	 * @param Closure $fetch      The function to fetch the results.
	 *
	 * @return Generator<mixed> A generator to get all the results of the query.
	 */
	private static function run_batched_query( string $query, int $batch_size, Closure $fetch ): Generator {
		// Capture the end part of the main query, the one not bound with parentheses.
		preg_match( '/([^)(]*?)$/', $query, $matches );

		// Does the end part of the query contain a LIMIT? Look for `LIMIT <offset>, <limit>` or `LIMIT <limit>`.
		$has_limit = (bool) preg_match( '/LIMIT\\s+\\d+\\s*(,\\s*\\d+)*$/i', $matches[1] );

		if ( $has_limit ) {
			yield from $fetch( $query );

			return;
		}

		// Add a LIMIT template to the query.
		$query_template = trim( $query . ' LIMIT %d, %d' );

		$offset     = 0;
		$found_rows = 0;
		$fetched    = 0;
		$key        = 0;

		// Set up the first query, make sure ot include SQL_CALC_FOUND_ROWS.
		$first_query = self::prepare(
			$query_template,
			$offset,
			$batch_size
		);

		// Build the first query to include SQL_CALC_FOUND_ROWS if not already present.
		$first_query = preg_replace( '/^SELECT\\s*(?!SQL_CALC_FOUND_ROWS)/i', 'SELECT SQL_CALC_FOUND_ROWS ', $first_query );

		$run_query = $first_query;

		do {
			foreach ( $fetch( $run_query ) as $result ) {
				$fetched ++;
				yield $key => $result;
				$key ++;
			}

			if ( $offset === 0 ) {
				// First run.
				$found_rows = (int) self::get_var( 'SELECT FOUND_ROWS()' );
			}

			$offset += $batch_size;

			// Compile the query for the next run.
			$run_query = self::prepare(
				$query_template,
				$offset,
				$batch_size
			);
		} while ( $fetched < $found_rows );
	}

	/**
	 * Get all the results from a query in batches.
	 *
	 * This method should be used to run an unbounded query in batches.
	 *
	 * @since TBD
	 *
	 * @param string|null $query      The SQL query.
	 * @param string      $output     The required return type. One of OBJECT, ARRAY_A, ARRAY_N.
	 * @param int         $batch_size The number of results to return in each batch.
	 *
	 * @return Generator<array|object|null> A generator to get all the results of the query.
	 */
	public static function generate_results( string $query = null, string $output = OBJECT, int $batch_size = 20 ): Generator {
		yield from self::run_batched_query( $query, $batch_size, static function ( string $run_query ) use ( $output ) {
			return self::get_results( $run_query, $output );
		} );
	}

	/**
	 * Get all the values in a column from a query in batches.
	 *
	 * @since TBD
	 *
	 * @param string|null $query      The SQL query.
	 * @param int         $x          The column number to return.
	 * @param int         $batch_size The number of results to return in each batch.
	 *
	 * @return Generator<mixed> The values of the column.
	 */
	public function generate_col( string $query = null, int $x = 0, int $batch_size = 50 ): Generator {
		yield from self::run_batched_query( $query, $batch_size, static function ( string $run_query ) use ( $x ) {
			return self::get_col( $run_query, $x );
		} );
	}
}
