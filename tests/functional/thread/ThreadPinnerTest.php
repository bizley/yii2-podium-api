<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\thread;

use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadPinner;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class ThreadPinnerTest extends TestCase
{
    private ThreadPinner $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ThreadPinner();
        $this->eventsRaised = [];
    }

    public function testPinShouldTriggerBeforeAndAfterEventsWhenPinningIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadPinner::EVENT_BEFORE_PINNING] = $event instanceof PinEvent;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadPinner::EVENT_AFTER_PINNING] = $event instanceof PinEvent
                && 99 === $event->repository->getId();
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_AFTER_PINNING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('pin')->willReturn(true);
        $thread->method('getId')->willReturn(99);
        $this->service->pin($thread);

        self::assertTrue($this->eventsRaised[ThreadPinner::EVENT_BEFORE_PINNING]);
        self::assertTrue($this->eventsRaised[ThreadPinner::EVENT_AFTER_PINNING]);

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        Event::off(ThreadPinner::class, ThreadPinner::EVENT_AFTER_PINNING, $afterHandler);
    }

    public function testPinShouldOnlyTriggerBeforeEventWhenPinningErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadPinner::EVENT_BEFORE_PINNING] = true;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadPinner::EVENT_AFTER_PINNING] = true;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_AFTER_PINNING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('pin')->willReturn(false);
        $this->service->pin($thread);

        self::assertTrue($this->eventsRaised[ThreadPinner::EVENT_BEFORE_PINNING]);
        self::assertArrayNotHasKey(ThreadPinner::EVENT_AFTER_PINNING, $this->eventsRaised);

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        Event::off(ThreadPinner::class, ThreadPinner::EVENT_AFTER_PINNING, $afterHandler);
    }

    public function testPinShouldReturnErrorWhenEventPreventsPinning(): void
    {
        $handler = static function (PinEvent $event) {
            $event->canPin = false;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $handler);

        $result = $this->service->pin($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_PINNING, $handler);
    }

    public function testUnpinShouldTriggerBeforeAndAfterEventsWhenUnpinningIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadPinner::EVENT_BEFORE_UNPINNING] = $event instanceof PinEvent;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadPinner::EVENT_AFTER_UNPINNING] = $event instanceof PinEvent
                && 101 === $event->repository->getId();
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_AFTER_UNPINNING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unpin')->willReturn(true);
        $thread->method('getId')->willReturn(101);
        $this->service->unpin($thread);

        self::assertTrue($this->eventsRaised[ThreadPinner::EVENT_BEFORE_UNPINNING]);
        self::assertTrue($this->eventsRaised[ThreadPinner::EVENT_AFTER_UNPINNING]);

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        Event::off(ThreadPinner::class, ThreadPinner::EVENT_AFTER_UNPINNING, $afterHandler);
    }

    public function testUnpinShouldOnlyTriggerBeforeEventWhenUnpinningErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadPinner::EVENT_BEFORE_UNPINNING] = true;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadPinner::EVENT_AFTER_UNPINNING] = true;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_AFTER_UNPINNING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unpin')->willReturn(false);
        $this->service->unpin($thread);

        self::assertTrue($this->eventsRaised[ThreadPinner::EVENT_BEFORE_UNPINNING]);
        self::assertArrayNotHasKey(ThreadPinner::EVENT_AFTER_UNPINNING, $this->eventsRaised);

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        Event::off(ThreadPinner::class, ThreadPinner::EVENT_AFTER_UNPINNING, $afterHandler);
    }

    public function testUnpinShouldReturnErrorWhenEventPreventsUnpinning(): void
    {
        $handler = static function (PinEvent $event) {
            $event->canUnpin = false;
        };
        Event::on(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $handler);

        $result = $this->service->unpin($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadPinner::class, ThreadPinner::EVENT_BEFORE_UNPINNING, $handler);
    }
}
