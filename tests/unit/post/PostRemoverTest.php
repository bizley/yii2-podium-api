<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\post;

use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\post\PostRemover;
use bizley\podium\tests\AppTestCase;
use Exception;
use Yii;
use yii\db\Connection;
use yii\db\Transaction;

class PostRemoverTest extends AppTestCase
{
    private PostRemover $service;

    protected function setUp(): void
    {
        $this->service = new PostRemover();
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
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
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('delete')->willReturn(false);
        $result = $this->service->remove($post);

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testRemoveShouldReturnErrorWhenPostIsNotArchived(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(false);
        $post->method('delete')->willReturn(true);
        $result = $this->service->remove($post);

        self::assertFalse($result->getResult());
        self::assertSame('post.must.be.archived', $result->getErrors()['api']);
    }

    public function testRemoveShouldReturnSuccessWhenRemovingIsDone(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('delete')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($thread);
        $result = $this->service->remove($post);

        self::assertTrue($result->getResult());
    }

    public function testRemoveShouldReturnErrorWhenRemovingThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('delete')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($post);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testRemoveShouldReturnErrorWhenIsArchivedThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willThrowException(new Exception('exc'));
        $result = $this->service->remove($post);

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testRemoveShouldReturnErrorWhenUpdatingThreadCountersErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('delete')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(false);
        $post->method('getParent')->willReturn($thread);
        $result = $this->service->remove($post);

        self::assertFalse($result->getResult());
        self::assertSame('Error while updating thread counters!', $result->getErrors()['exception']->getMessage());
    }

    public function testRemoveShouldReturnErrorWhenUpdatingForumCountersErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('isArchived')->willReturn(true);
        $post->method('delete')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(false);
        $thread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($thread);
        $result = $this->service->remove($post);

        self::assertFalse($result->getResult());
        self::assertSame('Error while updating forum counters!', $result->getErrors()['exception']->getMessage());
    }
}
