<?php

namespace StellarWP\DB\Database;

use lucatume\DI52\App;
use lucatume\DI52\ServiceProvider;

class Provider extends ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		$this->container->singleton( static::class, $this );
		$this->container->singleton( Actions\EnableBigSqlSelects::class, Actions\EnableBigSqlSelects::class );
		$this->register_hooks();
	}

	/**
	 * Registers all hooks.
	 *
	 * @since 1.0.0
	 */
	private function register_hooks() : void {
		add_action( 'stellarwp_db_pre_query', App::callback( Actions\EnableBigSqlSelects::class, 'set_var' ) );
	}
}
