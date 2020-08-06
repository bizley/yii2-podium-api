<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\interfaces\RemoverInterface;
use bizley\podium\api\repos\PostRepo;
use bizley\podium\api\repos\ThreadRepo;
use bizley\podium\api\services\thread\ThreadRemover;
use bizley\podium\tests\DbTestCase;
use Exception;
use yii\base\Event;

class ThreadRemoverTest extends DbTestCase
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
                'created_at' => 1,
                'updated_at' => 1,
                'archived' => true,
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
                'archived' => false,
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
        ],
    ];

    private array $eventsRaised = [];

    public function testRemove(): void
    {
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, function () {
            $this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING] = true;
        });
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, function () {
            $this->eventsRaised[ThreadRemover::EVENT_AFTER_REMOVING] = true;
        });

        self::assertTrue($this->podium()->thread->remove(1)->getResult());

        self::assertEmpty(ThreadRepo::findOne(1));
        self::assertEmpty(PostRepo::findOne(1));

        self::assertArrayHasKey(ThreadRemover::EVENT_BEFORE_REMOVING, $this->eventsRaised);
        self::assertArrayHasKey(ThreadRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);
    }

    public function testRemoveEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRemove = false;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $handler);

        self::assertFalse($this->podium()->thread->remove(1)->getResult());

        self::assertNotEmpty(ThreadRepo::findOne(1));
        self::assertNotEmpty(PostRepo::findOne(1));

        Event::off(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $handler);
    }

    public function testNonArchived(): void
    {
        self::assertFalse($this->podium()->thread->remove(2)->getResult());
        self::assertNotEmpty(ThreadRepo::findOne(2));
    }

    public function testFailedRemove(): void
    {
        $mock = $this->createMock(RemoverInterface::class);
        $mock->method('delete')->willReturn(false);
        $mock->method('isArchived')->willReturn(true);

        self::assertFalse($mock->remove(1)->getResult());
    }

    public function testExceptionRemove(): void
    {
        $mock = $this->createMock(RemoverInterface::class);
        $mock->method('delete')->willThrowException(new Exception());
        $mock->method('isArchived')->willReturn(true);

        self::assertFalse($mock->remove(1)->getResult());
    }

    public function testNoThreadToRemove(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->thread->remove(999);
    }
}
