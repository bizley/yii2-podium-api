<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\thread;

use bizley\podium\api\events\LockEvent;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadLocker;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class ThreadLockerTest extends TestCase
{
    private ThreadLocker $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ThreadLocker();
        $this->eventsRaised = [];
    }

    public function testLockShouldTriggerBeforeAndAfterEventsWhenLockingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadLocker::EVENT_BEFORE_LOCKING] = $event instanceof LockEvent;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadLocker::EVENT_AFTER_LOCKING] = $event instanceof LockEvent
                && 99 === $event->repository->getId();
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_AFTER_LOCKING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('lock')->willReturn(true);
        $thread->method('getId')->willReturn(99);
        $this->service->lock($thread);

        self::assertTrue($this->eventsRaised[ThreadLocker::EVENT_BEFORE_LOCKING]);
        self::assertTrue($this->eventsRaised[ThreadLocker::EVENT_AFTER_LOCKING]);

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $beforeHandler);
        Event::off(ThreadLocker::class, ThreadLocker::EVENT_AFTER_LOCKING, $afterHandler);
    }

    public function testLockShouldOnlyTriggerBeforeEventWhenLockingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadLocker::EVENT_BEFORE_LOCKING] = true;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadLocker::EVENT_AFTER_LOCKING] = true;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_AFTER_LOCKING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('lock')->willReturn(false);
        $this->service->lock($thread);

        self::assertTrue($this->eventsRaised[ThreadLocker::EVENT_BEFORE_LOCKING]);
        self::assertArrayNotHasKey(ThreadLocker::EVENT_AFTER_LOCKING, $this->eventsRaised);

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $beforeHandler);
        Event::off(ThreadLocker::class, ThreadLocker::EVENT_AFTER_LOCKING, $afterHandler);
    }

    public function testLockShouldReturnErrorWhenEventPreventsLocking(): void
    {
        $handler = static function (LockEvent $event) {
            $event->canLock = false;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $handler);

        $result = $this->service->lock($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_LOCKING, $handler);
    }

    public function testUnlockShouldTriggerBeforeAndAfterEventsWhenUnlockingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadLocker::EVENT_BEFORE_UNLOCKING] = $event instanceof LockEvent;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadLocker::EVENT_AFTER_UNLOCKING] = $event instanceof LockEvent
                && 101 === $event->repository->getId();
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_AFTER_UNLOCKING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unlock')->willReturn(true);
        $thread->method('getId')->willReturn(101);
        $this->service->unlock($thread);

        self::assertTrue($this->eventsRaised[ThreadLocker::EVENT_BEFORE_UNLOCKING]);
        self::assertTrue($this->eventsRaised[ThreadLocker::EVENT_AFTER_UNLOCKING]);

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $beforeHandler);
        Event::off(ThreadLocker::class, ThreadLocker::EVENT_AFTER_UNLOCKING, $afterHandler);
    }

    public function testUnlockShouldOnlyTriggerBeforeEventWhenUnlockingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadLocker::EVENT_BEFORE_UNLOCKING] = true;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadLocker::EVENT_AFTER_UNLOCKING] = true;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_AFTER_UNLOCKING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unlock')->willReturn(false);
        $this->service->unlock($thread);

        self::assertTrue($this->eventsRaised[ThreadLocker::EVENT_BEFORE_UNLOCKING]);
        self::assertArrayNotHasKey(ThreadLocker::EVENT_AFTER_UNLOCKING, $this->eventsRaised);

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $beforeHandler);
        Event::off(ThreadLocker::class, ThreadLocker::EVENT_AFTER_UNLOCKING, $afterHandler);
    }

    public function testUnlockShouldReturnErrorWhenEventPreventsUnlocking(): void
    {
        $handler = static function (LockEvent $event) {
            $event->canUnlock = false;
        };
        Event::on(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $handler);

        $result = $this->service->unlock($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadLocker::class, ThreadLocker::EVENT_BEFORE_UNLOCKING, $handler);
    }
}
