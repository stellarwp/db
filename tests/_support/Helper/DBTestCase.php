<?php

namespace StellarWP\DB\Tests;

use Codeception\TestCase\WPTestCase;
use StellarWP\DB\DB;

class DBTestCase extends WPTestCase {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		DB::init();
	}
}

