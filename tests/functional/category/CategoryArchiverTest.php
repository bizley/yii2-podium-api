<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\category;

use bizley\podium\api\events\ArchiveEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\services\category\CategoryArchiver;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class CategoryArchiverTest extends TestCase
{
    private CategoryArchiver $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new CategoryArchiver();
        $this->eventsRaised = [];
    }

    public function testArchiveShouldTriggerBeforeAndAfterEventsWhenArchiveIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = $event instanceof ArchiveEvent
                && 99 === $event->repository->getId();
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('archive')->willReturn(true);
        $category->method('getId')->willReturn(99);
        $this->service->archive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING]);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldOnlyTriggerBeforeEventWhenArchiveErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_ARCHIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('archive')->willReturn(false);
        $this->service->archive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_ARCHIVING]);
        self::assertArrayNotHasKey(CategoryArchiver::EVENT_AFTER_ARCHIVING, $this->eventsRaised);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_ARCHIVING, $afterHandler);
    }

    public function testArchiveShouldReturnErrorWhenEventPreventsArchiving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canArchive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);

        $result = $this->service->archive($this->createMock(CategoryRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_ARCHIVING, $handler);
    }

    public function testReviveShouldTriggerBeforeAndAfterEventsWhenReviveIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = $event instanceof ArchiveEvent;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = $event instanceof ArchiveEvent
                && 101 === $event->repository->getId();
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('revive')->willReturn(true);
        $category->method('getId')->willReturn(101);
        $this->service->revive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING]);
        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING]);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldOnlyTriggerBeforeEventWhenReviveErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryArchiver::EVENT_AFTER_REVIVING] = true;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('revive')->willReturn(false);
        $this->service->revive($category);

        self::assertTrue($this->eventsRaised[CategoryArchiver::EVENT_BEFORE_REVIVING]);
        self::assertArrayNotHasKey(CategoryArchiver::EVENT_AFTER_REVIVING, $this->eventsRaised);

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $beforeHandler);
        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_AFTER_REVIVING, $afterHandler);
    }

    public function testReviveShouldReturnErrorWhenEventPreventsReviving(): void
    {
        $handler = static function (ArchiveEvent $event) {
            $event->canRevive = false;
        };
        Event::on(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);

        $result = $this->service->revive($this->createMock(CategoryRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategoryArchiver::class, CategoryArchiver::EVENT_BEFORE_REVIVING, $handler);
    }
}
