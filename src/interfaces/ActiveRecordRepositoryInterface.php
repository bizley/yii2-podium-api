<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

interface ActiveRecordRepositoryInterface
{
    public function setModel(ActiveRecord $model): void;

    public function getModel(): ?ActiveRecord;

    public function setCollection(ActiveDataProvider $collection): void;

    public function getCollection(): ActiveDataProvider;

    /**
     * @param int|string|array $id
     */
    public function fetchOne($id): bool;

    /**
     * @param mixed|null $filter
     * @param mixed|null $sort
     * @param mixed|null $pagination
     */
    public function fetchAll($filter = null, $sort = null, $pagination = null): void;
}
