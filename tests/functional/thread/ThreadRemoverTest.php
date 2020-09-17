<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\thread;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadRemover;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class ThreadRemoverTest extends AppTestCase
{
    private ThreadRemover $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ThreadRemover();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testRemoveShouldTriggerBeforeAndAfterEventsWhenRemovingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING] = $event instanceof RemoveEvent;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('delete')->willReturn(true);
        $thread->method('isArchived')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $this->service->remove($thread);

        self::assertTrue($this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING]);
        self::assertTrue($this->eventsRaised[ThreadRemover::EVENT_AFTER_REMOVING]);

        Event::off(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenRemovingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('delete')->willReturn(false);
        $thread->method('isArchived')->willReturn(true);
        $this->service->remove($thread);

        self::assertTrue($this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(ThreadRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenThreadIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('delete')->willReturn(true);
        $thread->method('isArchived')->willReturn(false);
        $this->service->remove($thread);

        self::assertTrue($this->eventsRaised[ThreadRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(ThreadRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(ThreadRemover::class, ThreadRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldReturnErrorWhenEventPreventsRemoving(): void
    {
        $handler = static function (RemoveEvent $event) {
            $event->canRemove = false;
        };
        Event::on(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $handler);

        $result = $this->service->remove($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadRemover::class, ThreadRemover::EVENT_BEFORE_REMOVING, $handler);
    }
}
