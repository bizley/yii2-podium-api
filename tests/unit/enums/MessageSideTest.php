<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\MessageSide;
use PHPUnit\Framework\TestCase;

class MessageSideTest extends TestCase
{
    public function testData(): void
    {
        self::assertEquals(
            [
                MessageSide::SENDER => 'message.side.sender',
                MessageSide::RECEIVER => 'message.side.receiver',
            ],
            MessageSide::data()
        );
    }
}
