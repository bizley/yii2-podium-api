<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\post;

use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\post\PostArchiver;
use Exception;
use PHPUnit\Framework\TestCase;

class PostArchiverTest extends TestCase
{
    private PostArchiver $service;

    protected function setUp(): void
    {
        $this->service = new PostArchiver();
    }

    public function testBeforeArchiveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeArchive());
    }

    public function testArchiveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->archive($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testArchiveShouldReturnErrorWhenArchivingErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('archive')->willReturn(false);
        $post->method('getErrors')->willReturn([1]);
        $result = $this->service->archive($post);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testArchiveShouldReturnSuccessWhenArchivingIsDone(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('archive')->willReturn(true);
        $result = $this->service->archive($post);

        self::assertTrue($result->getResult());
    }

    public function testArchiveShouldReturnErrorWhenArchivingThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('archive')->willThrowException(new Exception('exc'));
        $result = $this->service->archive($post);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testBeforeReviveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeRevive());
    }

    public function testReviveShouldReturnErrorWhenRepositoryIsWrong(): void
    {
        $result = $this->service->revive($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testReviveShouldReturnErrorWhenRevivingErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('revive')->willReturn(false);
        $post->method('getErrors')->willReturn([1]);
        $result = $this->service->revive($post);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testReviveShouldReturnSuccessWhenRevivingIsDone(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('revive')->willReturn(true);
        $result = $this->service->revive($post);

        self::assertTrue($result->getResult());
    }

    public function testReviveShouldReturnErrorWhenRevivingThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('revive')->willThrowException(new Exception('exc'));
        $result = $this->service->revive($post);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
