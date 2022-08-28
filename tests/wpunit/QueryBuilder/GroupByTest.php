<?php
namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\DB;
use StellarWP\DB\QueryBuilder\QueryBuilder;
use StellarWP\DB\Tests\DBTestCase;

final class GroupByTest extends DBTestCase
{
    public function testGroupBy()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->groupBy('ID');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' GROUP BY ID",
            $builder->getSQL()
        );
    }
}
