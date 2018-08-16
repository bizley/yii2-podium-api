<?php

declare(strict_types=1);

namespace bizley\podium\tests\thread;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\models\thread\Subscribing;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\SubscriptionRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class ThreadSubscribingTest
 * @package bizley\podium\tests\thread
 */
class ThreadSubscribingTest extends DbTestCase
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
    protected static $eventsRaised = [];

    public function testSubscribe(): void
    {
        Event::on(Subscribing::class, Subscribing::EVENT_BEFORE_SUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_BEFORE_SUBSCRIBING] = true;
        });
        Event::on(Subscribing::class, Subscribing::EVENT_AFTER_SUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_AFTER_SUBSCRIBING] = true;
        });

        $this->assertTrue($this->podium()->thread->subscribe(Member::findOne(1), Thread::findOne(1)));

        $subscription = SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]);
        $this->assertNotEmpty($subscription);
        $this->assertEquals(true, $subscription->seen);

        $this->assertArrayHasKey(Subscribing::EVENT_BEFORE_SUBSCRIBING, static::$eventsRaised);
        $this->assertArrayHasKey(Subscribing::EVENT_AFTER_SUBSCRIBING, static::$eventsRaised);
    }

    public function testSubscribeEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canSubscribe = false;
        };
        Event::on(Subscribing::class, Subscribing::EVENT_BEFORE_SUBSCRIBING, $handler);

        $this->assertFalse($this->podium()->thread->subscribe(Member::findOne(1), Thread::findOne(1)));

        $this->assertEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]));

        Event::off(Subscribing::class, Subscribing::EVENT_BEFORE_SUBSCRIBING, $handler);
    }

    public function testSubscribeAgain(): void
    {
        $this->assertFalse($this->podium()->thread->subscribe(Member::findOne(1), Thread::findOne(2)));
    }

    public function testUnsubscribe(): void
    {
        Event::on(Subscribing::class, Subscribing::EVENT_BEFORE_UNSUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_BEFORE_UNSUBSCRIBING] = true;
        });
        Event::on(Subscribing::class, Subscribing::EVENT_AFTER_UNSUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_AFTER_UNSUBSCRIBING] = true;
        });

        $this->assertTrue($this->podium()->thread->unsubscribe(Member::findOne(1), Thread::findOne(2)));

        $this->assertEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 2,
        ]));

        $this->assertArrayHasKey(Subscribing::EVENT_BEFORE_UNSUBSCRIBING, static::$eventsRaised);
        $this->assertArrayHasKey(Subscribing::EVENT_AFTER_UNSUBSCRIBING, static::$eventsRaised);
    }

    public function testUnsubscribeEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canUnsubscribe = false;
        };
        Event::on(Subscribing::class, Subscribing::EVENT_BEFORE_UNSUBSCRIBING, $handler);

        $this->assertFalse($this->podium()->thread->unsubscribe(Member::findOne(1), Thread::findOne(2)));

        $this->assertNotEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 2,
        ]));

        Event::off(Subscribing::class, Subscribing::EVENT_BEFORE_UNSUBSCRIBING, $handler);
    }

    public function testUnsubscribeAgain(): void
    {
        $this->assertFalse($this->podium()->thread->unsubscribe(Member::findOne(1), Thread::findOne(1)));
    }
}
