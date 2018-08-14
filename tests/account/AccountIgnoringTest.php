<?php

declare(strict_types=1);

namespace bizley\podium\tests\account;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Ignoring;
use bizley\podium\api\models\member\Member;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\tests\AccountTestCase;
use bizley\podium\tests\props\UserIdentity;
use yii\base\Event;

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
                'id' => 10,
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
    protected static $eventsRaised = [];

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
        \Yii::$app->user->setIdentity(new UserIdentity(['id' => '10']));
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
        parent::tearDown();
    }

    public function testIgnore(): void
    {
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_IGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_BEFORE_IGNORING] = true;
        });
        Event::on(Ignoring::class, Ignoring::EVENT_AFTER_IGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_AFTER_IGNORING] = true;
        });

        $this->assertTrue($this->podium()->account->ignore(Member::findOne(11)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 11,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertNotEmpty($acq);

        $this->assertArrayHasKey(Ignoring::EVENT_BEFORE_IGNORING, static::$eventsRaised);
        $this->assertArrayHasKey(Ignoring::EVENT_AFTER_IGNORING, static::$eventsRaised);
    }

    public function testIgnoreEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canIgnore = false;
        };
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_IGNORING, $handler);

        $this->assertFalse($this->podium()->account->ignore(Member::findOne(11)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 11,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertEmpty($acq);

        Event::off(Ignoring::class, Ignoring::EVENT_BEFORE_IGNORING, $handler);
    }

    public function testIgnoreAgain(): void
    {
        $this->assertFalse($this->podium()->account->ignore(Member::findOne(12)));
    }

    public function testUnignore(): void
    {
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_UNIGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_BEFORE_UNIGNORING] = true;
        });
        Event::on(Ignoring::class, Ignoring::EVENT_AFTER_UNIGNORING, function () {
            static::$eventsRaised[Ignoring::EVENT_AFTER_UNIGNORING] = true;
        });

        $this->assertTrue($this->podium()->account->unignore(Member::findOne(12)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 12,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertEmpty($acq);

        $this->assertArrayHasKey(Ignoring::EVENT_BEFORE_UNIGNORING, static::$eventsRaised);
        $this->assertArrayHasKey(Ignoring::EVENT_AFTER_UNIGNORING, static::$eventsRaised);
    }

    public function testUnignoreEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canUnignore = false;
        };
        Event::on(Ignoring::class, Ignoring::EVENT_BEFORE_UNIGNORING, $handler);

        $this->assertFalse($this->podium()->account->unignore(Member::findOne(12)));

        $acq = AcquaintanceRepo::findOne([
            'member_id' => 10,
            'target_id' => 12,
            'type_id' => AcquaintanceType::IGNORE,
        ]);
        $this->assertNotEmpty($acq);

        Event::off(Ignoring::class, Ignoring::EVENT_BEFORE_UNIGNORING, $handler);
    }

    public function testUnignoreAgain(): void
    {
        $this->assertFalse($this->podium()->account->unignore(Member::findOne(11)));
    }
}
