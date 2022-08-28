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

		// Trim double spaces added by DB::prepare
		return str_replace(
			[ '   ', '  ' ],
			' ',
			implode( ' ', $sql )
		);
	}
}
