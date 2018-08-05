<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\forum\Forum;
use bizley\podium\tests\DbTestCase;
use yii\data\ActiveDataFilter;

/**
 * Class ForumTest
 * @package bizley\podium\tests\base
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
            [
                'id' => 2,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum2',
                'slug' => 'forum2',
                'sort' => 4,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
    }

    public function testGetForumById(): void
    {
        $forum = $this->podium()->forum->getForumById(1);
        $this->assertEquals(1, $forum->getId());
    }

    public function testNonExistingForum(): void
    {
        $this->assertEmpty($this->podium()->forum->getForumById(999));
    }

    public function testGetForumsByFilterEmpty(): void
    {
        $forums = $this->podium()->forum->getForums();
        $this->assertEquals(2, $forums->getTotalCount());
        $this->assertEquals([1, 2], $forums->getKeys());
    }

    public function testGetForumsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id' => null]))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $forums = $this->podium()->forum->getForums($filter);
        $this->assertEquals(1, $forums->getTotalCount());
        $this->assertEquals([2], $forums->getKeys());
    }

    public function testDeleteForum(): void
    {
        $this->assertEquals(1, $this->podium()->forum->delete(Forum::findOne(1)));
        $this->assertEmpty($this->podium()->forum->getForumById(1));
    }
}
