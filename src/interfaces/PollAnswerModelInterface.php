<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface PollAnswerModelInterface
 * @package bizley\podium\api\interfaces
 */
interface PollAnswerModelInterface extends ModelInterface
{
    /**
     * @return int
     */
    public function getPollId(): int;
}
