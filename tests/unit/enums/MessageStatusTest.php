<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\MessageStatus;
use PHPUnit\Framework\TestCase;

class MessageStatusTest extends TestCase
{
    public function testData(): void
    {
        self::assertEquals(
            [
                MessageStatus::NEW => 'message.status.new',
                MessageStatus::READ => 'message.status.read',
            ],
            MessageStatus::data()
        );
    }
}
