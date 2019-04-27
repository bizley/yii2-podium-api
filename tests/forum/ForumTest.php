<?php

declare(strict_types=1);

namespace bizley\podium\tests\forum;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\tests\DbTestCase;
use yii\base\DynamicModel;
use yii\data\ActiveDataFilter;

/**
 * Class ForumTest
 * @package bizley\podium\tests\forum
 */
class ForumTest extends DbTestCase
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
                'archived' => false,
                'posts_count' => 5,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum2',
                'slug' => 'forum2',
                'sort' => 4,
                'archived' => true,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    public function testGetForumById(): void
    {
        $forum = $this->podium()->forum->getById(1);
        $this->assertEquals(1, $forum->getId());
    }

    public function testNonExistingForum(): void
    {
        $this->assertEmpty($this->podium()->forum->getById(999));
    }

    public function testGetForumsByFilterEmpty(): void
    {
        $forums = $this->podium()->forum->getAll();
        $this->assertEquals(2, $forums->getTotalCount());
        $this->assertEquals([1, 2], $forums->getKeys());
    }

    public function testGetForumsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => static function () {
                return (new DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');

        $forums = $this->podium()->forum->getAll($filter);

        $this->assertEquals(1, $forums->getTotalCount());
        $this->assertEquals([2], $forums->getKeys());
    }

    public function testGetPostsCount(): void
    {
        $this->assertEquals(5, $this->podium()->forum->getById(1)->getPostsCount());
    }

    public function testIsArchived(): void
    {
        $this->assertFalse($this->podium()->forum->getById(1)->isArchived());
        $this->assertTrue($this->podium()->forum->getById(2)->isArchived());
    }
}
