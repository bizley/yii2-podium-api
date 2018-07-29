<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Friendship;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberFriendshipTest
 * @package bizley\podium\tests\base
 */
class MemberFriendshipTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 100,
                'user_id' => '100',
                'username' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 101,
                'user_id' => '101',
                'username' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 102,
                'user_id' => '102',
                'username' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_acquaintance' => [
            [
                'id' => 100,
                'member_id' => 101,
                'target_id' => 102,
                'type_id' => AcquaintanceType::FRIEND,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected static $eventsRaised = [];

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Event::on(Friendship::class, Friendship::EVENT_BEFORE_BEFRIENDING, function () {
            static::$eventsRaised[Friendship::EVENT_BEFORE_BEFRIENDING] = true;
        });
        Event::on(Friendship::class, Friendship::EVENT_AFTER_BEFRIENDING, function () {
            static::$eventsRaised[Friendship::EVENT_AFTER_BEFRIENDING] = true;
        });
        Event::on(Friendship::class, Friendship::EVENT_BEFORE_UNFRIENDING, function () {
            static::$eventsRaised[Friendship::EVENT_BEFORE_UNFRIENDING] = true;
        });
        Event::on(Friendship::class, Friendship::EVENT_AFTER_UNFRIENDING, function () {
            static::$eventsRaised[Friendship::EVENT_AFTER_UNFRIENDING] = true;
        });
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
    }

    public function testBefriend(): void
    {
        $this->assertTrue($this->podium()->member->befriend(Member::findOne(100), Member::findOne(101)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::FRIEND,
        ]);
        $this->assertNotEmpty($acq);

        $this->assertArrayHasKey(Friendship::EVENT_BEFORE_BEFRIENDING, static::$eventsRaised);
        $this->assertArrayHasKey(Friendship::EVENT_AFTER_BEFRIENDING, static::$eventsRaised);
    }

    public function testBefriendEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canBeFriends = false;
        };
        Event::on(Friendship::class, Friendship::EVENT_BEFORE_BEFRIENDING, $handler);

        $this->assertFalse($this->podium()->member->befriend(Member::findOne(100), Member::findOne(101)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::FRIEND,
        ]);
        $this->assertEmpty($acq);

        Event::off(Friendship::class, Friendship::EVENT_BEFORE_BEFRIENDING, $handler);
    }

    public function testBefriendAgain(): void
    {
        $this->assertFalse($this->podium()->member->befriend(Member::findOne(101), Member::findOne(102)));
    }

    public function testUnfriend(): void
    {
        $this->assertTrue($this->podium()->member->unfriend(Member::findOne(101), Member::findOne(102)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::FRIEND,
        ]);
        $this->assertEmpty($acq);

        $this->assertArrayHasKey(Friendship::EVENT_BEFORE_UNFRIENDING, static::$eventsRaised);
        $this->assertArrayHasKey(Friendship::EVENT_AFTER_UNFRIENDING, static::$eventsRaised);
    }

    public function testUnfriendEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canUnfriend = false;
        };
        Event::on(Friendship::class, Friendship::EVENT_BEFORE_UNFRIENDING, $handler);

        $this->assertFalse($this->podium()->member->unfriend(Member::findOne(101), Member::findOne(102)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 101,
            'target_id' => 102,
            'type_id' => AcquaintanceType::FRIEND,
        ]);
        $this->assertNotEmpty($acq);

        Event::off(Friendship::class, Friendship::EVENT_BEFORE_UNFRIENDING, $handler);
    }

    public function testUnfriendAgain(): void
    {
        $this->assertFalse($this->podium()->member->unfriend(Member::findOne(100), Member::findOne(101)));
    }
}
