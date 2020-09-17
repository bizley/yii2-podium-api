<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\thread;

use bizley\podium\api\interfaces\ForumRepositoryInterface;
use bizley\podium\api\interfaces\RepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadMover;
use bizley\podium\tests\AppTestCase;
use Exception;
use Yii;
use yii\db\Connection;
use yii\db\Transaction;

class ThreadMoverTest extends AppTestCase
{
    private ThreadMover $service;

    protected function setUp(): void
    {
        $this->service = new ThreadMover();
        $connection = $this->createMock(Connection::class);
        $connection->method('beginTransaction')->willReturn($this->createMock(Transaction::class));
        Yii::$app->set('db', $connection);
    }

    public function testBeforeMoveShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeMove());
    }

    public function testMoveShouldReturnErrorWhenThreadRepositoryIsWrong(): void
    {
        $result = $this->service->move(
            $this->createMock(RepositoryInterface::class),
            $this->createMock(ForumRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testMoveShouldReturnErrorWhenForumRepositoryIsWrong(): void
    {
        $result = $this->service->move(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(RepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testMoveShouldReturnErrorWhenMovingErrored(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willReturn(false);
        $result = $this->service->move($thread, $this->createMock(ForumRepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertEmpty($result->getErrors());
    }

    public function testMoveShouldReturnSuccessWhenMovingIsDone(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($forum);
        $thread->method('updateCounters')->willReturn(true);
        $result = $this->service->move($thread, $forum);

        self::assertTrue($result->getResult());
    }

    public function testMoveShouldReturnErrorWhenMovingThrowsException(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willThrowException(new Exception('exc'));
        $result = $this->service->move($thread, $this->createMock(ForumRepositoryInterface::class));

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testMoveShouldReturnErrorWhenUpdatingOldForumCountersErrored(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willReturn(true);
        $forum = $this->createMock(ForumRepositoryInterface::class);
        $forum->method('updateCounters')->willReturn(false);
        $thread->method('getParent')->willReturn($forum);
        $result = $this->service->move($thread, $forum);

        self::assertFalse($result->getResult());
        self::assertSame(
            'Error while updating old forum counters!',
            $result->getErrors()['exception']->getMessage()
        );
    }

    public function testMoveShouldReturnErrorWhenUpdatingNewForumCountersErrored(): void
    {
        $thread = $this->createMock(ThreadRepositoryInterface::class);
        $thread->method('move')->willReturn(true);
        $oldForum = $this->createMock(ForumRepositoryInterface::class);
        $oldForum->method('updateCounters')->willReturn(true);
        $thread->method('getParent')->willReturn($oldForum);
        $newForum = $this->createMock(ForumRepositoryInterface::class);
        $newForum->method('updateCounters')->willReturn(false);
        $result = $this->service->move($thread, $newForum);

        self::assertFalse($result->getResult());
        self::assertSame(
            'Error while updating new forum counters!',
            $result->getErrors()['exception']->getMessage()
        );
    }
}
