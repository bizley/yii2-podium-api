<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\thread\Subscribing;
use bizley\podium\api\models\thread\Thread;
use bizley\podium\api\repos\SubscriptionRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use yii\base\Event;

/**
 * Class AccountSubscribingTest
 * @package bizley\podium\tests\account
 */
class AccountSubscribingTest extends AccountTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member1',
                'slug' => 'member1',
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

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
        \Yii::$app->user->setIdentity(new UserIdentity(['id' => '1']));
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    public function testSubscribe(): void
    {
        Event::on(Subscribing::class, Subscribing::EVENT_BEFORE_SUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_BEFORE_SUBSCRIBING] = true;
        });
        Event::on(Subscribing::class, Subscribing::EVENT_AFTER_SUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_AFTER_SUBSCRIBING] = true;
        });

        $this->assertTrue($this->podium()->account->subscribe(Thread::findOne(1))->result);

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

        $this->assertFalse($this->podium()->account->subscribe(Thread::findOne(1))->result);

        $this->assertEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 1,
        ]));

        Event::off(Subscribing::class, Subscribing::EVENT_BEFORE_SUBSCRIBING, $handler);
    }

    public function testSubscribeAgain(): void
    {
        $this->assertFalse($this->podium()->account->subscribe(Thread::findOne(2))->result);
    }

    public function testUnsubscribe(): void
    {
        Event::on(Subscribing::class, Subscribing::EVENT_BEFORE_UNSUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_BEFORE_UNSUBSCRIBING] = true;
        });
        Event::on(Subscribing::class, Subscribing::EVENT_AFTER_UNSUBSCRIBING, function () {
            static::$eventsRaised[Subscribing::EVENT_AFTER_UNSUBSCRIBING] = true;
        });

        $this->assertTrue($this->podium()->account->unsubscribe(Thread::findOne(2))->result);

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

        $this->assertFalse($this->podium()->account->unsubscribe(Thread::findOne(2))->result);

        $this->assertNotEmpty(SubscriptionRepo::findOne([
            'member_id' => 1,
            'thread_id' => 2,
        ]));

        Event::off(Subscribing::class, Subscribing::EVENT_BEFORE_UNSUBSCRIBING, $handler);
    }

    public function testUnsubscribeAgain(): void
    {
        $this->assertFalse($this->podium()->account->unsubscribe(Thread::findOne(1))->result);
    }
}
