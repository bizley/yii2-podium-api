<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\category;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\services\category\CategoryRemover;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class CategoryRemoverTest extends TestCase
{
    private CategoryRemover $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new CategoryRemover();
        $this->eventsRaised = [];
    }

    public function testRemoveShouldTriggerBeforeAndAfterEventsWhenRemovingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING] = $event instanceof RemoveEvent;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('delete')->willReturn(true);
        $category->method('isArchived')->willReturn(true);
        $this->service->remove($category);

        self::assertTrue($this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING]);
        self::assertTrue($this->eventsRaised[CategoryRemover::EVENT_AFTER_REMOVING]);

        Event::off(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenRemovingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('delete')->willReturn(false);
        $category->method('isArchived')->willReturn(true);
        $this->service->remove($category);

        self::assertTrue($this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(CategoryRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenCategoryIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategoryRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('delete')->willReturn(true);
        $category->method('isArchived')->willReturn(false);
        $this->service->remove($category);

        self::assertTrue($this->eventsRaised[CategoryRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(CategoryRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(CategoryRemover::class, CategoryRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldReturnErrorWhenEventPreventsRemoving(): void
    {
        $handler = static function (RemoveEvent $event) {
            $event->canRemove = false;
        };
        Event::on(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $handler);

        $result = $this->service->remove($this->createMock(CategoryRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategoryRemover::class, CategoryRemover::EVENT_BEFORE_REMOVING, $handler);
    }
}
