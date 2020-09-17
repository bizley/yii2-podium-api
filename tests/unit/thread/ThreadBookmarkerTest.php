<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\thread;

use bizley\podium\api\interfaces\BookmarkRepositoryInterface;
use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadBookmarker;
use Exception;
use PHPUnit\Framework\TestCase;

class ThreadBookmarkerTest extends TestCase
{
    private ThreadBookmarker $service;

    protected function setUp(): void
    {
        $this->service = new ThreadBookmarker();
    }

    public function testBeforeMarkShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeMark());
    }

    public function testMarkShouldReturnTrueIfMarkingIsDone(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->expects(self::never())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willReturn(true);
        $this->service->repositoryConfig = $bookmark;

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(2);

        $result = $this->service->mark($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($result->getResult());
    }

    public function testMarkShouldReturnTrueIfBookmarkIsSeenAfterPostCreation(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->expects(self::never())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(2);
        $bookmark->expects(self::never())->method('mark');
        $this->service->repositoryConfig = $bookmark;

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(1);

        $result = $this->service->mark($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($result->getResult());
    }

    public function testMarkShouldPrepareBookmarkWhenItDoesntExist(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(false);
        $bookmark->expects(self::once())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willReturn(true);
        $this->service->repositoryConfig = $bookmark;

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(2);

        $result = $this->service->mark($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertTrue($result->getResult());
    }

    public function testMarkShouldReturnErrorWhenMarkingThrowsException(): void
    {
        $bookmark = $this->createMock(BookmarkRepositoryInterface::class);
        $bookmark->method('fetchOne')->willReturn(true);
        $bookmark->expects(self::never())->method('prepare');
        $bookmark->method('getLastSeen')->willReturn(1);
        $bookmark->method('mark')->willThrowException(new Exception('exc'));
        $this->service->repositoryConfig = $bookmark;

        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('getParent')->willReturn($this->createMock(ThreadRepositoryInterface::class));
        $post->method('getCreatedAt')->willReturn(2);

        $result = $this->service->mark($post, $this->createMock(MemberRepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
