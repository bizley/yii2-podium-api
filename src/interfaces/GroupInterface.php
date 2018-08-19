<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

/**
 * Interface GroupInterface
 * @package bizley\podium\api\interfaces
 */
interface GroupInterface
{
    /**
     * @param int $id
     * @return ModelInterface|null
     */
    public function getGroupById(int $id): ?ModelInterface;

    /**
     * Returns group form handler.
     * @return ModelFormInterface
     */
    public function getGroupForm(): ModelFormInterface;

    /**
     * Creates group.
     * @param array $data
     * @return bool
     */
    public function create(array $data): bool;

    /**
     * Updates group.
     * @param ModelFormInterface $groupForm
     * @param array $data
     * @return bool
     */
    public function edit(ModelFormInterface $groupForm, array $data): bool;

    /**
     * @param RemovableInterface $rankRemover
     * @return bool
     */
    public function remove(RemovableInterface $rankRemover): bool;
}
