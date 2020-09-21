<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\post;

use bizley\podium\api\events\PinEvent;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\services\post\PostPinner;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class PostPinnerTest extends TestCase
{
    private PostPinner $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new PostPinner();
        $this->eventsRaised = [];
    }

    public function testPinShouldTriggerBeforeAndAfterEventsWhenPinningIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostPinner::EVENT_BEFORE_PINNING] = $event instanceof PinEvent;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostPinner::EVENT_AFTER_PINNING] = $event instanceof PinEvent
                && 99 === $event->repository->getId();
        };
        Event::on(PostPinner::class, PostPinner::EVENT_AFTER_PINNING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('pin')->willReturn(true);
        $post->method('getId')->willReturn(99);
        $this->service->pin($post);

        self::assertTrue($this->eventsRaised[PostPinner::EVENT_BEFORE_PINNING]);
        self::assertTrue($this->eventsRaised[PostPinner::EVENT_AFTER_PINNING]);

        Event::off(PostPinner::class, PostPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        Event::off(PostPinner::class, PostPinner::EVENT_AFTER_PINNING, $afterHandler);
    }

    public function testPinShouldOnlyTriggerBeforeEventWhenPinningErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostPinner::EVENT_BEFORE_PINNING] = true;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostPinner::EVENT_AFTER_PINNING] = true;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_AFTER_PINNING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('pin')->willReturn(false);
        $this->service->pin($post);

        self::assertTrue($this->eventsRaised[PostPinner::EVENT_BEFORE_PINNING]);
        self::assertArrayNotHasKey(PostPinner::EVENT_AFTER_PINNING, $this->eventsRaised);

        Event::off(PostPinner::class, PostPinner::EVENT_BEFORE_PINNING, $beforeHandler);
        Event::off(PostPinner::class, PostPinner::EVENT_AFTER_PINNING, $afterHandler);
    }

    public function testPinShouldReturnErrorWhenEventPreventsPinning(): void
    {
        $handler = static function (PinEvent $event) {
            $event->canPin = false;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_BEFORE_PINNING, $handler);

        $result = $this->service->pin($this->createMock(PostRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostPinner::class, PostPinner::EVENT_BEFORE_PINNING, $handler);
    }

    public function testUnpinShouldTriggerBeforeAndAfterEventsWhenUnpinningIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[PostPinner::EVENT_BEFORE_UNPINNING] = $event instanceof PinEvent;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[PostPinner::EVENT_AFTER_UNPINNING] = $event instanceof PinEvent
                && 101 === $event->repository->getId();
        };
        Event::on(PostPinner::class, PostPinner::EVENT_AFTER_UNPINNING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('unpin')->willReturn(true);
        $post->method('getId')->willReturn(101);
        $this->service->unpin($post);

        self::assertTrue($this->eventsRaised[PostPinner::EVENT_BEFORE_UNPINNING]);
        self::assertTrue($this->eventsRaised[PostPinner::EVENT_AFTER_UNPINNING]);

        Event::off(PostPinner::class, PostPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        Event::off(PostPinner::class, PostPinner::EVENT_AFTER_UNPINNING, $afterHandler);
    }

    public function testUnpinShouldOnlyTriggerBeforeEventWhenUnpinningErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[PostPinner::EVENT_BEFORE_UNPINNING] = true;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[PostPinner::EVENT_AFTER_UNPINNING] = true;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_AFTER_UNPINNING, $afterHandler);

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('unpin')->willReturn(false);
        $this->service->unpin($post);

        self::assertTrue($this->eventsRaised[PostPinner::EVENT_BEFORE_UNPINNING]);
        self::assertArrayNotHasKey(PostPinner::EVENT_AFTER_UNPINNING, $this->eventsRaised);

        Event::off(PostPinner::class, PostPinner::EVENT_BEFORE_UNPINNING, $beforeHandler);
        Event::off(PostPinner::class, PostPinner::EVENT_AFTER_UNPINNING, $afterHandler);
    }

    public function testUnpinShouldReturnErrorWhenEventPreventsUnpinning(): void
    {
        $handler = static function (PinEvent $event) {
            $event->canUnpin = false;
        };
        Event::on(PostPinner::class, PostPinner::EVENT_BEFORE_UNPINNING, $handler);

        $result = $this->service->unpin($this->createMock(PostRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(PostPinner::class, PostPinner::EVENT_BEFORE_UNPINNING, $handler);
    }
}
