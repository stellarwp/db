<?php

namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\QueryBuilder\Concerns\WhereClause;

/**
 * @since 2.19.0
 */
class WhereQueryBuilder {
	use WhereClause;

	/**
	 * @return string[]
	 */
	public function getSQL() {
		return $this->getWhereSQL();
	}
}
