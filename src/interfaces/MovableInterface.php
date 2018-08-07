<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface MovableInterface
 * @package bizley\podium\api\interfaces
 */
interface MovableInterface
{
    /**
     * Moves model.
     * @return bool
     */
    public function move(): bool;

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
