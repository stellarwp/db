# Change Log

All notable changes to this project will be documented in this file. This project adhere to the [Semantic Versioning](http://semver.org/) standard.

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
