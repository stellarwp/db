<?php
namespace StellarWP\DB\QueryBuilder;

namespace StellarWP\DB\QueryBuilder;

use StellarWP\DB\DB;
use StellarWP\DB\QueryBuilder\Concerns\CRUD;
use StellarWP\DB\Tests\DBTestCase;

/**
 * @since 2.19.0
 *
 * @covers CRUD
 */
final class CRUDTest extends DBTestCase
{
    /**
     * Truncate posts table to avoid duplicate records
     *
     * @since 2.19.0
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        $posts = DB::prefix('posts');

        DB::query("TRUNCATE TABLE $posts");
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testInsertShouldAddRowToDatabase()
    {
        $data = [
            'post_title' => 'Query Builder CRUD test',
            'post_type' => 'crud_test',
            'post_content' => 'Hello World!',
        ];

        DB::table('posts')->insert($data);

        $id = DB::last_insert_id();

        $post = DB::table('posts')
            ->select('post_title', 'post_type', 'post_content')
            ->where('ID', $id)
            ->get();

        $this->assertEquals($data['post_title'], $post->post_title);
        $this->assertEquals($data['post_type'], $post->post_type);
        $this->assertEquals($data['post_content'], $post->post_content);
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testUpdateShouldUpdateRowValuesInDatabase()
    {
        $data = [
            'post_title' => 'Query Builder CRUD test',
            'post_type' => 'crud_test',
            'post_content' => 'Hello World!',
        ];

        DB::table('posts')->insert($data);

        $id = DB::last_insert_id();

        $updated = [
            'post_title'   => 'Query Builder CRUD test - UPDATED',
            'post_type'    => 'crud_test-updated',
            'post_content' => 'Hello World! - UPDATED',
        ];

        DB::table('posts')
            ->where('ID', $id)
            ->update($updated);

        $post = DB::table('posts')
            ->select('ID', 'post_title', 'post_type', 'post_content')
            ->where('ID', $id)
            ->get();

        $this->assertEquals($id, $post->ID);
        $this->assertEquals($updated['post_title'], $post->post_title);
        $this->assertEquals($updated['post_type'], $post->post_type);
        $this->assertEquals($updated['post_content'], $post->post_content);
    }

    /**
     * @since 2.19.0
     *
     * @return void
     */
    public function testDeleteShouldDeleteRowInDatabase()
    {
        $data = [
            'post_title' => 'Query Builder CRUD test',
            'post_type' => 'crud_test',
            'post_content' => 'Hello World!',
        ];

        DB::table('posts')->insert($data);

        $id = DB::last_insert_id();

        $post = DB::table('posts')
            ->where('ID', $id)
            ->get();

        $this->assertNotNull($post);

        DB::table('posts')
            ->where('ID', $id)
            ->delete();

        $post = DB::table('posts')
            ->where('ID', $id)
            ->get();

        $this->assertNull($post);
    }

}
