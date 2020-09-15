<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\forum;

use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\forum\ForumRemover;
use Exception;
use PHPUnit\Framework\TestCase;

class ForumRemoverTest extends TestCase
{
    private ForumRemover $service;

    protected function setUp(): void
    {
        $this->service = new ForumRemover();
    }

    public function testBeforeRemoveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeRemove());
    }

    public function testRemoveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->remove($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testRemoveShouldReturnErrorWhenRemovingErrored(): void
    {
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(true);
        $forum->method('delete')->willReturn(false);
        $result = $this->service->remove($forum);

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testRemoveShouldReturnErrorWhenCategoryIsNotArchived(): void
    {
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(false);
        $forum->method('delete')->willReturn(true);
        $result = $this->service->remove($forum);

        self::assertFalse($result->getResult());
        self::assertSame('forum.must.be.archived', $result->getErrors()['api']);
    }

    public function testRemoveShouldReturnSuccessWhenRemovingIsDone(): void
    {
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(true);
        $forum->method('delete')->willReturn(true);
        $result = $this->service->remove($forum);

        self::assertTrue($result->getResult());
    }

    public function testRemoveShouldReturnErrorWhenRemovingThrowsException(): void
    {
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willReturn(true);
        $forum->method('delete')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($forum);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testRemoveShouldReturnErrorWhenIsArchivedThrowsException(): void
    {
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('isArchived')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($forum);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
