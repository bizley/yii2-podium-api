<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\components\PodiumResponse;
use yii\data\DataFilter;
use yii\data\DataProviderInterface;
use yii\data\Pagination;
use yii\data\Sort;

interface RankInterface
{
    public function getById(int $id): ?ModelInterface;

    /**
     * @param bool|array|Sort|null       $sort
     * @param bool|array|Pagination|null $pagination
     */
    public function getAll(DataFilter $filter = null, $sort = null, $pagination = null): DataProviderInterface;

    /**
     * Returns rank form handler instance.
     */
    public function getForm(int $id = null): ?ModelFormInterface;

    /**
     * Creates rank.
     */
    public function create(array $data): PodiumResponse;

    /**
     * Updates rank.
     */
    public function edit(array $data): PodiumResponse;

    public function remove(int $id): PodiumResponse;
}
