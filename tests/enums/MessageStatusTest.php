<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\MessageStatus;
use bizley\podium\tests\TestCase;

/**
 * Class MessageStatusTest
 * @package bizley\podium\tests\enums
 */
class MessageStatusTest extends TestCase
{
    public function testData(): void
    {
        $this->assertEquals([
            MessageStatus::NEW => 'message.status.new',
            MessageStatus::READ => 'message.status.read',
            MessageStatus::REPLIED => 'message.status.replied',
        ], MessageStatus::data());
    }
}
