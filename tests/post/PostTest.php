<?php

declare(strict_types=1);

namespace bizley\podium\tests\post;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\post\Post;
use bizley\podium\tests\DbTestCase;
use yii\base\NotSupportedException;
use yii\data\ActiveDataFilter;

/**
 * Class PostTest
 * @package bizley\podium\tests\post
 */
class PostTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member',
                'slug' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_category' => [
            [
                'id' => 1,
                'author_id' => 1,
                'name' => 'category1',
                'slug' => 'category1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_forum' => [
            [
                'id' => 1,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum1',
                'slug' => 'forum1',
                'sort' => 8,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_thread' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread1',
                'slug' => 'thread1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_post' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'thread_id' => 1,
                'author_id' => 1,
                'content' => 'post2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetPostById(): void
    {
        $post = $this->podium()->post->getPostById(1);
        $this->assertEquals(1, $post->getId());
    }

    public function testNonExistingPost(): void
    {
        $this->assertEmpty($this->podium()->post->getPostById(999));
    }

    public function testGetPostsByFilterEmpty(): void
    {
        $posts = $this->podium()->post->getPosts();
        $this->assertEquals(2, $posts->getTotalCount());
        $this->assertEquals([1, 2], $posts->getKeys());
    }

    public function testGetPostsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $posts = $this->podium()->post->getPosts($filter);
        $this->assertEquals(1, $posts->getTotalCount());
        $this->assertEquals([2], $posts->getKeys());
    }

    public function testGetPostsCount(): void
    {
        $this->expectException(NotSupportedException::class);
        (new Post())->getPostsCount();
    }
}
