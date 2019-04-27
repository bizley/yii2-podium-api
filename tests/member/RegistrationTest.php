<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\Registration;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use function array_merge;
use function time;
use yii\helpers\ArrayHelper;

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

        $response = $this->podium()->member->register($data);
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;
        $createdAt = ArrayHelper::remove($responseData, 'created_at');
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        $this->assertLessThanOrEqual($time, $createdAt);
        $this->assertLessThanOrEqual($time, $updatedAt);

        $this->assertEquals([
            'id' => 1,
            'user_id' => '100',
            'username' => 'testname',
            'slug' => 'testname',
            'status_id' => MemberStatus::REGISTERED,
        ], $responseData);

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

    public function testRegisterWithSlug(): void
    {
        $data = [
            'user_id' => '110',
            'username' => 'member-with-slug',
            'slug' => 'mem-slug',
        ];
        $this->assertTrue($this->podium()->member->register($data)->result);

        $member = MemberRepo::findOne(['username' => 'member-with-slug']);
        $this->assertEquals($data, [
            'user_id' => $member->user_id,
            'username' => $member->username,
            'slug' => $member->slug,
        ]);
    }

    public function testRegisterEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canRegister = false;
        };
        Event::on(Registration::class, Registration::EVENT_BEFORE_REGISTERING, $handler);

        $data = [
            'user_id' => '101',
            'username' => 'notestname',
        ];
        $this->assertFalse($this->podium()->member->register($data)->result);

        $this->assertEmpty(MemberRepo::findOne(['username' => 'notestname']));

        Event::off(Registration::class, Registration::EVENT_BEFORE_REGISTERING, $handler);
    }

    public function testRegisterLoadFalse(): void
    {
        $this->assertFalse($this->podium()->member->register([])->result);
    }

    public function testFailedRegister(): void
    {
        $this->assertFalse((new Registration())->register()->result);
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        $this->assertEquals([
            'user_id' => 'registration.user.id',
            'username' => 'registration.username',
            'slug' => 'registration.slug',
        ], (new Registration())->attributeLabels());
    }
}
