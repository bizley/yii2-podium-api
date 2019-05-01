<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface MovableInterface
 * @package bizley\podium\api\interfaces
 */
interface MoverInterface
{
    /**
     * @param int $modelId
     * @return MoverInterface|null
     */
    public static function findById(int $modelId): ?ModelInterface;

    /**
     * Moves model.
     * @return PodiumResponse
     */
    public function move(): PodiumResponse;

    /**
     * @param ModelInterface $category
     */
    public function prepareCategory(ModelInterface $category): void;

    /**
     * @param ModelInterface $forum
     */
    public function prepareForum(ModelInterface $forum): void;

    /**
     * @param ModelInterface $thread
     */
    public function prepareThread(ModelInterface $thread): void;
}
