<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\category;

use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\services\category\CategorySorter;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class CategorySorterTest extends AppTestCase
{
    private CategorySorter $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new CategorySorter();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testReplaceShouldTriggerBeforeAndAfterEventsWhenReplacingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategorySorter::EVENT_BEFORE_REPLACING] = $event instanceof SortEvent;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategorySorter::EVENT_AFTER_REPLACING] = true;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_AFTER_REPLACING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('getOrder')->willReturn(1);
        $category->method('setOrder')->willReturn(true);
        $this->service->replace($category, $category);

        self::assertTrue($this->eventsRaised[CategorySorter::EVENT_BEFORE_REPLACING]);
        self::assertTrue($this->eventsRaised[CategorySorter::EVENT_AFTER_REPLACING]);

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        Event::off(CategorySorter::class, CategorySorter::EVENT_AFTER_REPLACING, $afterHandler);
    }

    public function testReplaceShouldOnlyTriggerBeforeEventWhenReplacingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategorySorter::EVENT_BEFORE_REPLACING] = true;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategorySorter::EVENT_AFTER_REPLACING] = true;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_AFTER_REPLACING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('getOrder')->willReturn(1);
        $category->method('setOrder')->willReturn(false);
        $this->service->replace($category, $category);

        self::assertTrue($this->eventsRaised[CategorySorter::EVENT_BEFORE_REPLACING]);
        self::assertArrayNotHasKey(CategorySorter::EVENT_AFTER_REPLACING, $this->eventsRaised);

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        Event::off(CategorySorter::class, CategorySorter::EVENT_AFTER_REPLACING, $afterHandler);
    }

    public function testReplaceShouldReturnErrorWhenEventPreventsReplacing(): void
    {
        $handler = static function (SortEvent $event) {
            $event->canReplace = false;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_REPLACING, $handler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $result = $this->service->replace($category, $category);
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_REPLACING, $handler);
    }

    public function testSortShouldTriggerBeforeAndAfterEventsWhenSortingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[CategorySorter::EVENT_BEFORE_SORTING] = $event instanceof SortEvent;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategorySorter::EVENT_AFTER_SORTING] = true;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_AFTER_SORTING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('sort')->willReturn(true);
        $this->service->repositoryConfig = $category;
        $this->service->sort();

        self::assertTrue($this->eventsRaised[CategorySorter::EVENT_BEFORE_SORTING]);
        self::assertTrue($this->eventsRaised[CategorySorter::EVENT_AFTER_SORTING]);

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $beforeHandler);
        Event::off(CategorySorter::class, CategorySorter::EVENT_AFTER_SORTING, $afterHandler);
    }

    public function testSortShouldOnlyTriggerBeforeEventWhenSortingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[CategorySorter::EVENT_BEFORE_SORTING] = true;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[CategorySorter::EVENT_AFTER_SORTING] = true;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_AFTER_SORTING, $afterHandler);

        $category = $this->createMock(CategoryRepositoryInterface::class);
        $category->method('sort')->willReturn(false);
        $this->service->sort();

        self::assertTrue($this->eventsRaised[CategorySorter::EVENT_BEFORE_SORTING]);
        self::assertArrayNotHasKey(CategorySorter::EVENT_AFTER_SORTING, $this->eventsRaised);

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $beforeHandler);
        Event::off(CategorySorter::class, CategorySorter::EVENT_AFTER_SORTING, $afterHandler);
    }

    public function testSortShouldReturnErrorWhenEventPreventsSorting(): void
    {
        $handler = static function (SortEvent $event) {
            $event->canSort = false;
        };
        Event::on(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $handler);

        $result = $this->service->sort();
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(CategorySorter::class, CategorySorter::EVENT_BEFORE_SORTING, $handler);
    }
}
