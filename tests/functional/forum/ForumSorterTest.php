<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\forum;

use bizley\podium\api\events\SortEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\services\forum\ForumSorter;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class ForumSorterTest extends AppTestCase
{
    private ForumSorter $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ForumSorter();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testReplaceShouldTriggerBeforeAndAfterEventsWhenReplacingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumSorter::EVENT_BEFORE_REPLACING] = $event instanceof SortEvent;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumSorter::EVENT_AFTER_REPLACING] = true;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_AFTER_REPLACING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('getOrder')->willReturn(1);
        $forum->method('setOrder')->willReturn(true);
        $this->service->replace($forum, $forum);

        self::assertTrue($this->eventsRaised[ForumSorter::EVENT_BEFORE_REPLACING]);
        self::assertTrue($this->eventsRaised[ForumSorter::EVENT_AFTER_REPLACING]);

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        Event::off(ForumSorter::class, ForumSorter::EVENT_AFTER_REPLACING, $afterHandler);
    }

    public function testReplaceShouldOnlyTriggerBeforeEventWhenReplacingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumSorter::EVENT_BEFORE_REPLACING] = true;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumSorter::EVENT_AFTER_REPLACING] = true;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_AFTER_REPLACING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('getOrder')->willReturn(1);
        $forum->method('setOrder')->willReturn(false);
        $this->service->replace($forum, $forum);

        self::assertTrue($this->eventsRaised[ForumSorter::EVENT_BEFORE_REPLACING]);
        self::assertArrayNotHasKey(ForumSorter::EVENT_AFTER_REPLACING, $this->eventsRaised);

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_REPLACING, $beforeHandler);
        Event::off(ForumSorter::class, ForumSorter::EVENT_AFTER_REPLACING, $afterHandler);
    }

    public function testReplaceShouldReturnErrorWhenEventPreventsReplacing(): void
    {
        $handler = static function (SortEvent $event) {
            $event->canReplace = false;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_REPLACING, $handler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $result = $this->service->replace($forum, $forum);
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_REPLACING, $handler);
    }

    public function testSortShouldTriggerBeforeAndAfterEventsWhenSortingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumSorter::EVENT_BEFORE_SORTING] = $event instanceof SortEvent;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumSorter::EVENT_AFTER_SORTING] = true;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_AFTER_SORTING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('sort')->willReturn(true);
        $this->service->repositoryConfig = $forum;
        $this->service->sort();

        self::assertTrue($this->eventsRaised[ForumSorter::EVENT_BEFORE_SORTING]);
        self::assertTrue($this->eventsRaised[ForumSorter::EVENT_AFTER_SORTING]);

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $beforeHandler);
        Event::off(ForumSorter::class, ForumSorter::EVENT_AFTER_SORTING, $afterHandler);
    }

    public function testSortShouldOnlyTriggerBeforeEventWhenSortingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumSorter::EVENT_BEFORE_SORTING] = true;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumSorter::EVENT_AFTER_SORTING] = true;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_AFTER_SORTING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('sort')->willReturn(false);
        $this->service->sort();

        self::assertTrue($this->eventsRaised[ForumSorter::EVENT_BEFORE_SORTING]);
        self::assertArrayNotHasKey(ForumSorter::EVENT_AFTER_SORTING, $this->eventsRaised);

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $beforeHandler);
        Event::off(ForumSorter::class, ForumSorter::EVENT_AFTER_SORTING, $afterHandler);
    }

    public function testSortShouldReturnErrorWhenEventPreventsSorting(): void
    {
        $handler = static function (SortEvent $event) {
            $event->canSort = false;
        };
        Event::on(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $handler);

        $result = $this->service->sort();
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumSorter::class, ForumSorter::EVENT_BEFORE_SORTING, $handler);
    }
}
