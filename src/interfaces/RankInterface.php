<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\base\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

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
     * @param null|DataFilter $filter
     * @param null|bool|array|Sort $sort
     * @param null|bool|array|Pagination $pagination
     * @return DataProviderInterface
     */
    public function getRanks(?DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns rank form handler.
     * @return ModelFormInterface
     */
    public function getRankForm(): ModelFormInterface;

    /**
     * Creates rank.
     * @param array $data
     * @return PodiumResponse
     */
    public function create(array $data): PodiumResponse;

    /**
     * Updates rank.
     * @param ModelFormInterface $rankForm
     * @param array $data
     * @return PodiumResponse
     */
    public function edit(ModelFormInterface $rankForm, array $data): PodiumResponse;

    /**
     * @param RemovableInterface $rankRemover
     * @return PodiumResponse
     */
    public function remove(RemovableInterface $rankRemover): PodiumResponse;
}
