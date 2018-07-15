<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;

/**
 * Class RegistrationTest
 * @package bizley\podium\tests\base
 */
class RegistrationTest extends DbTestCase
{
    public function testRegister(): void
    {
        $data = [
            'user_id' => 100,
            'username' => 'testname',
        ];
        $this->assertTrue($this->podium()->member->register($data));

        $member = MemberRepo::findOne(['username' => 'testname']);
        $this->assertEquals($data, [
            'user_id' => $member->user_id,
            'username' => $member->username,
        ]);
    }
}
