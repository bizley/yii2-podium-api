<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\models\thread\ThreadRemover;
use bizley\podium\tests\DbTestCase;
use yii\base\DynamicModel;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataFilter;

/**
 * Class ThreadTest
 * @package bizley\podium\tests\thread
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

    public function testGetThreadById(): void
    {
        $thread = $this->podium()->thread->getById(1);
        $this->assertEquals(1, $thread->getId());
        $this->assertEquals(1, $thread->getCreatedAt());
    }

    public function testNonExistingThread(): void
    {
        $this->assertEmpty($this->podium()->thread->getById(999));
    }

    public function testGetThreadsByFilterEmpty(): void
    {
        $threads = $this->podium()->thread->getAll();
        $this->assertEquals(2, $threads->getTotalCount());
        $this->assertEquals([1, 2], $threads->getKeys());
    }

    public function testGetThreadsByFilter(): void
    {
        $filter = new ActiveDataFilter([
            'searchModel' => static function () {
                return (new DynamicModel(['id']))->addRule('id', 'integer');
            }
        ]);
        $filter->load(['filter' => ['id' => 2]], '');

        $threads = $this->podium()->thread->getAll($filter);

        $this->assertEquals(1, $threads->getTotalCount());
        $this->assertEquals([2], $threads->getKeys());
    }

    public function testGetThreadsByFilterWithSorter(): void
    {
        $threads = $this->podium()->thread->getAll(null, ['defaultOrder' => ['id' => SORT_DESC]]);
        $this->assertEquals(2, $threads->getTotalCount());
        $this->assertEquals([2, 1], $threads->getKeys());
    }

    public function testGetThreadsByFilterWithPagination(): void
    {
        $threads = $this->podium()->thread->getAll(null, null, ['defaultPageSize' => 1]);
        $this->assertEquals(2, $threads->getTotalCount());
        $this->assertEquals([1], $threads->getKeys());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testConvert(): void
    {
        $thread = Thread::findById(1);
        $this->assertInstanceOf(ThreadRemover::class, $thread->convert(ThreadRemover::class));
    }

    /**
     * @throws InvalidConfigException
     */
    public function testWrongConvert(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $thread = Thread::findById(1);
        $thread->convert('stdClass');
    }
}
