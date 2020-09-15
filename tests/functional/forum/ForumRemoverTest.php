<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\forum;

use bizley\podium\api\events\RemoveEvent;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\services\forum\ForumRemover;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class ForumRemoverTest extends TestCase
{
    private ForumRemover $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ForumRemover();
        $this->eventsRaised = [];
    }

    public function testRemoveShouldTriggerBeforeAndAfterEventsWhenRemovingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING] = $event instanceof RemoveEvent;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('delete')->willReturn(true);
        $forum->method('isArchived')->willReturn(true);
        $this->service->remove($forum);

        self::assertTrue($this->eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING]);
        self::assertTrue($this->eventsRaised[ForumRemover::EVENT_AFTER_REMOVING]);

        Event::off(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenRemovingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('delete')->willReturn(false);
        $forum->method('isArchived')->willReturn(true);
        $this->service->remove($forum);

        self::assertTrue($this->eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(ForumRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldOnlyTriggerBeforeEventWhenCategoryIsNotArchived(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING] = true;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumRemover::EVENT_AFTER_REMOVING] = true;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('delete')->willReturn(true);
        $forum->method('isArchived')->willReturn(false);
        $this->service->remove($forum);

        self::assertTrue($this->eventsRaised[ForumRemover::EVENT_BEFORE_REMOVING]);
        self::assertArrayNotHasKey(ForumRemover::EVENT_AFTER_REMOVING, $this->eventsRaised);

        Event::off(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $beforeHandler);
        Event::off(ForumRemover::class, ForumRemover::EVENT_AFTER_REMOVING, $afterHandler);
    }

    public function testRemoveShouldReturnErrorWhenEventPreventsRemoving(): void
    {
        $handler = static function (RemoveEvent $event) {
            $event->canRemove = false;
        };
        Event::on(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $handler);

        $result = $this->service->remove($this->createMock(ForumRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumRemover::class, ForumRemover::EVENT_BEFORE_REMOVING, $handler);
    }
}
