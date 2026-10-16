# StellarWP DB - Claude Instructions

## Project Overview

StellarWP DB is a WordPress database wrapper and query builder library that provides a fluent interface for constructing and executing database queries. It's built on top of WordPress's `$wpdb` global and adds a modern, chainable API with proper error handling.

**Key Features:**
- Fluent query builder interface
- SQL injection protection via WordPress's prepared statements
- Support for complex queries (joins, unions, subqueries, etc.)
- Meta table abstractions for WordPress-style data
- Error handling with exceptions
- Full compatibility with `$wpdb` methods

## Architecture

### Core Classes

1. **DB** (`src/DB/DB.php`)
   - Static facade/decorator for `$wpdb`
   - Entry point for creating query builders via `DB::table()`
   - Provides static methods that wrap `$wpdb` methods with error checking
   - Throws `DatabaseQueryException` on SQL errors

2. **QueryBuilder** (`src/DB/QueryBuilder/QueryBuilder.php`)
   - Main query building class
   - Uses traits to organize functionality:
     - `Aggregate` - COUNT, SUM, AVG, MIN, MAX operations
     - `CRUD` - INSERT, UPDATE, DELETE, UPSERT operations
     - `FromClause` - FROM table handling
     - `WhereClause` - WHERE conditions
     - `JoinClause` - JOIN operations
     - `SelectStatement` - SELECT column handling
     - `MetaQuery` - WordPress meta table helpers
     - And more...

3. **Config** (`src/DB/Config.php`)
   - Configuration management
   - Allows setting custom exception classes
   - Hook prefix configuration for WordPress integration

### Directory Structure

```
/home/matt/git/db/
├── src/DB/                    # Main source code
│   ├── Config.php            # Configuration class
│   ├── DB.php               # Main DB facade
│   ├── Database/            # Database-specific classes
│   │   ├── Actions/         # Database actions (e.g., EnableBigSqlSelects)
│   │   ├── Exceptions/      # Custom exceptions
│   │   └── Provider.php     # Service provider
│   └── QueryBuilder/        # Query builder components
│       ├── Clauses/         # SQL clause representations
│       ├── Concerns/        # Traits for QueryBuilder
│       ├── Types/           # Type definitions (JoinType, Operator, etc.)
│       ├── QueryBuilder.php # Main query builder
│       ├── JoinQueryBuilder.php
│       └── WhereQueryBuilder.php
├── tests/                    # Test suite
│   ├── wpunit/              # WordPress unit tests
│   └── _support/            # Test support classes
├── .github/workflows/        # GitHub Actions CI/CD
├── composer.json            # PHP dependencies
├── phpstan.neon.dist        # PHPStan configuration
└── README.md                # Documentation
```

## Development Commands

### Composer Scripts

```bash
# Run static analysis with PHPStan
composer test:analysis
```

### Testing

The project uses Codeception with WordPress testing framework (wp-browser) and a tool called "slic" for managing the test environment:

```bash
# Run tests using slic (see .github/workflows/tests-php.yml)
${SLIC_BIN} run wpunit --ext DotReporter
```

### Static Analysis

PHPStan is configured at level 5 with WordPress-specific rules:
- Configuration: `phpstan.neon.dist`
- Includes WordPress stubs via `szepeviktor/phpstan-wordpress`
- Analyzes only the `src/` directory

## Code Conventions

### Namespacing
- Root namespace: `StellarWP\DB`
- Follow PSR-4 autoloading standard
- Tests use `StellarWP\DB\Tests` namespace

### WordPress Integration
- All table names are automatically prefixed with `$wpdb->prefix`
- Use `DB::raw()` to bypass automatic prefixing
- Methods match `$wpdb` signatures where applicable

### Error Handling
- SQL errors throw `DatabaseQueryException` by default
- Custom exception classes can be configured via `Config::setDatabaseQueryException()`
- All database operations should be wrapped in try-catch blocks in production code

### Query Building Patterns

```php
// Basic query
DB::table('posts')
    ->select('ID', 'post_title')
    ->where('post_status', 'publish')
    ->orderBy('post_date', 'DESC')
    ->limit(10)
    ->getAll();

// Complex query with joins and meta
DB::table('posts', 'p')
    ->select(['p.ID', 'id'], ['p.post_title', 'title'])
    ->attachMeta('postmeta', 'p.ID', 'post_id',
        ['_thumbnail_id', 'thumbnailId'],
        ['_custom_field', 'customValue']
    )
    ->leftJoin('term_relationships', 'p.ID', 'tr.object_id', 'tr')
    ->where('p.post_type', 'post')
    ->where('p.post_status', 'publish')
    ->getAll();
```

## Important Notes

### For Strauss Integration
The library is designed to be included via Strauss for namespace isolation in WordPress plugins. The README examples assume a namespace prefix like `Boom\Shakalaka\`.

### WordPress Compatibility
- Requires WordPress with `$wpdb` global
- Compatible with WordPress coding standards
- Follows WordPress database structure conventions (posts, postmeta, etc.)

### Performance Considerations
- Queries are not executed until terminal methods are called (`get()`, `getAll()`, `count()`, etc.)
- Use `attachMeta()` for efficient meta queries instead of multiple joins
- The library generates SQL using WordPress's `prepare()` method for security

### Recent Updates (v1.0.8)
- Added `DB::generate_results()` and `DB::generate_col()` for handling large result sets with bounded queries
- PHP 8.1 compatibility improvements
- Better type safety in docblocks

## CI/CD

### GitHub Actions Workflows

1. **Tests** (`.github/workflows/tests-php.yml`)
   - Runs on every push
   - Uses `stellarwp/slic` for test environment setup
   - Executes wpunit test suite

2. **Static Analysis** (`.github/workflows/static-analysis.yml`)
   - Runs on every push
   - PHP 8.0 environment
   - Executes PHPStan via `composer test:analysis`

## Common Tasks

### Adding New Query Features
1. Create a new trait in `src/DB/QueryBuilder/Concerns/`
2. Add the trait to the `QueryBuilder` class
3. Implement the SQL generation method (e.g., `getYourFeatureSQL()`)
4. Add it to the `getSQL()` method if needed
5. Write tests in `tests/wpunit/QueryBuilder/`

### Debugging Queries
```php
// Get the generated SQL without executing
$sql = DB::table('posts')
    ->where('post_status', 'publish')
    ->getSQL();

// Use wpdb's last_query after execution
$results = DB::table('posts')->getAll();
$lastQuery = $wpdb->last_query;
```

### Meta Table Patterns
The library provides special handling for WordPress meta tables:
- `attachMeta()` - Efficiently join and select meta values
- `configureMetaTable()` - Customize meta table column names
- Automatic LEFT JOIN generation for each meta key
- Support for multiple meta values with the same key

## Resources

- [WordPress $wpdb Documentation](https://developer.wordpress.org/reference/classes/wpdb/)
- [Codeception Documentation](https://codeception.com/)
- [PHPStan Documentation](https://phpstan.org/)
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)