<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\PollAnswerAction;
use PHPUnit\Framework\TestCase;

class PollAnswerActionTest extends TestCase
{
    public function testData(): void
    {
        self::assertEquals(
            [
                PollAnswerAction::ADD => 'poll.answer.action.add',
                PollAnswerAction::EDIT => 'poll.answer.action.edit',
                PollAnswerAction::REMOVE => 'poll.answer.action.remove',
            ],
            PollAnswerAction::data()
        );
    }
}
