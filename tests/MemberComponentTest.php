<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\components\Member;
use yii\base\Event;

class MemberComponentTest extends TestCase
{
    public $eventsRaised = [];

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

    // broken
    public function testDelete()
    {
        $this->component()->register(['username' => 'test3']);
        var_dump($this->component()->memberRepo->errors);
        $this->assertEquals(1, $this->component()->delete(['username' => 'test3']));
    }
}
