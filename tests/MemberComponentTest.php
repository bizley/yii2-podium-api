<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\components\Member;
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
    protected function component()
    {
        return $this->podium()->member;
    }

    public function testRegister()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_REGISTER, function () {
            $this->eventsRaised[Member::EVENT_BEFORE_REGISTER] = true;
        });
        Event::on(Member::class, Member::EVENT_AFTER_REGISTER, function () {
            $this->eventsRaised[Member::EVENT_AFTER_REGISTER] = true;
        });
        $this->assertTrue($this->component()->register(['username' => 'test']));
        $this->assertTrue($this->eventsRaised[Member::EVENT_BEFORE_REGISTER]);
        $this->assertTrue($this->eventsRaised[Member::EVENT_AFTER_REGISTER]);
    }

    public function testRegisterBeforeInvalid()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_REGISTER, function ($event) {
            $event->isValid = false;
        });
        $this->assertFalse($this->component()->register(['username' => 'test2']));
    }

    public function testDelete()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_DELETE, function () {
            $this->eventsRaised[Member::EVENT_BEFORE_DELETE] = true;
        });
        Event::on(Member::class, Member::EVENT_AFTER_DELETE, function () {
            $this->eventsRaised[Member::EVENT_AFTER_DELETE] = true;
        });
        $this->component()->register(['username' => 'test3']);
        $this->assertEquals(1, $this->component()->delete(['username' => 'test3']));
        $this->assertTrue($this->eventsRaised[Member::EVENT_BEFORE_DELETE]);
        $this->assertTrue($this->eventsRaised[Member::EVENT_AFTER_DELETE]);
    }

    public function testDeleteBeforeInvalid()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_DELETE, function ($event) {
            $event->isValid = false;
        });
        $this->component()->register(['username' => 'test4']);
        $this->assertFalse($this->component()->delete(['username' => 'test4']));
    }

    public function testIgnore()
    {
        Event::on(Member::class, Member::EVENT_BEFORE_IGNORE, function () {
            $this->eventsRaised[Member::EVENT_BEFORE_IGNORE] = true;
        });
        Event::on(Member::class, Member::EVENT_AFTER_IGNORE, function () {
            $this->eventsRaised[Member::EVENT_AFTER_IGNORE] = true;
        });
        // todo
        $this->assertTrue($this->eventsRaised[Member::EVENT_BEFORE_IGNORE]);
        $this->assertTrue($this->eventsRaised[Member::EVENT_AFTER_IGNORE]);
    }
}
