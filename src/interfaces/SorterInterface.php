<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;

/**
 * Interface SorterInterface
 * @package bizley\podium\api\interfaces
 */
interface SorterInterface
{
    /**
     * Loads sorter data.
     * @param array $data
     * @return bool
     */
    public function loadData(array $data = []): bool;

    /**
     * Sorts models.
     * @return PodiumResponse
     */
    public function sort(): PodiumResponse;

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
