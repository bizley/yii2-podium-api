<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\thread;

use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadPinner;
use Exception;
use PHPUnit\Framework\TestCase;

class ThreadPinnerTest extends TestCase
{
    private ThreadPinner $service;

    protected function setUp(): void
    {
        $this->service = new ThreadPinner();
    }

    public function testBeforePinShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforePin());
    }

    public function testPinShouldReturnErrorWhenPinningErrored(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('pin')->willReturn(false);
        $thread->method('getErrors')->willReturn([1]);
        $result = $this->service->pin($thread);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testPinShouldReturnSuccessWhenPinningIsDone(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('pin')->willReturn(true);
        $result = $this->service->pin($thread);

        self::assertTrue($result->getResult());
    }

    public function testPinShouldReturnErrorWhenPinningThrowsException(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('pin')->willThrowException(new Exception('exc'));
        $result = $this->service->pin($thread);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testBeforeUnpinShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeUnpin());
    }

    public function testUnpinShouldReturnErrorWhenUnpinningErrored(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unpin')->willReturn(false);
        $thread->method('getErrors')->willReturn([1]);
        $result = $this->service->unpin($thread);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testUnpinShouldReturnSuccessWhenUnpinningIsDone(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unpin')->willReturn(true);
        $result = $this->service->unpin($thread);

        self::assertTrue($result->getResult());
    }

    public function testUnpinShouldReturnErrorWhenUnpinningThrowsException(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('unpin')->willThrowException(new Exception('exc'));
        $result = $this->service->unpin($thread);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
