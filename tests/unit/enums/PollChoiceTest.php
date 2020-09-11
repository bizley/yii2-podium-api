<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\PollChoice;
use PHPUnit\Framework\TestCase;

class PollChoiceTest extends TestCase
{
    public function testData(): void
    {
        self::assertEquals(
            [
                PollChoice::SINGLE => 'poll.choice.single',
                PollChoice::MULTIPLE => 'poll.choice.multiple',
            ],
            PollChoice::data()
        );
    }
}
