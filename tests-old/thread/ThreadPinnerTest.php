<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\api\services\thread\ThreadPinner;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ThreadPinnerTest
 * @package bizley\podium\tests\thread
 */
class ThreadPinnerTest extends DbTestCase
{
    public array $fixtures = [
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
                'pinned' => false,
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
                'pinned' => true,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    private array $eventsRaised = [];

    public function testPin(): void
    {
        Event::on(
            ThreadPinner::class,
            ThreadPinner::EVENT_BEFORE_PINNING,
            function () {
                $this->eventsRaised[ThreadPinner::EVENT_BEFORE_PINNING] = true;
            }
        );
        Event::on(
            ThreadPinner::class,
            ThreadPinner::EVENT_AFTER_PINNING,
            function () {
                $this->eventsRaised[ThreadPinner::EVENT_AFTER_PINNING] = true;
            }
        );

        self::assertTrue($this->podium()->thread->pin(1)->getResult());
        self::assertEquals(1, ThreadRepo::findOne(1)->pinned);

        self::assertArrayHasKey(ThreadPinner::EVENT_BEFORE_PINNING, $this->eventsRaised);
        self::assertArrayHasKey(ThreadPinner::EVENT_AFTER_PINNING, $this->eventsRaised);
    }

    public function testPinEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canPin = false;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $handler);

        self::assertFalse($this->podium()->thread->pin(1)->getResult());
        self::assertEquals(0, ThreadRepo::findOne(1)->pinned);

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $handler);
    }

    public function testUnpin(): void
    {
        Event::on(
            ThreadPinner::class,
            ThreadPinner::EVENT_BEFORE_UNPINNING,
            function () {
                $this->eventsRaised[ThreadPinner::EVENT_BEFORE_UNPINNING] = true;
            }
        );
        Event::on(
            ThreadPinner::class,
            ThreadPinner::EVENT_AFTER_UNPINNING,
            function () {
                $this->eventsRaised[ThreadPinner::EVENT_AFTER_UNPINNING] = true;
            }
        );

        self::assertTrue($this->podium()->thread->unpin(2)->getResult());
        self::assertEquals(0, ThreadRepo::findOne(2)->pinned);

        self::assertArrayHasKey(ThreadPinner::EVENT_BEFORE_UNPINNING, $this->eventsRaised);
        self::assertArrayHasKey(ThreadPinner::EVENT_AFTER_UNPINNING, $this->eventsRaised);
    }

    public function testUnpinEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnpin = false;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $handler);

        self::assertFalse($this->podium()->thread->unpin(2)->getResult());
        self::assertEquals(1, ThreadRepo::findOne(2)->pinned);

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $handler);
    }
}
