<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\MemberStatus;
use PHPUnit\Framework\TestCase;

class MemberStatusTest extends TestCase
{
    public function testData(): void
    {
        self::assertEquals(
            [
                MemberStatus::REGISTERED => 'member.status.registered',
                MemberStatus::ACTIVE => 'member.status.active',
                MemberStatus::BANNED => 'member.status.banned',
            ],
            MemberStatus::data()
        );
    }
}
