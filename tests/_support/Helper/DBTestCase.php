<?php

namespace StellarWP\DB\Tests;

use Codeception\TestCase\WPTestCase;
use StellarWP\DB\DB;

/**
 * The base test case.
 *
 * @mixin \Codeception\PHPUnit\TestCase
 */
class DBTestCase extends WPTestCase {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		DB::init();
	}
}

