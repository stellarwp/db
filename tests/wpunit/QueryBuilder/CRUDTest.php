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

	/**
	 * Tests if upsert() adds a row to the database.
	 *
	 * @return void
	 */
	public function testUpsertShouldAddRowToDatabase()
	{
		$data = [
			'post_title' => 'Query Builder CRUD test',
			'post_type' => 'crud_test',
			'post_content' => 'Hello World!',
		];

		DB::table('posts')->upsert($data);

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
	 * Tests if upsert() updates a row in the database.
	 *
	 * @return void
	 */
	public function testUpsertShouldUpdateRowInDatabase()
	{
		$data = [
			'post_title' => 'Query Builder CRUD test - upsert update',
			'post_type' => 'crud_test',
			'post_content' => 'Hello World from upsert!',
		];

		DB::table('posts')->insert($data);

		$original_id = DB::last_insert_id();

		$updated_data = [
			'post_title' => 'Query Builder CRUD test - upsert update',
			'post_type' => 'crud_test',
			'post_content' => 'Hello World from upsert! - updated',
		];

		$match = [
			'post_title',
		];

		DB::table('posts')->upsert( $updated_data, $match );

		$post = DB::table('posts')
			->select('post_title', 'post_type', 'post_content')
			->where('ID', $original_id)
			->get();

		$this->assertEquals($updated_data['post_content'], $post->post_content);

		// Test multiple match columns
		$further_updated_data = [
			'post_title' => 'Query Builder CRUD test - upsert update',
			'post_type' => 'crud_test',
			'post_content' => 'Hello World from upsert! - updated even more!',
		];

		$match = [
			'post_title',
			'post_type',
		];

		DB::table('posts')->upsert( $further_updated_data, $match );

		$post = DB::table('posts')
			->select('post_title', 'post_type', 'post_content')
			->where('ID', $original_id)
			->get();

		$this->assertEquals($further_updated_data['post_content'], $post->post_content);
	}

	/**
	 * Tests if delete() can delete with both ORDER BY and LIMIT clauses.
	 *
	 * @return void
	 */
	public function testDeleteShouldWorkWithOrderByAndLimit()
	{
		// Insert multiple posts
		$posts = [
			['post_title' => 'Delete Combined A', 'post_type' => 'delete_combined_test', 'post_content' => 'Content A'],
			['post_title' => 'Delete Combined B', 'post_type' => 'delete_combined_test', 'post_content' => 'Content B'],
			['post_title' => 'Delete Combined C', 'post_type' => 'delete_combined_test', 'post_content' => 'Content C'],
			['post_title' => 'Delete Combined D', 'post_type' => 'delete_combined_test', 'post_content' => 'Content D'],
		];

		$ids = $this->insert_posts($posts);
		$this->assert_posts_exist($posts, $ids);

		// Delete the 2 oldest posts (lowest IDs)
		DB::table('posts')
			->where('post_type', 'delete_combined_test')
			->orderBy('ID', 'ASC')
			->limit(2)
			->delete();

		$foundPosts = DB::table('posts')
		                ->select('post_title', 'post_type', 'post_content')
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);
		unset($posts[0], $posts[1]);

		$this->assertEquals(array_values($posts), $foundPosts);
	}

	/**
	 * Tests if delete() works with complex WHERE clauses using whereIn.
	 *
	 * @return void
	 */
	public function testDeleteShouldWorkWithWhereIn()
	{
		// Insert multiple posts
		$posts = [
			['post_title' => 'Delete WhereIn 1', 'post_type' => 'delete_wherein_test', 'post_content' => 'Content 1'],
			['post_title' => 'Delete WhereIn 2', 'post_type' => 'delete_wherein_test', 'post_content' => 'Content 2'],
			['post_title' => 'Delete WhereIn 3', 'post_type' => 'delete_wherein_test', 'post_content' => 'Content 3'],
			['post_title' => 'Delete WhereIn 4', 'post_type' => 'delete_wherein_test', 'post_content' => 'Content 4'],
		];

		$ids = $this->insert_posts($posts);
		$this->assert_posts_exist($posts, $ids);

		// Delete posts with specific IDs using whereIn
		DB::table('posts')
			->whereIn('ID', [$ids[0], $ids[2]])
			->delete();

		$foundPosts = DB::table('posts')
		                ->select('post_title', 'post_type', 'post_content')
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);
		unset($posts[0], $posts[2]);

		$this->assertEquals(array_values($posts), $foundPosts);
	}

	/**
	 * Tests if delete() works with complex WHERE clauses using whereBetween.
	 *
	 * @return void
	 */
	public function testDeleteShouldWorkWithWhereBetween()
	{
		// Insert posts with specific menu_order values
		$posts = [
			['post_title' => 'Delete Between 1', 'post_type' => 'delete_between_test', 'menu_order' => 10],
			['post_title' => 'Delete Between 2', 'post_type' => 'delete_between_test', 'menu_order' => 20],
			['post_title' => 'Delete Between 3', 'post_type' => 'delete_between_test', 'menu_order' => 30],
			['post_title' => 'Delete Between 4', 'post_type' => 'delete_between_test', 'menu_order' => 40],
		];

		$ids = $this->insert_posts($posts);
		$this->assert_posts_exist($posts, $ids);

		// Delete posts with menu_order between 15 and 35
		DB::table('posts')
			->where('post_type', 'delete_between_test')
			->whereBetween('menu_order', 15, 35)
			->delete();

		$foundPosts = DB::table('posts')
		                ->select('post_title', 'post_type', 'menu_order')
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);
		unset($posts[1], $posts[2]);

		$this->assertEquals(array_values($posts), $foundPosts);
	}

	/**
	 * Tests if delete() works with multiple WHERE conditions.
	 *
	 * @return void
	 */
	public function testDeleteShouldWorkWithMultipleWhereConditions()
	{
		// Insert multiple posts with different types
		$posts = [
			['post_title' => 'Delete Multi 1', 'post_type' => 'type_a', 'post_status' => 'publish'],
			['post_title' => 'Delete Multi 2', 'post_type' => 'type_a', 'post_status' => 'draft'],
			['post_title' => 'Delete Multi 3', 'post_type' => 'type_b', 'post_status' => 'publish'],
			['post_title' => 'Delete Multi 4', 'post_type' => 'type_b', 'post_status' => 'draft'],
		];

		$ids = $this->insert_posts($posts);
		$this->assert_posts_exist($posts, $ids);

		// Delete only posts with type_a AND status publish
		DB::table('posts')
			->where('post_type', 'type_a')
			->where('post_status', 'publish')
			->where('post_title', 'Delete Multi 1')
			->delete();

		$foundPosts = DB::table('posts')
		                ->select('post_title', 'post_type', 'post_status')
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);
		unset($posts[0]);

		$this->assertEquals(array_values($posts), $foundPosts);
	}

	/**
	 * Tests if delete() works with whereLike clause.
	 *
	 * @return void
	 */
	public function testDeleteShouldWorkWithWhereLike()
	{
		// Insert multiple posts with similar titles
		$posts = [
			['post_title' => 'Product: Widget ABC', 'post_type' => 'delete_like_test', 'post_content' => 'Content 1'],
			['post_title' => 'Product: WidgetXYZ', 'post_type' => 'delete_like_test', 'post_content' => 'Content 2'],
			['post_title' => 'Product: Gadget ABC', 'post_type' => 'delete_like_test', 'post_content' => 'Content 3'],
			['post_title' => 'Service: Widget ABC', 'post_type' => 'delete_like_test', 'post_content' => 'Content 4'],
		];

		$ids = $this->insert_posts($posts);
		$this->assert_posts_exist($posts, $ids);

		// Delete all posts with titles containing "Widget"
		DB::table('posts')
			->where('post_type', 'delete_like_test')
			->whereLike('post_title', '%Widget%')
			->delete();

		$foundPosts = DB::table('posts')
		                ->select('post_title', 'post_type', 'post_content')
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);

		$this->assertEquals([$posts[2]], $foundPosts);
	}

	/**
	 * Tests if delete() works with whereLike using wildcard prefix.
	 *
	 * @return void
	 */
	public function testDeleteShouldWorkWithWhereLikePrefix()
	{
		// Insert multiple posts with different prefixes
		$posts = [
			['post_title' => 'Draft: Important Document', 'post_type' => 'delete_prefix_test', 'post_content' => 'Content 1'],
			['post_title' => 'Draft: Meeting Notes', 'post_type' => 'delete_prefix_test', 'post_content' => 'Content 2'],
			['post_title' => 'Final: Important Document Not Draft', 'post_type' => 'delete_prefix_test', 'post_content' => 'Content 3'],
			['post_title' => 'Review: Meeting Notes', 'post_type' => 'delete_prefix_test', 'post_content' => 'Content 4'],
		];

		$ids = $this->insert_posts($posts);
		$this->assert_posts_exist($posts, $ids);

		// Delete all posts starting with "Draft:"
		DB::table('posts')
			->where('post_type', 'delete_prefix_test')
			->whereLike('post_title', 'Draft:%')
			->delete();

		$foundPosts = DB::table('posts')
		                ->select('post_title', 'post_type', 'post_content')
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);

		$this->assertEquals([$posts[2], $posts[3]], $foundPosts);
	}

	/**
	 * Inserts multiple posts into the database and returns their IDs.
	 *
	 * @param array $posts An array of associative arrays, where each associative array represents a post to insert.
	 *
	 * @return array An array of IDs corresponding to the inserted posts.
	 */
	private function insert_posts( array $posts ): array {
		$ids = [];
		foreach ($posts as $post) {
			DB::table('posts')->insert($post);
			$ids[] = DB::last_insert_id();
		}

		return $ids;
	}

	/**
	 * Asserts that posts with the given IDs exist and match the specified data.
	 *
	 * @param array $posts An array of expected post data to validate against the database.
	 * @param array $ids An array of post IDs to check for existence in the database.
	 *
	 * @return void
	 */
	private function assert_posts_exist( array $posts, array $ids ) {
		$foundPosts = DB::table('posts')
		                ->select(...array_keys($posts[0]))
		                ->whereIn('ID', $ids)
		                ->getAll(ARRAY_A);

		$this->assertEquals($posts, $foundPosts);
	}
}
