<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\Registration;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class RegistrationTest
 * @package bizley\podium\tests\base
 */
class RegistrationTest extends DbTestCase
{
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
        $this->assertTrue($this->podium()->member->register($data));

        $member = MemberRepo::findOne(['username' => 'testname']);
        $this->assertEquals(array_merge($data, [
            'status_id' => MemberStatus::REGISTERED,
        ]), [
            'user_id' => $member->user_id,
            'username' => $member->username,
            'status_id' => $member->status_id,
        ]);

        $this->assertArrayHasKey(Registration::EVENT_BEFORE_REGISTERING, $this->eventsRaised);
        $this->assertArrayHasKey(Registration::EVENT_AFTER_REGISTERING, $this->eventsRaised);
    }
}
