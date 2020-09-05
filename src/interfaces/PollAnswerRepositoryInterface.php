<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

interface PollAnswerRepositoryInterface
{
    /**
     * @param int|string|array $id
     */
    public function isAnswer($id): bool;

    public function create(string $answer): bool;

    /**
     * @param int|string|array $id
     */
    public function remove($id): bool;

    /**
     * @param int|string|array $id
     */
    public function edit($id, string $answer): bool;

    /**
     * @return int|string|array
     */
    public function getId();
}
