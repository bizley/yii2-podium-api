<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\thread;

use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadArchiver;
use Exception;
use PHPUnit\Framework\TestCase;

class ThreadArchiverTest extends TestCase
{
    private ThreadArchiver $service;

    protected function setUp(): void
    {
        $this->service = new ThreadArchiver();
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
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('archive')->willReturn(false);
        $thread->method('getErrors')->willReturn([1]);
        $result = $this->service->archive($thread);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testArchiveShouldReturnSuccessWhenArchivingIsDone(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('archive')->willReturn(true);
        $result = $this->service->archive($thread);

        self::assertTrue($result->getResult());
    }

    public function testArchiveShouldReturnErrorWhenArchivingThrowsException(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('archive')->willThrowException(new Exception('exc'));
        $result = $this->service->archive($thread);

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
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('revive')->willReturn(false);
        $thread->method('getErrors')->willReturn([1]);
        $result = $this->service->revive($thread);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testReviveShouldReturnSuccessWhenRevivingIsDone(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('revive')->willReturn(true);
        $result = $this->service->revive($thread);

        self::assertTrue($result->getResult());
    }

    public function testReviveShouldReturnErrorWhenRevivingThrowsException(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('revive')->willThrowException(new Exception('exc'));
        $result = $this->service->revive($thread);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
