<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\ars\CategoryActiveRecord;
use yii\data\ActiveDataProvider;

interface ARCategoryRepositoryInterface extends CategoryRepositoryInterface
{
    public function setModel(CategoryActiveRecord $model): void;

    public function getModel(): ?CategoryActiveRecord;

    public function setCollection(ActiveDataProvider $collection): void;

    public function getCollection(): ActiveDataProvider;
}
