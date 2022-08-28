<?php
namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\DB;
use StellarWP\DB\QueryBuilder\QueryBuilder;
use InvalidArgumentException;
use StellarWP\DB\Tests\DBTestCase;

final class OrderByTest extends DBTestCase
{
    public function testOrderByDesc()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->orderBy('ID', 'DESC');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' ORDER BY ID DESC",
            $builder->getSQL()
        );
    }


    public function testOrderByAsc()
    {
        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->orderBy('ID', 'ASC');

        $this->assertContains(
            "SELECT * FROM posts WHERE post_status = 'published' ORDER BY ID ASC",
            $builder->getSQL()
        );
    }


    public function testReturnExceptionWhenBadOrderByDirectionArgumentIsUsed() {
        $this->expectException( InvalidArgumentException::class );

        $builder = new QueryBuilder();

        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->where('post_status', 'published')
            ->orderBy('ID', 'BANANAS');
    }
}
