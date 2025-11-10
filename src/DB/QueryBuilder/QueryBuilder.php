<?php

namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\QueryBuilder\Concerns\Aggregate;
use StellarWP\DB\QueryBuilder\Concerns\CRUD;
use StellarWP\DB\QueryBuilder\Concerns\FromClause;
use StellarWP\DB\QueryBuilder\Concerns\GroupByStatement;
use StellarWP\DB\QueryBuilder\Concerns\HavingClause;
use StellarWP\DB\QueryBuilder\Concerns\JoinClause;
use StellarWP\DB\QueryBuilder\Concerns\LimitStatement;
use StellarWP\DB\QueryBuilder\Concerns\MetaQuery;
use StellarWP\DB\QueryBuilder\Concerns\OffsetStatement;
use StellarWP\DB\QueryBuilder\Concerns\OrderByStatement;
use StellarWP\DB\QueryBuilder\Concerns\SelectStatement;
use StellarWP\DB\QueryBuilder\Concerns\TablePrefix;
use StellarWP\DB\QueryBuilder\Concerns\UnionOperator;
use StellarWP\DB\QueryBuilder\Concerns\WhereClause;

/**
 * @since 1.0.0
 */
class QueryBuilder {
	use Aggregate;
	use CRUD;
	use FromClause;
	use GroupByStatement;
	use HavingClause;
	use JoinClause;
	use LimitStatement;
	use MetaQuery;
	use OffsetStatement;
	use OrderByStatement;
	use SelectStatement;
	use TablePrefix;
	use UnionOperator;
	use WhereClause;

	/**
	 * @return string
	 */
	public function getSQL() {
		$sql = array_merge(
			$this->getSelectSQL(),
			$this->getFromSQL(),
			$this->getJoinSQL(),
			$this->getWhereSQL(),
			$this->getGroupBySQL(),
			$this->getHavingSQL(),
			$this->getOrderBySQL(),
			$this->getLimitSQL(),
			$this->getOffsetSQL(),
			$this->getUnionSQL()
		);

		return $this->buildSQL($sql);
	}

	/**
	 * Generate the SQL for a DELETE query. Only the FROM, WHERE, ORDER BY, and LIMIT clauses are included.
	 * RETURNING is not supported.
	 * Note that aliases are supported only on MySQL >= 8.0.24 and MariaDB >= 11.6.
	 *
	 * @see https://mariadb.com/docs/server/reference/sql-statements/data-manipulation/changing-deleting-data/delete
	 * @see https://dev.mysql.com/doc/refman/8.4/en/delete.html
	 *
	 * @return string DELETE query.
	 */
	public function deleteSQL() {
		$sql = array_merge(
			$this->getFromSQL(),
			$this->getWhereSQL(),
			$this->getOrderBySQL(),
			$this->getLimitSQL()
		);

		return 'DELETE ' . $this->buildSQL($sql);
	}

	/**
	 * Build the SQL query from the given parts.
	 *
	 * @param array $sql The SQL query parts.
	 *
	 * @return string SQL query.
	 */
	private function buildSQL( $sql ) {
		return str_replace(
			[ '   ', '  ' ],
			' ',
			implode( ' ', $sql )
		);
	}
}
