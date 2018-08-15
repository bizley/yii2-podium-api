<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\BaseEnum;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\tests\TestCase;

/**
 * Class MemberStatusTest
 * @package bizley\podium\tests\enums
 */
class MemberStatusTest extends TestCase
{
    /**
     * @covers BaseEnum::data
     */
    public function testData(): void
    {
        $this->assertEquals([
            MemberStatus::REGISTERED => 'member.status.registered',
            MemberStatus::ACTIVE => 'member.status.active',
            MemberStatus::BANNED => 'member.status.banned',
        ], MemberStatus::data());
    }
}
