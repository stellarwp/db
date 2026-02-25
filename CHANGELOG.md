# Change Log

All notable changes to this project will be documented in this file. This project adhere to the [Semantic Versioning](http://semver.org/) standard.

## [1.3.0] - 2026-02-25

### Added
- Transaction nesting support via savepoints. Nested `beginTransaction()` calls now create savepoints instead of issuing `START TRANSACTION`, which would implicitly commit any existing transaction.
- `DB::transactionLevel()` method to retrieve the current transaction nesting depth.

### Changed
- `DB::commit()` releases the current savepoint when nested, and issues a real `COMMIT` only at the outermost level.
- `DB::rollback()` rolls back to the current savepoint when nested, and issues a real `ROLLBACK` only at the outermost level.
- `tests-php.yml` ensure slic runs in PHP 7.4.

## [1.0.8] TBD

* Feat - Add the `DB::generate_results` and `DB::generate_col` methods to the `DB` class to fetch all results matching an unbounded query with a set of bounded queries.

## [1.0.7] 2023-10-23

* Tweak - Updates around `trim()` for php 8.1 compatibility.
* Tweak - Force `From()` and `Select()` to convert passed non-strings to an empty string.

## [1.0.6] 2023-09-05

* Tweak - Fix array shape for errors in `DatabaseQueryException`

## [1.0.5] 2023-09-05

* Tweak - Updating docblock for `whereExists()` and `whereNotExists()` in response to a PHPStan flag.

## [1.0.4] 2023-06-06

* Tweak - Added more documentation for methods provided by DB.
* Tweak - Adjusted docblocks to better declare types.

## [1.0.3] 2022-11-22

* Tweak - Set composer.json `config.platform.php` to `7.0`.

## [1.0.2] 2022-11-22

* Fix - Adjust `DB::insert()`, `DB::delete()`, `DB::update()`, and `DB::replace()` signature to match `wpdb`'s supported method signatures.
* Fix - Adjust `DB::get_var()`, `DB::get_col()`, and `DB::get_results()` signature of first arg to match `wpdb`'s signature.

## [1.0.1] 2022-09-29

* Tweak - Added a `Config` class to handle overrides of the `DatabaseQueryException` and addition of a hook prefix.
* Tweak - Added tests for `Config`
* Docs - More documentation

## [1.0.0] 2022-08-17

* Feature - Initial version
* Docs - Documentation
* Tweak - Automated tests

[1.0.0]: https://github.com/stellarwp/schema/releases/tag/1.0.0
[1.0.1]: https://github.com/stellarwp/schema/releases/tag/1.0.1
[1.0.2]: https://github.com/stellarwp/schema/releases/tag/1.0.2
[1.0.3]: https://github.com/stellarwp/schema/releases/tag/1.0.3
