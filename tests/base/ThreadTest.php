<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\tests\DbTestCase;
use yii\data\ActiveDataFilter;

/**
 * Class ThreadTest
 * @package bizley\podium\tests\base
 */
class ThreadTest extends DbTestCase
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
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread2',
                'slug' => 'thread2',
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

    public function testGetThreadById(): void
    {
        $thread = $this->podium()->thread->getThreadById(1);
        $this->assertEquals(1, $thread->getId());
    }

    public function testNonExistingThread(): void
    {
        $this->assertEmpty($this->podium()->thread->getThreadById(999));
    }

    public function testGetThreadsByFilterEmpty(): void
    {
        $threads = $this->podium()->thread->getThreads();
        $this->assertEquals(2, $threads->getTotalCount());
        $this->assertEquals([1, 2], $threads->getKeys());
    }

    public function testGetThreadsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => function () {
                return (new \yii\base\DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');
        $threads = $this->podium()->thread->getThreads($filter);
        $this->assertEquals(1, $threads->getTotalCount());
        $this->assertEquals([2], $threads->getKeys());
    }

    public function testDeleteThread(): void
    {
        $this->assertEquals(1, $this->podium()->thread->delete(Thread::findOne(1)));
        $this->assertEmpty($this->podium()->thread->getThreadById(1));
    }
}
