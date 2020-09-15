<?php

declare(strict_types=1);

namespace bizley\podium\tests\functional\forum;

use bizley\podium\api\events\BuildEvent;
use bizley\podium\api\interfaces\CategoryRepositoryInterface;
use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\services\forum\ForumBuilder;
use PHPUnit\Framework\TestCase;
use yii\base\Event;

class ForumBuilderTest extends TestCase
{
    private ForumBuilder $service;

    private array $eventsRaised;

    protected function setUp(): void
    {
        $this->service = new ForumBuilder();
        $this->eventsRaised = [];
    }

    public function testCreateShouldTriggerBeforeAndAfterEventsWhenCreatingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumBuilder::EVENT_BEFORE_CREATING] = $event instanceof BuildEvent;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ForumBuilder::EVENT_AFTER_CREATING] = $event instanceof BuildEvent
                && 99 === $event->repository->getId();
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_AFTER_CREATING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('create')->willReturn(true);
        $forum->method('getId')->willReturn(99);
        $this->service->repositoryConfig = $forum;
        $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class)
        );

        self::assertTrue($this->eventsRaised[ForumBuilder::EVENT_BEFORE_CREATING]);
        self::assertTrue($this->eventsRaised[ForumBuilder::EVENT_AFTER_CREATING]);

        Event::off(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        Event::off(ForumBuilder::class, ForumBuilder::EVENT_AFTER_CREATING, $afterHandler);
    }

    public function testCreateShouldOnlyTriggerBeforeEventWhenCreatingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumBuilder::EVENT_BEFORE_CREATING] = true;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumBuilder::EVENT_AFTER_CREATING] = true;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_AFTER_CREATING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('create')->willReturn(false);
        $this->service->repositoryConfig = $forum;
        $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class)
        );

        self::assertTrue($this->eventsRaised[ForumBuilder::EVENT_BEFORE_CREATING]);
        self::assertArrayNotHasKey(ForumBuilder::EVENT_AFTER_CREATING, $this->eventsRaised);

        Event::off(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_CREATING, $beforeHandler);
        Event::off(ForumBuilder::class, ForumBuilder::EVENT_AFTER_CREATING, $afterHandler);
    }

    public function testCreateShouldReturnErrorWhenEventPreventsCreating(): void
    {
        $handler = static function (BuildEvent $event) {
            $event->canCreate = false;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_CREATING, $handler);

        $result = $this->service->create(
            $this->createMock(MemberRepositoryInterface::class),
            $this->createMock(CategoryRepositoryInterface::class)
        );
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_CREATING, $handler);
    }

    public function testEditShouldTriggerBeforeAndAfterEventsWhenEditingIsDone(): void
    {
        $beforeHandler = function ($event) {
            $this->eventsRaised[ForumBuilder::EVENT_BEFORE_EDITING] = $event instanceof BuildEvent;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        $afterHandler = function ($event) {
            $this->eventsRaised[ForumBuilder::EVENT_AFTER_EDITING] = $event instanceof BuildEvent
                && 101 === $event->repository->getId();
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_AFTER_EDITING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('edit')->willReturn(true);
        $forum->method('getId')->willReturn(101);
        $this->service->edit($forum);

        self::assertTrue($this->eventsRaised[ForumBuilder::EVENT_BEFORE_EDITING]);
        self::assertTrue($this->eventsRaised[ForumBuilder::EVENT_AFTER_EDITING]);

        Event::off(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        Event::off(ForumBuilder::class, ForumBuilder::EVENT_AFTER_EDITING, $afterHandler);
    }

    public function testEditShouldOnlyTriggerBeforeEventWhenEditingErrored(): void
    {
        $beforeHandler = function () {
            $this->eventsRaised[ForumBuilder::EVENT_BEFORE_EDITING] = true;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        $afterHandler = function () {
            $this->eventsRaised[ForumBuilder::EVENT_AFTER_EDITING] = true;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_AFTER_EDITING, $afterHandler);

        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('edit')->willReturn(false);
        $this->service->edit($forum);

        self::assertTrue($this->eventsRaised[ForumBuilder::EVENT_BEFORE_EDITING]);
        self::assertArrayNotHasKey(ForumBuilder::EVENT_AFTER_EDITING, $this->eventsRaised);

        Event::off(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_EDITING, $beforeHandler);
        Event::off(ForumBuilder::class, ForumBuilder::EVENT_AFTER_EDITING, $afterHandler);
    }

    public function testEditShouldReturnErrorWhenEventPreventsEditing(): void
    {
        $handler = static function (BuildEvent $event) {
            $event->canEdit = false;
        };
        Event::on(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_EDITING, $handler);

        $result = $this->service->edit($this->createMock(ForumRepositoryInterface::class));
        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());

        Event::off(ForumBuilder::class, ForumBuilder::EVENT_BEFORE_EDITING, $handler);
    }
}
