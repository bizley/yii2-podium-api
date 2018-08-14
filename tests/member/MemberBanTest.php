<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\MemberBan;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberBanTest
 * @package bizley\podium\tests\member
 */
class MemberBanTest extends DbTestCase
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
                'slug' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 101,
                'user_id' => '101',
                'username' => 'member2',
                'slug' => 'member2',
                'status_id' => MemberStatus::BANNED,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected static $eventsRaised = [];

    public function testBan(): void
    {
        Event::on(MemberBan::class, MemberBan::EVENT_BEFORE_BANNING, function () {
            static::$eventsRaised[MemberBan::EVENT_BEFORE_BANNING] = true;
        });
        Event::on(MemberBan::class, MemberBan::EVENT_AFTER_BANNING, function () {
            static::$eventsRaised[MemberBan::EVENT_AFTER_BANNING] = true;
        });

        $this->assertTrue($this->podium()->member->ban(MemberBan::findOne(100)));

        $banned = MemberRepo::findOne(100);
        $this->assertEquals(MemberStatus::BANNED, $banned->status_id);

        $this->assertArrayHasKey(MemberBan::EVENT_BEFORE_BANNING, static::$eventsRaised);
        $this->assertArrayHasKey(MemberBan::EVENT_AFTER_BANNING, static::$eventsRaised);
    }

    public function testBanEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canBan = false;
        };
        Event::on(MemberBan::class, MemberBan::EVENT_BEFORE_BANNING, $handler);

        $this->assertFalse($this->podium()->member->ban(MemberBan::findOne(100)));

        $notbanned = MemberRepo::findOne(100);
        $this->assertEquals(MemberStatus::ACTIVE, $notbanned->status_id);

        Event::off(MemberBan::class, MemberBan::EVENT_BEFORE_BANNING, $handler);
    }

    public function testBanAgain(): void
    {
        $this->assertFalse($this->podium()->member->ban(MemberBan::findOne(101)));
    }

    public function testUnban(): void
    {
        Event::on(MemberBan::class, MemberBan::EVENT_BEFORE_UNBANNING, function () {
            static::$eventsRaised[MemberBan::EVENT_BEFORE_UNBANNING] = true;
        });
        Event::on(MemberBan::class, MemberBan::EVENT_AFTER_UNBANNING, function () {
            static::$eventsRaised[MemberBan::EVENT_AFTER_UNBANNING] = true;
        });

        $this->assertTrue($this->podium()->member->unban(MemberBan::findOne(101)));

        $unbanned = MemberRepo::findOne(101);
        $this->assertEquals(MemberStatus::ACTIVE, $unbanned->status_id);

        $this->assertArrayHasKey(MemberBan::EVENT_BEFORE_UNBANNING, static::$eventsRaised);
        $this->assertArrayHasKey(MemberBan::EVENT_AFTER_UNBANNING, static::$eventsRaised);
    }

    public function testUnbanEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canUnban = false;
        };
        Event::on(MemberBan::class, MemberBan::EVENT_BEFORE_UNBANNING, $handler);

        $this->assertFalse($this->podium()->member->unban(MemberBan::findOne(101)));

        $notunbanned = MemberRepo::findOne(101);
        $this->assertEquals(MemberStatus::BANNED, $notunbanned->status_id);

        Event::off(MemberBan::class, MemberBan::EVENT_BEFORE_UNBANNING, $handler);
    }

    public function testUnbanAgain(): void
    {
        $this->assertFalse($this->podium()->member->unban(MemberBan::findOne(100)));
    }
}
