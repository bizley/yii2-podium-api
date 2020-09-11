<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\AcquaintanceType;
use PHPUnit\Framework\TestCase;

class AcquaintanceTypeTest extends TestCase
{
    public function testData(): void
    {
        self::assertEquals(
            [
                AcquaintanceType::FRIEND => 'acquaintance.type.friend',
                AcquaintanceType::IGNORE => 'acquaintance.type.ignore',
            ],
            AcquaintanceType::data()
        );
    }
}
