<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\post;

use bizley\podium\api\events\ThumbEvent;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThumbRepositoryInterface;
use bizley\podium\api\services\post\PostLiker;
use bizley\podium\tests\AppTestCase;
use Yii;
use yii\base\Event;
use yii\db\Connection;
use yii\db\Transaction;

class PostLikerTest extends AppTestCase
{
    private PostLiker $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new PostLiker();
        $this->eventsRaised = [];
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testThumbUpShouldTriggerBeforeAndAfterEventsWhenUpIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostLiker::EVENT_BEFORE_THUMB_UP] = $event instanceof ThumbEvent;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostLiker::EVENT_AFTER_THUMB_UP] = $event instanceof ThumbEvent;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_AFTER_THUMB_UP, $afterHandler);

        $thumb = $this->createMock(ThumbRepositoryInterface::class);
        $thumb->method('up')->willReturn(true);
        $thumb->method('fetchOne')->willReturn(true);
        $thumb->method('isUp')->willReturn(false);
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('updateCounters')->willReturn(true);
        $this->service->repositoryConfig = $thumb;
        $this->service->thumbUp($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($this->eventsRaised[PostLiker::EVENT_BEFORE_THUMB_UP]);
        self::assertTrue($this->eventsRaised[PostLiker::EVENT_AFTER_THUMB_UP]);

        Event::off(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $beforeHandler);
        Event::off(PostLiker::class, PostLiker::EVENT_AFTER_THUMB_UP, $afterHandler);
    }

    public function testThumbUpShouldOnlyTriggerBeforeEventWhenUpErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostLiker::EVENT_BEFORE_THUMB_UP] = true;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostLiker::EVENT_AFTER_THUMB_UP] = true;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_AFTER_THUMB_UP, $afterHandler);

        $thumb = $this->createMock(ThumbRepositoryInterface::class);
        $thumb->method('up')->willReturn(false);
        $thumb->method('fetchOne')->willReturn(true);
        $thumb->method('isUp')->willReturn(false);
        $this->service->repositoryConfig = $thumb;
        $this->service->thumbUp(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertTrue($this->eventsRaised[PostLiker::EVENT_BEFORE_THUMB_UP]);
        self::assertArrayNotHasKey(PostLiker::EVENT_AFTER_THUMB_UP, $this->eventsRaised);

        Event::off(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $beforeHandler);
        Event::off(PostLiker::class, PostLiker::EVENT_AFTER_THUMB_UP, $afterHandler);
    }

    public function testThumbUpShouldOnlyTriggerBeforeEventWhenIsUpIsTrue(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostLiker::EVENT_BEFORE_THUMB_UP] = true;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostLiker::EVENT_AFTER_THUMB_UP] = true;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_AFTER_THUMB_UP, $afterHandler);

        $thumb = $this->createMock(ThumbRepositoryInterface::class);
        $thumb->method('fetchOne')->willReturn(true);
        $thumb->method('isUp')->willReturn(true);
        $this->service->repositoryConfig = $thumb;
        $this->service->thumbUp(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertTrue($this->eventsRaised[PostLiker::EVENT_BEFORE_THUMB_UP]);
        self::assertArrayNotHasKey(PostLiker::EVENT_AFTER_THUMB_UP, $this->eventsRaised);

        Event::off(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $beforeHandler);
        Event::off(PostLiker::class, PostLiker::EVENT_AFTER_THUMB_UP, $afterHandler);
    }

    public function testThumbUpShouldReturnErrorWhenEventPreventsUp(): void
    {
        $handler = static function (ThumbEvent $event) {
            $event->canThumbUp = false;
        };
        Event::on(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $handler);

        $result = $this->service->thumbUp(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostLiker::class, PostLiker::EVENT_BEFORE_THUMB_UP, $handler);
    }
}
