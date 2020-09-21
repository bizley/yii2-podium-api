<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\post;

use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\post\PostBuilder;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class PostBuilderTest extends AppTestCase
{
    private PostBuilder $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new PostBuilder();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testCreateShouldTriggerBeforeAndAfterEventsWhenCreatingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostBuilder::EVENT_BEFORE_CREATING] = $event instanceof BuildEvent;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostBuilder::EVENT_AFTER_CREATING] = $event instanceof BuildEvent
                && 99 === $event->repository->getId();
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_AFTER_CREATING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('create')->willReturn(true);
        $post->method('getId')->willReturn(99);
        $this->service->repositoryConfig = $post;
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $this->service->create($this->createMock(MemberRepositoryInterface::class), $thread);

        self::assertTrue($this->eventsRaised[PostBuilder::EVENT_BEFORE_CREATING]);
        self::assertTrue($this->eventsRaised[PostBuilder::EVENT_AFTER_CREATING]);

        Event::off(PostBuilder::class, PostBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        Event::off(PostBuilder::class, PostBuilder::EVENT_AFTER_CREATING, $afterHandler);
    }

    public function testCreateShouldOnlyTriggerBeforeEventWhenCreatingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostBuilder::EVENT_BEFORE_CREATING] = true;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostBuilder::EVENT_AFTER_CREATING] = true;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_AFTER_CREATING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('create')->willReturn(false);
        $this->service->repositoryConfig = $post;
        $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(ThreadRepositoryInterface::class)
        );

        self::assertTrue($this->eventsRaised[PostBuilder::EVENT_BEFORE_CREATING]);
        self::assertArrayNotHasKey(PostBuilder::EVENT_AFTER_CREATING, $this->eventsRaised);

        Event::off(PostBuilder::class, PostBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        Event::off(PostBuilder::class, PostBuilder::EVENT_AFTER_CREATING, $afterHandler);
    }

    public function testCreateShouldReturnErrorWhenEventPreventsCreating(): void
    {
        $handler = static function (BuildEvent $event) {
            $event->canCreate = false;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_BEFORE_CREATING, $handler);

        $result = $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(ThreadRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostBuilder::class, PostBuilder::EVENT_BEFORE_CREATING, $handler);
    }

    public function testEditShouldTriggerBeforeAndAfterEventsWhenEditingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostBuilder::EVENT_BEFORE_EDITING] = $event instanceof BuildEvent;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostBuilder::EVENT_AFTER_EDITING] = $event instanceof BuildEvent
                && 101 === $event->repository->getId();
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_AFTER_EDITING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('edit')->willReturn(true);
        $post->method('getId')->willReturn(101);
        $this->service->edit($post);

        self::assertTrue($this->eventsRaised[PostBuilder::EVENT_BEFORE_EDITING]);
        self::assertTrue($this->eventsRaised[PostBuilder::EVENT_AFTER_EDITING]);

        Event::off(PostBuilder::class, PostBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        Event::off(PostBuilder::class, PostBuilder::EVENT_AFTER_EDITING, $afterHandler);
    }

    public function testEditShouldOnlyTriggerBeforeEventWhenEditingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostBuilder::EVENT_BEFORE_EDITING] = true;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostBuilder::EVENT_AFTER_EDITING] = true;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_AFTER_EDITING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('edit')->willReturn(false);
        $this->service->edit($post);

        self::assertTrue($this->eventsRaised[PostBuilder::EVENT_BEFORE_EDITING]);
        self::assertArrayNotHasKey(PostBuilder::EVENT_AFTER_EDITING, $this->eventsRaised);

        Event::off(PostBuilder::class, PostBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        Event::off(PostBuilder::class, PostBuilder::EVENT_AFTER_EDITING, $afterHandler);
    }

    public function testEditShouldReturnErrorWhenEventPreventsEditing(): void
    {
        $handler = static function (BuildEvent $event) {
            $event->canEdit = false;
        };
        Event::on(PostBuilder::class, PostBuilder::EVENT_BEFORE_EDITING, $handler);

        $result = $this->service->edit($this->createMock(PostRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostBuilder::class, PostBuilder::EVENT_BEFORE_EDITING, $handler);
    }
}
