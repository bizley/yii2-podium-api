<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\components\Member;
use bizley\podium\api\dictionaries\Permission;
use yii\base\Event;

class MemberComponentTest extends TestCase
{
    public $eventsRaised = [];

    protected function setUp()
    {
        Event::offAll();
    }

    /**
     * @return \bizley\podium\api\components\Member
     */
    protected function memberComponent()
    {
        return $this->podium()->member;
    }

    /**
     * @return \bizley\podium\api\components\Access
     */
    protected function accessComponent()
    {
        return $this->podium()->access;
    }

    public function testRegister()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_REGISTER, function () {
            $this->eventsRaised[Member::EVENT_BEFORE_REGISTER] = true;
        });
        Event::on(Member::class, Member::EVENT_AFTER_REGISTER, function () {
            $this->eventsRaised[Member::EVENT_AFTER_REGISTER] = true;
        });
        $this->assertTrue($this->memberComponent()->register(['username' => 'test']));
        $this->assertTrue($this->eventsRaised[Member::EVENT_BEFORE_REGISTER]);
        $this->assertTrue($this->eventsRaised[Member::EVENT_AFTER_REGISTER]);
    }

    public function testRegisterBeforeInvalid()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_REGISTER, function ($event) {
            $event->isValid = false;
        });
        $this->assertFalse($this->memberComponent()->register(['username' => 'test2']));
    }

    public function testDelete()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_DELETE, function () {
            $this->eventsRaised[Member::EVENT_BEFORE_DELETE] = true;
        });
        Event::on(Member::class, Member::EVENT_AFTER_DELETE, function () {
            $this->eventsRaised[Member::EVENT_AFTER_DELETE] = true;
        });
        $this->memberComponent()->register(['username' => 'test3']);
        $this->assertEquals(1, $this->memberComponent()->delete(['username' => 'test3']));
        $this->assertTrue($this->eventsRaised[Member::EVENT_BEFORE_DELETE]);
        $this->assertTrue($this->eventsRaised[Member::EVENT_AFTER_DELETE]);
    }

    public function testDeleteBeforeInvalid()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_DELETE, function ($event) {
            $event->isValid = false;
        });
        $this->memberComponent()->register(['username' => 'test4']);
        $this->assertFalse($this->memberComponent()->delete(['username' => 'test4']));
    }

    public function testIgnore()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_IGNORE, function () {
            $this->eventsRaised[Member::EVENT_BEFORE_IGNORE] = true;
        });
        Event::on(Member::class, Member::EVENT_AFTER_IGNORE, function () {
            $this->eventsRaised[Member::EVENT_AFTER_IGNORE] = true;
        });
        $this->memberComponent()->register(['username' => 'test5']);
        $memberId = $this->memberComponent()->memberRepo->id;
        $this->accessComponent()->grant($memberId, Permission::MEMBER_ACQUAINTANCE);
        $this->memberComponent()->register(['username' => 'test6']);
        $targetId = $this->memberComponent()->memberRepo->id;
        $this->memberComponent()->ignore($memberId, $targetId);

        $this->assertTrue($this->eventsRaised[Member::EVENT_BEFORE_IGNORE]);
        $this->assertTrue($this->eventsRaised[Member::EVENT_AFTER_IGNORE]);
        $this->assertTrue($this->memberComponent()->isIgnoring($memberId, $targetId));
    }

    public function testIgnoreBeforeInvalid()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_IGNORE, function ($event) {
            $event->isValid = false;
        });
        $this->memberComponent()->register(['username' => 'test7']);
        $memberId = $this->memberComponent()->memberRepo->id;
        $this->accessComponent()->grant($memberId, Permission::MEMBER_ACQUAINTANCE);
        $this->memberComponent()->register(['username' => 'test8']);
        $targetId = $this->memberComponent()->memberRepo->id;
        $this->assertFalse($this->memberComponent()->ignore($memberId, $targetId));
    }

    // todo broken
    public function testUnignore()
    {
        $this->memberComponent()->register(['username' => 'test9']);
        $memberId = $this->memberComponent()->memberRepo->id;
        $this->accessComponent()->grant($memberId, Permission::MEMBER_ACQUAINTANCE);
        $this->memberComponent()->register(['username' => 'test10']);
        $targetId = $this->memberComponent()->memberRepo->id;
        $this->memberComponent()->ignore($memberId, $targetId);
        $this->assertTrue($this->memberComponent()->isIgnoring($memberId, $targetId));
        $this->memberComponent()->ignore($memberId, $targetId);
        $this->assertFalse($this->memberComponent()->isIgnoring($memberId, $targetId));
    }
}
