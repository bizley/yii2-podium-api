<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\MessageSide;
use bizley\podium\tests\TestCase;

/**
 * Class MessageSideTest
 * @package bizley\podium\tests\enums
 */
class MessageSideTest extends TestCase
{
    public function testData(): void
    {
        $this->assertEquals([
            MessageSide::SENDER => 'message.side.sender',
            MessageSide::RECEIVER => 'message.side.receiver',
        ], MessageSide::data());
    }
}
