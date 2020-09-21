<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\post;

use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\PostRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\post\PostMover;
use bizley\podium\tests\AppTestCase;
use Exception;
use Yii;
use yii\db\Connection;
use yii\db\Transaction;

class PostMoverTest extends AppTestCase
{
    private PostMover $service;

    protected function setUp(): void
    {
        $this->service = new PostMover();
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testBeforeMoveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeMove());
    }

    public function testMoveShouldReturnErrorWhenPostRepositoryIsWrong(): void
    {
        $result = $this->service->move(
            $this->createMock(RepositoryInterface::class),
            $this->createMock(ThreadRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testMoveShouldReturnErrorWhenThreadRepositoryIsWrong(): void
    {
        $result = $this->service->move(
            $this->createMock(PostRepositoryInterface::class),
            $this->createMock(RepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testMoveShouldReturnErrorWhenMovingErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(false);
        $result = $this->service->move($post, $this->createMock(ThreadRepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testMoveShouldReturnSuccessWhenMovingIsDone(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($thread);
        $result = $this->service->move($post, $thread);

        self::assertTrue($result->getResult());
    }

    public function testMoveShouldReturnErrorWhenMovingThrowsException(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willThrowException(new Exception('exc'));
        $result = $this->service->move($post, $this->createMock(ThreadRepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testMoveShouldReturnErrorWhenUpdatingOldThreadCountersErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(false);
        $post->method('getParent')->willReturn($thread);
        $result = $this->service->move($post, $thread);

        self::assertFalse($result->getResult());
        self::assertSame(
            'Error while updating old thread counters!',
            $result->getErrors()['exception']->getMessage()
        );
    }

    public function testMoveShouldReturnErrorWhenUpdatingOldForumCountersErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(true);
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(false);
        $thread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($thread);
        $result = $this->service->move($post, $thread);

        self::assertFalse($result->getResult());
        self::assertSame(
            'Error while updating old forum counters!',
            $result->getErrors()['exception']->getMessage()
        );
    }

    public function testMoveShouldReturnErrorWhenUpdatingNewThreadCountersErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(true);
        $oldThread = $this->createMock(ThreadRepositoryInterface::class);
        $oldThread->method('updateCounters')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $oldThread->method('getParent')->willReturn($forum);
        $post->method('getParent')->willReturn($oldThread);

        $newThread = $this->createMock(ThreadRepositoryInterface::class);
        $newThread->method('updateCounters')->willReturn(false);

        $result = $this->service->move($post, $newThread);

        self::assertFalse($result->getResult());
        self::assertSame(
            'Error while updating new thread counters!',
            $result->getErrors()['exception']->getMessage()
        );
    }

    public function testMoveShouldReturnErrorWhenUpdatingNewForumCountersErrored(): void
    {
        $post = $this->createMock(PostRepositoryInterface::class);
        $post->method('move')->willReturn(true);
        $oldThread = $this->createMock(ThreadRepositoryInterface::class);
        $oldThread->method('updateCounters')->willReturn(true);
        $oldForum = $this->createMock(ForumRepositoryInterface::class);
        $oldForum->method('updateCounters')->willReturn(true);
        $oldThread->method('getParent')->willReturn($oldForum);
        $post->method('getParent')->willReturn($oldThread);

        $newThread = $this->createMock(ThreadRepositoryInterface::class);
        $newThread->method('updateCounters')->willReturn(true);
        $newForum = $this->createMock(ForumRepositoryInterface::class);
        $newForum->method('updateCounters')->willReturn(false);
        $newThread->method('getParent')->willReturn($newForum);

        $result = $this->service->move($post, $newThread);

        self::assertFalse($result->getResult());
        self::assertSame(
            'Error while updating new forum counters!',
            $result->getErrors()['exception']->getMessage()
        );
    }
}
