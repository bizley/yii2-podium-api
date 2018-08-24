<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MessageParticipantModelInterface
 * @package bizley\podium\api\interfaces
 */
interface MessageParticipantModelInterface extends ModelInterface
{
    /**
     * @return int
     */
    public function getMemberId(): int;

    /**
     * @return string
     */
    public function getSideId(): string;
}
