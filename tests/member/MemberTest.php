<?php

namespace bizley\podium\api\tests\member;

use bizley\podium\api\components\Member;
use bizley\podium\api\tests\TestCase;
use yii\db\Query;

class MemberTest extends TestCase
{
    /**
     * @return Member
     */
    protected function api()
    {
        return $this->podium()->member;
    }

    protected function tableName()
    {
        return \bizley\podium\api\repositories\Member::tableName();
    }

    public function testAddingMemberErroneous()
    {
        $this->assertFalse($this->api()->add(['nonexisting' => 'test']));
        $this->assertNotEmpty($this->api()->errors);
    }

    public function testAddingMemberSuccessful()
    {
        $this->assertTrue($this->api()->add(['username' => 'test']));
        $this->assertEmpty($this->api()->errors);

        $member = (new Query())->select(['username', 'slug', 'status'])->from($this->tableName())->where(['username' => 'test'])->one(static::$db);
        $this->assertEquals([
            'username' => 'test',
            'slug' => 'test',
            'status' => 0,
        ], $member);
    }

    public function testAddingMemberDuplicate()
    {
        $this->assertTrue($this->api()->add(['username' => 'test']));
        $this->assertFalse($this->api()->add(['username' => 'test']));
        $this->assertNotEmpty($this->api()->errors);
    }
}