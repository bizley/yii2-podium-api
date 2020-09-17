<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\thread;

use bizley\podium\api\events\BookmarkEvent;
use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadBookmarker;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class ThreadBookmarkerTest extends TestCase
{
    private ThreadBookmarker $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ThreadBookmarker();
        $this->eventsRaised = [];
    }

    public function testMarkShouldTriggerBeforeAndAfterEventsWhenMarkingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ThreadBookmarker::EVENT_BEFORE_MARKING] = $event instanceof BookmarkEvent;
        };
        Event::on(ThreadBookmarker::class, ThreadBookmarker::EVENT_BEFORE_MARKING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ThreadBookmarker::EVENT_AFTER_MARKING] = $event instanceof BookmarkEvent;
        };
        Event::on(ThreadBookmarker::class, ThreadBookmarker::EVENT_AFTER_MARKING, $afterHandler);

        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willReturn(true);
        $this->service->repositoryConfig = $bookmark;
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getCreatedAt')->willReturn(2);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $this->service->mark($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($this->eventsRaised[ThreadBookmarker::EVENT_BEFORE_MARKING]);
        self::assertTrue($this->eventsRaised[ThreadBookmarker::EVENT_AFTER_MARKING]);

        Event::off(ThreadBookmarker::class, ThreadBookmarker::EVENT_BEFORE_MARKING, $beforeHandler);
        Event::off(ThreadBookmarker::class, ThreadBookmarker::EVENT_AFTER_MARKING, $afterHandler);
    }

    public function testMarkShouldOnlyTriggerBeforeEventWhenMarkingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ThreadBookmarker::EVENT_BEFORE_MARKING] = true;
        };
        Event::on(ThreadBookmarker::class, ThreadBookmarker::EVENT_BEFORE_MARKING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ThreadBookmarker::EVENT_AFTER_MARKING] = true;
        };
        Event::on(ThreadBookmarker::class, ThreadBookmarker::EVENT_AFTER_MARKING, $afterHandler);

        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willReturn(false);
        $this->service->repositoryConfig = $bookmark;
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getCreatedAt')->willReturn(2);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $this->service->mark($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($this->eventsRaised[ThreadBookmarker::EVENT_BEFORE_MARKING]);
        self::assertArrayNotHasKey(ThreadBookmarker::EVENT_AFTER_MARKING, $this->eventsRaised);

        Event::off(ThreadBookmarker::class, ThreadBookmarker::EVENT_BEFORE_MARKING, $beforeHandler);
        Event::off(ThreadBookmarker::class, ThreadBookmarker::EVENT_AFTER_MARKING, $afterHandler);
    }

    public function testMarkShouldReturnErrorWhenEventPreventsMarking(): void
    {
        $handler = static function (BookmarkEvent $event) {
            $event->canMark = false;
        };
        Event::on(ThreadBookmarker::class, ThreadBookmarker::EVENT_BEFORE_MARKING, $handler);

        $result = $this->service->mark(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ThreadBookmarker::class, ThreadBookmarker::EVENT_BEFORE_MARKING, $handler);
    }
}
