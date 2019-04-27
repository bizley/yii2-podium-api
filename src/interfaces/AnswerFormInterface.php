<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface AnswerFormInterface
 * @package bizley\podium\api\interfaces
 */
interface AnswerFormInterface extends ModelFormInterface
{
    /**
     * @param int $pollId
     */
    public function setPollId(int $pollId): void;

    /**
     * @param string $answer
     */
    public function setAnswer(string $answer): void;
}
