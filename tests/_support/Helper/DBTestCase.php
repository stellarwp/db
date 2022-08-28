<?php

namespace StellarWP\DB\Tests;

use lucatume\DI52\App;
use StellarWP\DB\DB;

class DBTestCase extends \Codeception\Test\Unit {
	protected $backupGlobals = false;

	public function setUp() {
		// before
		parent::setUp();

		DB::init();
	}
}

