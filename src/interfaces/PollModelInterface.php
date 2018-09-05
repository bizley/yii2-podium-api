<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface PollModelInterface
 * @package bizley\podium\api\interfaces
 */
interface PollModelInterface extends ModelInterface
{
    /**
     * @return string
     */
    public function getChoiceId(): string;

    /**
     * @param int $modelId
     * @return ModelInterface|null
     */
    public static function findByPostId(int $modelId): ?ModelInterface;
}
