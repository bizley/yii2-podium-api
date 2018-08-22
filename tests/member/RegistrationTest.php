<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Registration;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class RegistrationTest
 * @package bizley\podium\tests\member
 */
class RegistrationTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testRegister(): void
    {
        Event::on(Registration::class, Registration::EVENT_BEFORE_REGISTERING, function () {
            $this->eventsRaised[Registration::EVENT_BEFORE_REGISTERING] = true;
        });
        Event::on(Registration::class, Registration::EVENT_AFTER_REGISTERING, function () {
            $this->eventsRaised[Registration::EVENT_AFTER_REGISTERING] = true;
        });

        $data = [
            'user_id' => '100',
            'username' => 'testname',
        ];
        $this->assertTrue($this->podium()->member->register($data)->result);

        $member = MemberRepo::findOne(['username' => 'testname']);
        $this->assertEquals(array_merge($data, [
            'status_id' => MemberStatus::REGISTERED,
            'slug' => 'testname',
        ]), [
            'user_id' => $member->user_id,
            'username' => $member->username,
            'status_id' => $member->status_id,
            'slug' => $member->slug,
        ]);

        $this->assertArrayHasKey(Registration::EVENT_BEFORE_REGISTERING, $this->eventsRaised);
        $this->assertArrayHasKey(Registration::EVENT_AFTER_REGISTERING, $this->eventsRaised);
    }

    public function testRegisterEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canRegister = false;
        };
        Event::on(Registration::class, Registration::EVENT_BEFORE_REGISTERING, $handler);

        $data = [
            'user_id' => '101',
            'username' => 'notestname',
        ];
        $this->assertFalse($this->podium()->member->register($data)->result);

        $this->assertEmpty( MemberRepo::findOne(['username' => 'notestname']));

        Event::off(Registration::class, Registration::EVENT_BEFORE_REGISTERING, $handler);
    }
}
