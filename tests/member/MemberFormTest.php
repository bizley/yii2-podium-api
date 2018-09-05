<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\MemberForm;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;

/**
 * Class MemberFormTest
 * @package bizley\podium\tests\member
 */
class MemberFormTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member',
                'slug' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    public function testUpdate(): void
    {
        Event::on(MemberForm::class, MemberForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[MemberForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(MemberForm::class, MemberForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[MemberForm::EVENT_AFTER_EDITING] = true;
        });

        $this->assertTrue($this->podium()->member->edit(MemberForm::findOne(1), ['username' => 'username-updated'])->result);

        $member = MemberRepo::findOne(['username' => 'username-updated']);
        $this->assertNotEmpty($member);
        $this->assertEquals('username-updated', $member->slug);
        $this->assertEmpty(MemberRepo::findOne(['username' => 'member']));

        $this->assertArrayHasKey(MemberForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    public function testUpdateEventPreventing(): void
    {
        $handler = function ($event) {
            $event->canEdit = false;
        };
        Event::on(MemberForm::class, MemberForm::EVENT_BEFORE_EDITING, $handler);

        $this->assertFalse($this->podium()->member->edit(MemberForm::findOne(1), ['username' => 'username-updated'])->result);

        $this->assertNotEmpty(MemberRepo::findOne(['username' => 'member']));
        $this->assertEmpty(MemberRepo::findOne(['username' => 'username-updated']));

        Event::off(MemberForm::class, MemberForm::EVENT_BEFORE_EDITING, $handler);
    }

    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->member->edit(MemberForm::findOne(1), [])->result);
    }
}
