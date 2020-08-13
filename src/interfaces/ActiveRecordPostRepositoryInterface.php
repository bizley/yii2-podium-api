<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\ars\PostActiveRecord;
use yii\data\ActiveDataProvider;

interface ActiveRecordPostRepositoryInterface extends PostRepositoryInterface
{
    public function getModel(): PostActiveRecord;
    public function setModel(?PostActiveRecord $model): void;
    public function getCollection(): ?ActiveDataProvider;
}
