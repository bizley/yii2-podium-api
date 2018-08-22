<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

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
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getGroups(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns group form handler.
     * @return ModelFormInterface
     */
    public function getGroupForm(): ModelFormInterface;

    /**
     * Creates group.
     * @param array $data
     * @return PodiumResponse
     */
    public function create(array $data): PodiumResponse;

    /**
     * Updates group.
     * @param ModelFormInterface $groupForm
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $groupForm, array $data): PodiumResponse;

    /**
     * @param RemovableInterface $rankRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $rankRemover): PodiumResponse;
}
