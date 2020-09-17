<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\thread;

use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadBuilder;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class ThreadBuilderTest extends AppTestCase
{
    private ThreadBuilder $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ThreadBuilder();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testCreateShouldTriggerBeforeAndAfterEventsWhenCreatingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadBuilder::EVENT_BEFORE_CREATING] = $event instanceof BuildEvent;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadBuilder::EVENT_AFTER_CREATING] = $event instanceof BuildEvent
                && 99 === $event->repository->getId();
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_CREATING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('create')->willReturn(true);
        $thread->method('getId')->willReturn(99);
        $this->service->repositoryConfig = $thread;
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $this->service->create($this->createMock(MemberRepositoryInterface::class), $forum);

        self::assertTrue($this->eventsRaised[ThreadBuilder::EVENT_BEFORE_CREATING]);
        self::assertTrue($this->eventsRaised[ThreadBuilder::EVENT_AFTER_CREATING]);

        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_CREATING, $afterHandler);
    }

    public function testCreateShouldOnlyTriggerBeforeEventWhenCreatingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadBuilder::EVENT_BEFORE_CREATING] = true;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadBuilder::EVENT_AFTER_CREATING] = true;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_CREATING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('create')->willReturn(false);
        $this->service->repositoryConfig = $thread;
        $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(ForumRepositoryInterface::class)
        );

        self::assertTrue($this->eventsRaised[ThreadBuilder::EVENT_BEFORE_CREATING]);
        self::assertArrayNotHasKey(ThreadBuilder::EVENT_AFTER_CREATING, $this->eventsRaised);

        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_CREATING, $afterHandler);
    }

    public function testCreateShouldReturnErrorWhenEventPreventsCreating(): void
    {
        $handler = static function (BuildEvent $event) {
            $event->canCreate = false;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_CREATING, $handler);

        $result = $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(ForumRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_CREATING, $handler);
    }

    public function testEditShouldTriggerBeforeAndAfterEventsWhenEditingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadBuilder::EVENT_BEFORE_EDITING] = $event instanceof BuildEvent;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadBuilder::EVENT_AFTER_EDITING] = $event instanceof BuildEvent
                && 101 === $event->repository->getId();
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_EDITING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('edit')->willReturn(true);
        $thread->method('getId')->willReturn(101);
        $this->service->edit($thread);

        self::assertTrue($this->eventsRaised[ThreadBuilder::EVENT_BEFORE_EDITING]);
        self::assertTrue($this->eventsRaised[ThreadBuilder::EVENT_AFTER_EDITING]);

        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_EDITING, $afterHandler);
    }

    public function testEditShouldOnlyTriggerBeforeEventWhenEditingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadBuilder::EVENT_BEFORE_EDITING] = true;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadBuilder::EVENT_AFTER_EDITING] = true;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_EDITING, $afterHandler);

        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('edit')->willReturn(false);
        $this->service->edit($thread);

        self::assertTrue($this->eventsRaised[ThreadBuilder::EVENT_BEFORE_EDITING]);
        self::assertArrayNotHasKey(ThreadBuilder::EVENT_AFTER_EDITING, $this->eventsRaised);

        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_AFTER_EDITING, $afterHandler);
    }

    public function testEditShouldReturnErrorWhenEventPreventsEditing(): void
    {
        $handler = static function (BuildEvent $event) {
            $event->canEdit = false;
        };
        Event::on(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_EDITING, $handler);

        $result = $this->service->edit($this->createMock(ThreadRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadBuilder::class, ThreadBuilder::EVENT_BEFORE_EDITING, $handler);
    }
}
