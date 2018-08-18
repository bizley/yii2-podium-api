<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface RankInterface
 * @package bizley\podium\api\interfaces
 */
interface RankInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getRankById(int $id): ?ModelInterface;

    /**
     * Returns rank form handler.
     * @return ModelFormInterface
     */
    public function getRankForm(): ModelFormInterface;

    /**
     * Creates rank.
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool;

    /**
     * Updates rank.
     * @param ModelFormInterface $rankForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $rankForm, array $data): bool;

    /**
     * @param RemovableInterface $rankRemover
     * @return bool
     */
    public function remove(RemovableInterface $rankRemover): bool;
}
