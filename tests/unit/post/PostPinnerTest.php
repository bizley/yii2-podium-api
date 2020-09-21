<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\post;

use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\services\post\PostPinner;
use Exception;
use PHPUnit\Framework\TestCase;

class PostPinnerTest extends TestCase
{
    private PostPinner $service;

    protected function setUp(): void
    {
        $this->service = new PostPinner();
    }

    public function testBeforePinShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforePin());
    }

    public function testPinShouldReturnErrorWhenPostRepositoryIsWrong(): void
    {
        $result = $this->service->pin($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testPinShouldReturnErrorWhenPinningErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('pin')->willReturn(false);
        $post->method('getErrors')->willReturn([1]);
        $result = $this->service->pin($post);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testPinShouldReturnSuccessWhenPinningIsDone(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('pin')->willReturn(true);
        $result = $this->service->pin($post);

        self::assertTrue($result->getResult());
    }

    public function testPinShouldReturnErrorWhenPinningThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('pin')->willThrowException(new Exception('exc'));
        $result = $this->service->pin($post);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testBeforeUnpinShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeUnpin());
    }

    public function testUnpinShouldReturnErrorWhenPostRepositoryIsWrong(): void
    {
        $result = $this->service->unpin($this->createMock(RepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testUnpinShouldReturnErrorWhenUnpinningErrored(): void
    {
        $thread = $this->createMock(PostRepositoryInterface::class);
        $thread->method('unpin')->willReturn(false);
        $thread->method('getErrors')->willReturn([1]);
        $result = $this->service->unpin($thread);

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testUnpinShouldReturnSuccessWhenUnpinningIsDone(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('unpin')->willReturn(true);
        $result = $this->service->unpin($post);

        self::assertTrue($result->getResult());
    }

    public function testUnpinShouldReturnErrorWhenUnpinningThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('unpin')->willThrowException(new Exception('exc'));
        $result = $this->service->unpin($post);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }
}
