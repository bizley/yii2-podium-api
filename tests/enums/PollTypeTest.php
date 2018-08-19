<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\PollType;
use bizley\podium\tests\TestCase;

/**
 * Class PollTypeTest
 * @package bizley\podium\tests\enums
 */
class PollTypeTest extends TestCase
{
    public function testData(): void
    {
        $this->assertEquals([
            PollType::SINGLE_CHOICE => 'poll.type.single',
            PollType::MULTIPLE_CHOICE => 'poll.type.multiple',
        ], PollType::data());
    }
}
