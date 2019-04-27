<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\base\NoMembershipException;
use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\MemberIgnorer;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\Event;
use yii\db\Exception;

/**
 * Class AccountIgnoringTest
 * @package bizley\podium\tests\account
 */
class AccountIgnoringTest extends AccountTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 10,
                'user_id' => '10',
                'username' => 'member1',
                'slug' => 'member1',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 11,
                'user_id' => '11',
                'username' => 'member2',
                'slug' => 'member2',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
            [
                'id' => 12,
                'user_id' => '12',
                'username' => 'member3',
                'slug' => 'member3',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
        'podium_acquaintance' => [
            [
                'member_id' => 10,
                'target_id' => 12,
                'type_id' => AcquaintanceType::IGNORE,
                'created_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
        Yii::$app->user->setIdentity(new UserIdentity(['id' => '10']));
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    /**
     * @throws NoMembershipException
     */
    public function testIgnore(): void
    {
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_IGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_BEFORE_IGNORING] = true;
        });
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_AFTER_IGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_AFTER_IGNORING] = true;
        });

        $this->assertTrue($this->podium()->account->ignoreMember(Member::findOne(11))->result);

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 11,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertNotEmpty($acq);

        $this->assertArrayHasKey(MemberIgnorer::EVENT_BEFORE_IGNORING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberIgnorer::EVENT_AFTER_IGNORING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testIgnoreEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canIgnore = false;
        };
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_IGNORING, $handler);

        $this->assertFalse($this->podium()->account->ignoreMember(Member::findOne(11))->result);

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 11,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertEmpty($acq);

        Event::off(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_IGNORING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testIgnoreAgain(): void
    {
        $this->assertFalse($this->podium()->account->ignoreMember(Member::findOne(12))->result);
    }

    /**
     * @throws NoMembershipException
     */
    public function testUnignore(): void
    {
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_UNIGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_BEFORE_UNIGNORING] = true;
        });
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_AFTER_UNIGNORING, function () {
            $this->eventsRaised[MemberIgnorer::EVENT_AFTER_UNIGNORING] = true;
        });

        $this->assertTrue($this->podium()->account->unignoreMember(Member::findOne(12))->result);

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 12,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertEmpty($acq);

        $this->assertArrayHasKey(MemberIgnorer::EVENT_BEFORE_UNIGNORING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberIgnorer::EVENT_AFTER_UNIGNORING, $this->eventsRaised);
    }

    /**
     * @throws NoMembershipException
     */
    public function testUnignoreEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canUnignore = false;
        };
        Event::on(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_UNIGNORING, $handler);

        $this->assertFalse($this->podium()->account->unignoreMember(Member::findOne(12))->result);

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 12,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertNotEmpty($acq);

        Event::off(MemberIgnorer::class, MemberIgnorer::EVENT_BEFORE_UNIGNORING, $handler);
    }

    /**
     * @throws NoMembershipException
     */
    public function testUnignoreAgain(): void
    {
        $this->assertFalse($this->podium()->account->unignoreMember(Member::findOne(11))->result);
    }
}
