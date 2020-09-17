<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\thread;

use bizley\podium\api\interfaces\MemberRepositoryInterface;
use bizley\podium\api\interfaces\SubscriptionRepositoryInterface;
use bizley\podium\api\interfaces\ThreadRepositoryInterface;
use bizley\podium\api\services\thread\ThreadSubscriber;
use Exception;
use PHPUnit\Framework\TestCase;

class ThreadSubscriberTest extends TestCase
{
    private ThreadSubscriber $service;

    protected function setUp(): void
    {
        $this->service = new ThreadSubscriber();
    }

    public function testBeforeSubscribeShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeSubscribe());
    }

    public function testSubscribeShouldReturnErrorWhenSubscribingErrored(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('getErrors')->willReturn([1]);
        $subscription->method('isMemberSubscribed')->willReturn(false);
        $subscription->method('subscribe')->willReturn(false);
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->subscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testSubscribeShouldReturnSuccessWhenSubscribingIsDone(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('isMemberSubscribed')->willReturn(false);
        $subscription->method('subscribe')->willReturn(true);
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->subscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertTrue($result->getResult());
    }

    public function testSubscribeShouldReturnErrorWhenSubscribingThrowsException(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('isMemberSubscribed')->willReturn(false);
        $subscription->method('subscribe')->willThrowException(new Exception('exc'));
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->subscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testSubscribeShouldReturnErrorWhenMemberIsAlreadySubscribed(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('isMemberSubscribed')->willReturn(true);
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->subscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertSame('thread.already.subscribed', $result->getErrors()['api']);
    }

    public function testBeforeUnsubscribeShouldReturnTrue(): void
    {
        self::assertTrue($this->service->beforeUnsubscribe());
    }

    public function testUnsubscribeShouldReturnErrorWhenUnsubscribingErrored(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('getErrors')->willReturn([1]);
        $subscription->method('fetchOne')->willReturn(true);
        $subscription->method('delete')->willReturn(false);
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->unsubscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertSame([1], $result->getErrors());
    }

    public function testUnsubscribeShouldReturnSuccessWhenUnsubscribingIsDone(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('fetchOne')->willReturn(true);
        $subscription->method('delete')->willReturn(true);
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->unsubscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertTrue($result->getResult());
    }

    public function testUnsubscribeShouldReturnErrorWhenUnsubscribingThrowsException(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('fetchOne')->willReturn(true);
        $subscription->method('delete')->willThrowException(new Exception('exc'));
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->unsubscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertSame('exc', $result->getErrors()['exception']->getMessage());
    }

    public function testUnsubscribeShouldReturnErrorWhenSubscriptionDoesntExist(): void
    {
        $subscription = $this->createMock(SubscriptionRepositoryInterface::class);
        $subscription->method('fetchOne')->willReturn(false);
        $this->service->repositoryConfig = $subscription;
        $result = $this->service->unsubscribe(
            $this->createMock(ThreadRepositoryInterface::class),
            $this->createMock(MemberRepositoryInterface::class)
        );

        self::assertFalse($result->getResult());
        self::assertSame('thread.not.subscribed', $result->getErrors()['api']);
    }
}
