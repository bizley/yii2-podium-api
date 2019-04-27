<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\thread\ThreadSubscriber;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\SubscriptionRepo;
use bizley\podium\tests\DbTestCase;
use Exception;
use yii\base\Event;

/**
 * Class ThreadSubscriberTest
 * @package bizley\podium\tests\thread
 */
class ThreadSubscriberTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member',
                'slug' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_category' => [
            [
                'id' => 1,
                'author_id' => 1,
                'name' => 'category1',
                'slug' => 'category1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_forum' => [
            [
                'id' => 1,
                'category_id' => 1,
                'author_id' => 1,
                'name' => 'forum1',
                'slug' => 'forum1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_thread' => [
            [
                'id' => 1,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread1',
                'slug' => 'thread1',
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 2,
                'category_id' => 1,
                'forum_id' => 1,
                'author_id' => 1,
                'name' => 'thread2',
                'slug' => 'thread2',
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_subscription' => [
            [
                'member_id' => 1,
                'thread_id' => 2,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testSubscribe(): void
    {
        Event::on(ThreadSubscriber::class, ThreadSubscriber::EVENT_BEFORE_SUBSCRIBING, function () {
            $this->eventsRaised[ThreadSubscriber::EVENT_BEFORE_SUBSCRIBING] = true;
        });
        Event::on(ThreadSubscriber::class, ThreadSubscriber::EVENT_AFTER_SUBSCRIBING, function () {
            $this->eventsRaised[ThreadSubscriber::EVENT_AFTER_SUBSCRIBING] = true;
        });

        $this->assertTrue($this->podium()->thread->subscribe(Member::findOne(1), Thread::findOne(1))->result);

        $subscription = SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]);
        $this->assertNotEmpty($subscription);
        $this->assertEquals(true, $subscription->seen);

        $this->assertArrayHasKey(ThreadSubscriber::EVENT_BEFORE_SUBSCRIBING, $this->eventsRaised);
        $this->assertArrayHasKey(ThreadSubscriber::EVENT_AFTER_SUBSCRIBING, $this->eventsRaised);
    }

    public function testSubscribeEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canSubscribe = false;
        };
        Event::on(ThreadSubscriber::class, ThreadSubscriber::EVENT_BEFORE_SUBSCRIBING, $handler);

        $this->assertFalse($this->podium()->thread->subscribe(Member::findOne(1), Thread::findOne(1))->result);

        $this->assertEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]));

        Event::off(ThreadSubscriber::class, ThreadSubscriber::EVENT_BEFORE_SUBSCRIBING, $handler);
    }

    public function testSubscribeAgain(): void
    {
        $this->assertFalse($this->podium()->thread->subscribe(Member::findOne(1), Thread::findOne(2))->result);
    }

    public function testFailedSubscribe(): void
    {
        $mock = $this->getMockBuilder(ThreadSubscriber::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->subscribe()->result);
    }

    public function testUnsubscribe(): void
    {
        Event::on(ThreadSubscriber::class, ThreadSubscriber::EVENT_BEFORE_UNSUBSCRIBING, function () {
            $this->eventsRaised[ThreadSubscriber::EVENT_BEFORE_UNSUBSCRIBING] = true;
        });
        Event::on(ThreadSubscriber::class, ThreadSubscriber::EVENT_AFTER_UNSUBSCRIBING, function () {
            $this->eventsRaised[ThreadSubscriber::EVENT_AFTER_UNSUBSCRIBING] = true;
        });

        $this->assertTrue($this->podium()->thread->unsubscribe(Member::findOne(1), Thread::findOne(2))->result);

        $this->assertEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 2,
        ]));

        $this->assertArrayHasKey(ThreadSubscriber::EVENT_BEFORE_UNSUBSCRIBING, $this->eventsRaised);
        $this->assertArrayHasKey(ThreadSubscriber::EVENT_AFTER_UNSUBSCRIBING, $this->eventsRaised);
    }

    public function testUnsubscribeEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnsubscribe = false;
        };
        Event::on(ThreadSubscriber::class, ThreadSubscriber::EVENT_BEFORE_UNSUBSCRIBING, $handler);

        $this->assertFalse($this->podium()->thread->unsubscribe(Member::findOne(1), Thread::findOne(2))->result);

        $this->assertNotEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 2,
        ]));

        Event::off(ThreadSubscriber::class, ThreadSubscriber::EVENT_BEFORE_UNSUBSCRIBING, $handler);
    }

    public function testUnsubscribeAgain(): void
    {
        $this->assertFalse($this->podium()->thread->unsubscribe(Member::findOne(1), Thread::findOne(1))->result);
    }

    public function testExceptionRemove(): void
    {
        $mock = $this->getMockBuilder(ThreadSubscriber::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->will($this->throwException(new Exception()));

        $this->assertFalse($mock->unsubscribe()->result);
    }

    public function testFailedRemove(): void
    {
        $mock = $this->getMockBuilder(ThreadSubscriber::class)->setMethods(['delete'])->getMock();
        $mock->method('delete')->willReturn(false);

        $this->assertFalse($mock->unsubscribe()->result);
    }
}
