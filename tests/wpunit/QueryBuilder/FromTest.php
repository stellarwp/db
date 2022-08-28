<?php
namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\DB;
use StellarWP\DB\QueryBuilder\QueryBuilder;
use StellarWP\DB\Tests\DBTestCase;

final class FromTest extends DBTestCase
{

    public function testFrom()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'));

        $this->assertContains(
            'SELECT * FROM posts',
            $builder->getSQL()
        );
    }


    public function testFromAlias()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'), 'donations');

        $this->assertContains(
            'SELECT * FROM posts AS donations',
            $builder->getSQL()
        );
    }

    public function testMultipleFrom()
    {
        $builder = new QueryBuilder();
        $builder
            ->select('*')
            ->from(DB::raw('posts'))
            ->from(DB::raw('postmeta'));

        $this->assertContains(
            'SELECT * FROM posts, postmeta',
            $builder->getSQL()
        );
    }

}
