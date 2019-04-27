<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\base\PodiumResponse;

/**
 * Interface PollFormInterface
 * @package bizley\podium\api\interfaces
 */
interface PollFormInterface extends CategorisedFormInterface
{
    /**
     * @return ModelFormInterface
     */
    public function getAnswerForm(): ModelFormInterface;

    /**
     * @param int $answerId
     * @return RemoverInterface|null
     */
    public function getAnswerRemover(int $answerId): ?RemoverInterface;

    /**
     * Creates poll answer.
     * @param int $pollId
     * @param string $answer
     * @return PodiumResponse
     */
    public function createAnswer(int $pollId, string $answer): PodiumResponse;

    /**
     * Deletes poll answer.
     * @param int $answerId
     * @return PodiumResponse
     * @throws ModelNotFoundException
     */
    public function removeAnswer(int $answerId): PodiumResponse;
}
