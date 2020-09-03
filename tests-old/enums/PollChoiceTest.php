<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\PollChoice;
use bizley\podium\tests\TestCase;

/**
 * Class PollChoiceTest
 * @package bizley\podium\tests\enums
 */
class PollChoiceTest extends TestCase
{
    public function testData(): void
    {
        $this->assertEquals([
            PollChoice::SINGLE => 'poll.choice.single',
            PollChoice::MULTIPLE => 'poll.choice.multiple',
        ], PollChoice::data());
    }
}
