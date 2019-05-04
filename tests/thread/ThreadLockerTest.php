<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\thread\ThreadLocker;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ThreadLockerTest
 * @package bizley\podium\tests\thread
 */
class ThreadLockerTest extends DbTestCase
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
                'locked' => false,
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
                'locked' => true,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testLock(): void
    {
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, function () {
            $this->eventsRaised[ThreadLocker::EVENT_BEFORE_LOCKING] = true;
        });
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_AFTER_LOCKING, function () {
            $this->eventsRaised[ThreadLocker::EVENT_AFTER_LOCKING] = true;
        });

        $this->assertTrue($this->podium()->thread->lock(1)->result);
        $this->assertEquals(1, ThreadRepo::findOne(1)->locked);

        $this->assertArrayHasKey(ThreadLocker::EVENT_BEFORE_LOCKING, $this->eventsRaised);
        $this->assertArrayHasKey(ThreadLocker::EVENT_AFTER_LOCKING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testLockEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canLock = false;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $handler);

        $this->assertFalse($this->podium()->thread->lock(1)->result);
        $this->assertEquals(0, ThreadRepo::findOne(1)->locked);

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $handler);
    }

    public function testFailedLock(): void
    {
        $mock = $this->getMockBuilder(ThreadLocker::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->lock()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoThreadToLock(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->thread->lock(999);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testUnlock(): void
    {
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, function () {
            $this->eventsRaised[ThreadLocker::EVENT_BEFORE_UNLOCKING] = true;
        });
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_AFTER_UNLOCKING, function () {
            $this->eventsRaised[ThreadLocker::EVENT_AFTER_UNLOCKING] = true;
        });

        $this->assertTrue($this->podium()->thread->unlock(2)->result);
        $this->assertEquals(0, ThreadRepo::findOne(2)->locked);

        $this->assertArrayHasKey(ThreadLocker::EVENT_BEFORE_UNLOCKING, $this->eventsRaised);
        $this->assertArrayHasKey(ThreadLocker::EVENT_AFTER_UNLOCKING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testUnlockEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnlock = false;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $handler);

        $this->assertFalse($this->podium()->thread->unlock(2)->result);
        $this->assertEquals(1, ThreadRepo::findOne(2)->locked);

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $handler);
    }

    public function testFailedUnlock(): void
    {
        $mock = $this->getMockBuilder(ThreadLocker::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->unlock()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoThreadToUnlock(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->thread->unlock(999);
    }
}
