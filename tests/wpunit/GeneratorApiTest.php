<?php

namespace StellarWP\DB;

use Generator;
use stdClass;
use StellarWP\DB\Tests\DBTestCase;
use WP_Post;

class GeneratorApitest extends DBTestCase {
	private $logged_queries = [];


	private function startListeningToQueries(): void {
		$this->logged_queries = [];

		if ( has_filter( 'query', [ $this, 'log_query' ] ) ) {
			return;
		}

		add_filter( 'query', [ $this, 'log_query' ] );
	}

	private function assertCountQueriesToSelectFoundRows( int $expected ): void {
		$queries = array_filter(
			$this->logged_queries,
			static function ( string $query ) {
				return false !== stripos( $query, 'SELECT FOUND_ROWS()' );
			}
		);

		$this->assertCount(
			$expected,
			$queries,
			"Expected to find $expected queries to select found rows, but found " . count( $queries )
		);
	}

	private function assertCountQueriesToFetchResults( int $expected ) {
		$queries = array_filter(
			$this->logged_queries,
			static function ( string $query ) {
				return false === stripos( $query, 'SELECT FOUND_ROWS()' );
			}
		);

		$this->assertCount(
			$expected,
			$queries,
			"Expected to find $expected queries to fetch results, but found " . count( $queries )
		);
	}

	public function log_query( string $query ) {
		$this->logged_queries[] = $query;

		return $query;
	}

	/**
	 * It should fetch all elements with batched queries
	 *
	 * @test
	 */
	public function should_fetch_all_elements_with_batched_queries(): void {
		$posts = static::factory()->post->create_many( 7 );
		$this->startListeningToQueries();

		global $wpdb;
		$query             = DB::prepare(
			"SELECT * FROM %i",
			$wpdb->posts
		);
		$results_generator = DB::generate_results( $query, OBJECT, 3 );
		$all_results       = iterator_to_array( $results_generator );

		$this->assertInstanceOf( Generator::class, $results_generator );
		$this->assertCount( 7, $all_results );
		$this->assertEqualSets( $posts, array_map( static function ( stdClass $post ) {
			return $post->ID;
		}, $all_results ) );
		$this->assertCountQueriesToSelectFoundRows( 1 );
		$this->assertCountQueriesToFetchResults( 3 );

		$this->startListeningToQueries();

		$query         = DB::prepare(
			"SELECT ID FROM %i",
			$wpdb->posts
		);
		$ids_generator = DB::generate_col( $query, 0, 3 );
		$all_ids       = iterator_to_array( $ids_generator );

		$this->assertInstanceOf( Generator::class, $ids_generator );
		$this->assertCount( 7, $all_ids );
		$this->assertEqualSets( $posts, $all_ids );
		$this->assertCountQueriesToSelectFoundRows( 1 );
		$this->assertCountQueriesToFetchResults( 3 );
	}

	/**
	 * It should fetch all elements with batched query when query contains sub-query
	 *
	 * @test
	 */
	public function should_fetch_all_elements_with_batched_query_when_query_contains_sub_query(): void {
		$books  = static::factory()->post->create_many( 3, [ 'post_type' => 'book' ] );
		$movies = static::factory()->post->create_many( 3, [ 'post_type' => 'movie' ] );
		$posts  = static::factory()->post->create_many( 3, [ 'post_type' => 'post' ] );

		global $wpdb;
		$query = DB::prepare(
			"SELECT * FROM %i WHERE ID NOT IN (
				SELECT ID FROM %i WHERE post_type = 'book'
			)",
			$wpdb->posts,
			$wpdb->posts
		);
		$this->startListeningToQueries();

		$results_generator = DB::generate_results( $query, OBJECT, 2 );
		$all_results       = iterator_to_array( $results_generator );

		$this->assertInstanceOf( Generator::class, $results_generator );
		$this->assertCount( 6, $all_results );
		$this->assertEqualSets( array_merge( $movies, $posts ), array_map( static function ( stdClass $post ) {
			return $post->ID;
		}, $all_results ) );
		$this->assertCountQueriesToSelectFoundRows( 1 );
		$this->assertCountQueriesToFetchResults( 3 );

		$query = DB::prepare(
			"SELECT ID FROM %i WHERE ID NOT IN (
				SELECT ID FROM %i WHERE post_type = 'book'
			)",
			$wpdb->posts,
			$wpdb->posts
		);
		$this->startListeningToQueries();

		$ids_generator = DB::generate_col( $query, 0, 2 );
		$all_ids       = iterator_to_array( $ids_generator );

		$this->assertInstanceOf( Generator::class, $ids_generator );
		$this->assertCount( 6, $all_ids );
		$this->assertEqualSets( array_merge( $movies, $posts ), $all_ids );
		$this->assertCountQueriesToSelectFoundRows( 1 );
		$this->assertCountQueriesToFetchResults( 3 );
	}

	/**
	 * It should not alter a query that already has a LIMIT clause
	 *
	 * @test
	 */
	public function should_not_alter_a_query_that_already_has_a_limit_clause(): void {
		$posts = static::factory()->post->create_many( 7 );
		$this->startListeningToQueries();

		global $wpdb;
		$query             = DB::prepare(
			"SELECT * FROM %i LIMIT 4",
			$wpdb->posts
		);
		$results_generator = DB::generate_results( $query, OBJECT, 3 );
		$all_results       = iterator_to_array( $results_generator );

		$this->assertInstanceOf( Generator::class, $results_generator );
		$this->assertCount( 4, $all_results );
		$this->assertEqualSets( array_slice( $posts, 0, 4 ), array_map( static function ( stdClass $post ) {
			return $post->ID;
		}, $all_results ) );
		$this->assertCountQueriesToSelectFoundRows( 0 );
		$this->assertCountQueriesToFetchResults( 1 );

		$this->startListeningToQueries();

		$query = DB::prepare(
			"SELECT ID FROM %i LIMIT 4",
			$wpdb->posts
		);

		$ids_generator = DB::generate_col( $query, 0, 3 );
		$all_ids       = iterator_to_array( $ids_generator );

		$this->assertInstanceOf( Generator::class, $ids_generator );
		$this->assertCount( 4, $all_ids );
		$this->assertEqualSets( array_slice( $posts, 0, 4 ), $all_ids );
		$this->assertCountQueriesToSelectFoundRows( 0 );
		$this->assertCountQueriesToFetchResults( 1 );
	}

	/**
	 * It should respect the output format when getting results
	 *
	 * @test
	 */
	public function should_respect_the_output_format_when_getting_results(): void {
		$posts = static::factory()->post->create_many( 7 );
		$this->startListeningToQueries();

		global $wpdb;
		$query             = DB::prepare(
			"SELECT * FROM %i",
			$wpdb->posts
		);
		$results_generator = DB::generate_results( $query, ARRAY_A, 3 );

		$all_results = iterator_to_array( $results_generator );

		$this->assertInstanceOf( Generator::class, $results_generator );
		$this->assertCount( 7, $all_results );
		$this->assertContainsOnly( 'array', $all_results );
		$this->assertEqualSets( $posts, array_map( static function ( array $post ) {
			return $post['ID'];
		}, $all_results ) );
		$this->assertCountQueriesToSelectFoundRows( 1 );

		$this->startListeningToQueries();

		$query         = DB::prepare(
			"SELECT ID FROM %i",
			$wpdb->posts
		);
		$ids_generator = DB::generate_results( $query, OBJECT, 3 );

		$all_ids = iterator_to_array( $ids_generator );

		$this->assertInstanceOf( Generator::class, $ids_generator );
		$this->assertCount( 7, $all_ids );
		$this->assertContainsOnlyInstancesOf( stdClass::class, $all_ids );
		$this->assertEqualSets( $posts, array_map( static function ( stdClass $post ) {
			return $post->ID;
		}, $all_ids ) );
		$this->assertCountQueriesToSelectFoundRows( 1 );

		$this->startListeningToQueries();

		$query         = DB::prepare(
			"SELECT ID FROM %i",
			$wpdb->posts
		);
		$ids_generator = DB::generate_results( $query, ARRAY_N, 3 );
		$all_results   = iterator_to_array( $ids_generator );

		$this->assertInstanceOf( Generator::class, $ids_generator );
		$this->assertCount( 7, $all_results );
		$this->assertContainsOnly( 'array', $all_results );
		$this->assertEqualSets( $posts, array_map( static function ( array $post ) {
			return $post[0];
		}, $all_results ) );
		$this->assertCountQueriesToSelectFoundRows( 1 );
	}

	/**
	 * It should respect the x when getting column
	 *
	 * @test
	 */
	public function should_respect_the_x_when_getting_column(): void {
		$posts = static::factory()->post->create_many( 7 );
		$this->startListeningToQueries();

		global $wpdb;
		$query         = DB::prepare(
			"SELECT post_name, post_status, post_title, ID FROM %i",
			$wpdb->posts
		);
		$ids_generator = DB::generate_col( $query, 3, 3 );
		$all_ids       = iterator_to_array( $ids_generator );

		$this->assertInstanceOf( Generator::class, $ids_generator );
		$this->assertCount( 7, $all_ids );
		$this->assertEqualSets( $posts, $all_ids );
		$this->assertCountQueriesToSelectFoundRows( 1 );
	}

	/**
	 * It should correctly handle LIMIT in edge-case queries
	 *
	 * @test
	 */
	public function should_correctly_handle_limit_in_edge_case_queries(): void {
		$limited_editions = static::factory()->post->create_many( 7, [ 'post_type' => 'limited_edition' ] );
		$this->startListeningToQueries();

		global $wpdb;
		$query             = DB::prepare(
			"SELECT * FROM %i limited_editition",
			$wpdb->posts
		);
		$results_generator = DB::generate_results( $query, OBJECT, 3 );
		$all_results       = iterator_to_array( $results_generator );

		$this->assertInstanceOf( Generator::class, $results_generator );
		$this->assertCount( 7, $all_results );
		$this->assertEqualSets( $limited_editions, array_map( static function ( stdClass $post ) {
			return $post->ID;
		}, $all_results ) );
		$this->assertCountQueriesToSelectFoundRows( 1 );
		$this->assertCountQueriesToFetchResults( 3 );
	}
}
