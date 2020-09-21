<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\post;

use bizley\podium\api\events\MoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\post\PostMover;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class PostMoverTest extends AppTestCase
{
    private PostMover $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new PostMover();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testMoveShouldTriggerBeforeAndAfterEventsWhenMovingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostMover::EVENT_BEFORE_MOVING] = $event instanceof MoveEvent;
        };
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostMover::EVENT_AFTER_MOVING] = true;
        };
        Event::on(PostMover::class, PostMover::EVENT_AFTER_MOVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($thread);
        $this->service->move($post, $thread);

        self::assertTrue($this->eventsRaised[PostMover::EVENT_BEFORE_MOVING]);
        self::assertTrue($this->eventsRaised[PostMover::EVENT_AFTER_MOVING]);

        Event::off(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $beforeHandler);
        Event::off(PostMover::class, PostMover::EVENT_AFTER_MOVING, $afterHandler);
    }

    public function testMoveShouldOnlyTriggerBeforeEventWhenMovingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostMover::EVENT_BEFORE_MOVING] = true;
        };
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostMover::EVENT_AFTER_MOVING] = true;
        };
        Event::on(PostMover::class, PostMover::EVENT_AFTER_MOVING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(false);
        $this->service->move($post, $this->createMock(ThreadRepositoryInterface::class));

        self::assertTrue($this->eventsRaised[PostMover::EVENT_BEFORE_MOVING]);
        self::assertArrayNotHasKey(PostMover::EVENT_AFTER_MOVING, $this->eventsRaised);

        Event::off(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $beforeHandler);
        Event::off(PostMover::class, PostMover::EVENT_AFTER_MOVING, $afterHandler);
    }

    public function testMoveShouldReturnErrorWhenEventPreventsMoving(): void
    {
        $handler = static function (MoveEvent $event) {
            $event->canMove = false;
        };
        Event::on(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $handler);

        $result = $this->service->move(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(ThreadRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostMover::class, PostMover::EVENT_BEFORE_MOVING, $handler);
    }
}
