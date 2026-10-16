<?php declare( strict_types=1 );

namespace StellarWP\DB;

use StellarWP\DB\Tests\DBTestCase;

/**
 * @backupStaticAttributes
 */
final class TransactionTest extends DBTestCase {

	protected function tearDown() {
		parent::tearDown();

		// Reset transaction depth in case a test leaves it dirty.
		while ( DB::transactionLevel() > 0 ) {
			DB::rollback();
		}
	}

	public function test_it_should_start_real_transaction_at_depth_zero(): void {
		global $wpdb;

		DB::beginTransaction();

		$this->assertSame( 1, DB::transactionLevel() );

		// Verify we're in a real transaction by inserting + rolling back.
		$wpdb->query( "CREATE TEMPORARY TABLE _tx_test (id INT)" );
		$wpdb->query( "INSERT INTO _tx_test VALUES (1)" );

		DB::rollback();

		$this->assertSame( 0, DB::transactionLevel() );

		// Row should be gone after rollback.
		$result = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test" );
		$this->assertEquals( 0, $result );

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS _tx_test" );
	}

	public function test_it_should_use_savepoint_for_nested_transaction(): void {
		DB::beginTransaction();
		DB::beginTransaction();

		$this->assertSame( 2, DB::transactionLevel() );

		DB::commit();

		$this->assertSame( 1, DB::transactionLevel() );

		DB::commit();

		$this->assertSame( 0, DB::transactionLevel() );
	}

	public function test_it_should_rollback_savepoint_without_affecting_outer_transaction(): void {
		global $wpdb;

		$wpdb->query( "CREATE TEMPORARY TABLE _tx_test (id INT)" );

		DB::beginTransaction();

		$wpdb->query( "INSERT INTO _tx_test VALUES (1)" );

		// Nested transaction via savepoint.
		DB::beginTransaction();
		$wpdb->query( "INSERT INTO _tx_test VALUES (2)" );
		DB::rollback(); // Rolls back savepoint only.

		$this->assertSame( 1, DB::transactionLevel() );

		DB::commit(); // Commits outer transaction.

		// Row 1 should survive, row 2 should not.
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test WHERE id = 1" );
		$this->assertEquals( 1, $count );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test WHERE id = 2" );
		$this->assertEquals( 0, $count );

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS _tx_test" );
	}

	public function test_it_should_rollback_entire_transaction_at_depth_zero(): void {
		global $wpdb;

		$wpdb->query( "CREATE TEMPORARY TABLE _tx_test (id INT)" );

		DB::beginTransaction();
		$wpdb->query( "INSERT INTO _tx_test VALUES (1)" );

		DB::beginTransaction();
		$wpdb->query( "INSERT INTO _tx_test VALUES (2)" );
		DB::commit(); // Release savepoint.

		DB::rollback(); // Rollback outer — both rows gone.

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test" );
		$this->assertEquals( 0, $count );

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS _tx_test" );
	}

	public function test_transaction_helper_should_commit_on_success() {
		global $wpdb;

		$wpdb->query( "CREATE TEMPORARY TABLE _tx_test (id INT)" );

		DB::transaction( static function () use ( $wpdb ) {
			$wpdb->query( "INSERT INTO _tx_test VALUES (1)" );
		} );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test" );
		$this->assertEquals( 1, $count );

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS _tx_test" );
	}

	public function test_transaction_helper_should_rollback_on_exception(): void {
		global $wpdb;

		$wpdb->query( "CREATE TEMPORARY TABLE _tx_test (id INT)" );

		try {
			DB::transaction( static function () use ( $wpdb ) {
				$wpdb->query( "INSERT INTO _tx_test VALUES (1)" );
				throw new \RuntimeException( 'fail' );
			} );
		} catch ( \RuntimeException $e ) {
			// Expected.
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test" );
		$this->assertEquals( 0, $count );

		$this->assertSame( 0, DB::transactionLevel() );

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS _tx_test" );
	}

	public function test_nested_transaction_helper_should_use_savepoints(): void {
		global $wpdb;

		$wpdb->query( "CREATE TEMPORARY TABLE _tx_test (id INT)" );

		DB::transaction( static function () use ( $wpdb ) {
			$wpdb->query( "INSERT INTO _tx_test VALUES (1)" );

			// Nested transaction — should use savepoint.
			DB::transaction( static function () use ( $wpdb ) {
				$wpdb->query( "INSERT INTO _tx_test VALUES (2)" );
			} );
		} );

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM _tx_test" );
		$this->assertEquals( 2, $count );

		$this->assertSame( 0, DB::transactionLevel() );

		$wpdb->query( "DROP TEMPORARY TABLE IF EXISTS _tx_test" );
	}

	public function test_transaction_level_starts_at_zero(): void {
		$this->assertSame( 0, DB::transactionLevel() );
	}
}
