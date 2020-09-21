<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\post;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\post\PostRemover;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class PostRemoverTest extends AppTestCase
{
    private PostRemover $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new PostRemover();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testRemoveShouldTriggerBeforeAndAfterEventsWhenRemovingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostRemover::EVENT_BEFORE_REMOVING] = $event instanceof RemoveEvent;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('delete')->willReturn(true);
        $post->method('isArchived')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($thread);
        $this->service->remove($post);

        self::assertTrue($this->eventsRaised[PostRemover::EVENT_BEFORE_REMOVING]);
        self::assertTrue($this->eventsRaised[PostRemover::EVENT_AFTER_REMOVING]);

        Event::off(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenRemovingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('delete')->willReturn(false);
        $post->method('isArchived')->willReturn(true);
        $this->service->remove($post);

        self::assertTrue($this->eventsRaised[PostRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(PostRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenPostIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('delete')->willReturn(true);
        $post->method('isArchived')->willReturn(false);
        $this->service->remove($post);

        self::assertTrue($this->eventsRaised[PostRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(PostRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(PostRemover::class, PostRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldReturnErrorWhenEventPreventsRemoving(): void
    {
        $handler = static function (RemoveEvent $event) {
            $event->canRemove = false;
        };
        Event::on(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $handler);

        $result = $this->service->remove($this->createMock(PostRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostRemover::class, PostRemover::EVENT_BEFORE_REMOVING, $handler);
    }
}
