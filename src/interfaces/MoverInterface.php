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
    public static function findById(int $modelId): ?MoverInterface;

    /**
     * Moves model.
     * @return PodiumResponse
     */
    public function move(): PodiumResponse;

    /**
     * @param ModelInterface $category
     */
    public function setCategory(ModelInterface $category): void;

    /**
     * @param ModelInterface $forum
     */
    public function setForum(ModelInterface $forum): void;

    /**
     * @param ModelInterface $thread
     */
    public function setThread(ModelInterface $thread): void;
}
