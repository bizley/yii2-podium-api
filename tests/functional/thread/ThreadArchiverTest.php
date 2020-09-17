<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\thread;

use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadArchiver;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class ThreadArchiverTest extends TestCase
{
    private ThreadArchiver $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ThreadArchiver();
        $this->eventsRaised = [];
    }

    public function testArchiveShouldTriggerBeforeAndAfterEventsWhenArchivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadArchiver::EVENT_BEFORE_ARCHIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadArchiver::EVENT_AFTER_ARCHIVING] = $event instanceof ArchiveEvent
                && 99 === $event->repository->getId();
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('archive')->willReturn(true);
        $thread->method('getId')->willReturn(99);
        $this->service->archive($thread);

        self::assertTrue($this->eventsRaised[ThreadArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertTrue($this->eventsRaised[ThreadArchiver::EVENT_AFTER_ARCHIVING]);

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenArchivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('archive')->willReturn(false);
        $this->service->archive($thread);

        self::assertTrue($this->eventsRaised[ThreadArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(ThreadArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldReturnErrorWhenEventPreventsArchiving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canArchive = false;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $result = $this->service->archive($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testReviveShouldTriggerBeforeAndAfterEventsWhenRevivingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadArchiver::EVENT_BEFORE_REVIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadArchiver::EVENT_AFTER_REVIVING] = $event instanceof ArchiveEvent
                && 101 === $event->repository->getId();
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('revive')->willReturn(true);
        $thread->method('getId')->willReturn(101);
        $this->service->revive($thread);

        self::assertTrue($this->eventsRaised[ThreadArchiver::EVENT_BEFORE_REVIVING]);
        self::assertTrue($this->eventsRaised[ThreadArchiver::EVENT_AFTER_REVIVING]);

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenRevivingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('revive')->willReturn(false);
        $this->service->revive($thread);

        self::assertTrue($this->eventsRaised[ThreadArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(ThreadArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldReturnErrorWhenEventPreventsReviving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canRevive = false;
        };
        Event::on(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $handler);

        $result = $this->service->revive($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadArchiver::class, ThreadArchiver::EVENT_BEFORE_REVIVING, $handler);
    }
}
