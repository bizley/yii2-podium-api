<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\MemberBanisher;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberBanisherTest
 * @package bizley\podium\tests\member
 */
class MemberBanisherTest extends DbTestCase
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
    protected $eventsRaised = [];

    /**
     * @throws ModelNotFoundException
     */
    public function testBan(): void
    {
        Event::on(MemberBanisher::class, MemberBanisher::EVENT_BEFORE_BANNING, function () {
            $this->eventsRaised[MemberBanisher::EVENT_BEFORE_BANNING] = true;
        });
        Event::on(MemberBanisher::class, MemberBanisher::EVENT_AFTER_BANNING, function () {
            $this->eventsRaised[MemberBanisher::EVENT_AFTER_BANNING] = true;
        });

        $this->assertTrue($this->podium()->member->ban(100)->result);

        $banned = MemberRepo::findOne(100);
        $this->assertEquals(MemberStatus::BANNED, $banned->status_id);

        $this->assertArrayHasKey(MemberBanisher::EVENT_BEFORE_BANNING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberBanisher::EVENT_AFTER_BANNING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testBanEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canBan = false;
        };
        Event::on(MemberBanisher::class, MemberBanisher::EVENT_BEFORE_BANNING, $handler);

        $this->assertFalse($this->podium()->member->ban(100)->result);

        $notbanned = MemberRepo::findOne(100);
        $this->assertEquals(MemberStatus::ACTIVE, $notbanned->status_id);

        Event::off(MemberBanisher::class, MemberBanisher::EVENT_BEFORE_BANNING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testBanAgain(): void
    {
        $this->assertFalse($this->podium()->member->ban(101)->result);
    }

    public function testFailedBan(): void
    {
        $mock = $this->getMockBuilder(MemberBanisher::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $mock->status_id = MemberStatus::ACTIVE;

        $this->assertFalse($mock->ban()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoMemberToBan(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->member->ban(999);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testUnban(): void
    {
        Event::on(MemberBanisher::class, MemberBanisher::EVENT_BEFORE_UNBANNING, function () {
            $this->eventsRaised[MemberBanisher::EVENT_BEFORE_UNBANNING] = true;
        });
        Event::on(MemberBanisher::class, MemberBanisher::EVENT_AFTER_UNBANNING, function () {
            $this->eventsRaised[MemberBanisher::EVENT_AFTER_UNBANNING] = true;
        });

        $this->assertTrue($this->podium()->member->unban(101)->result);

        $unbanned = MemberRepo::findOne(101);
        $this->assertEquals(MemberStatus::ACTIVE, $unbanned->status_id);

        $this->assertArrayHasKey(MemberBanisher::EVENT_BEFORE_UNBANNING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberBanisher::EVENT_AFTER_UNBANNING, $this->eventsRaised);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testUnbanEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnban = false;
        };
        Event::on(MemberBanisher::class, MemberBanisher::EVENT_BEFORE_UNBANNING, $handler);

        $this->assertFalse($this->podium()->member->unban(101)->result);

        $notunbanned = MemberRepo::findOne(101);
        $this->assertEquals(MemberStatus::BANNED, $notunbanned->status_id);

        Event::off(MemberBanisher::class, MemberBanisher::EVENT_BEFORE_UNBANNING, $handler);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testUnbanAgain(): void
    {
        $this->assertFalse($this->podium()->member->unban(100)->result);
    }

    public function testFailedUnban(): void
    {
        $mock = $this->getMockBuilder(MemberBanisher::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $mock->status_id = MemberStatus::BANNED;

        $this->assertFalse($mock->unban()->result);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function testNoMemberToUnban(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->member->unban(999);
    }
}
