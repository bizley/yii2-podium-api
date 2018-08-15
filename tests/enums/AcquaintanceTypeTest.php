<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\AcquaintanceType;
use bizley\podium\api\enums\BaseEnum;
use bizley\podium\tests\TestCase;

/**
 * Class AcquaintanceTypeTest
 * @package bizley\podium\tests\enums
 */
class AcquaintanceTypeTest extends TestCase
{
    /**
     * @covers BaseEnum::data
     */
    public function testData(): void
    {
        $this->assertEquals([
            AcquaintanceType::FRIEND => 'acquaintance.type.friend',
            AcquaintanceType::IGNORE => 'acquaintance.type.ignore',
        ], AcquaintanceType::data());
    }
}
