<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\Friendship;
use bizley\podium\api\models\Member;
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
                'username' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 101,
                'user_id' => '101',
                'username' => 'target',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_acquaintance' => [],
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
    public function testBefriend(): void
    {
        $this->fixturesUp();

        $this->assertTrue($this->podium()->member->befriend(Member::findOne(100), Member::findOne(101)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 100,
            'target_id' => 101,
            'type_id' => AcquaintanceType::FRIEND,
        ]);
        $this->assertNotEmpty($acq);

        $this->assertArrayHasKey(Friendship::EVENT_BEFORE_BEFRIENDING, static::$eventsRaised);
        $this->assertArrayHasKey(Friendship::EVENT_AFTER_BEFRIENDING, static::$eventsRaised);

        $this->fixturesDown();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function testBefriendEventPreventing(): void
    {
        $this->fixturesUp();

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

        $this->fixturesDown();
    }
}
