<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\ars\RankActiveRecord;
use yii\data\ActiveDataProvider;

interface ActiveRecordRankRepositoryInterface extends RankRepositoryInterface
{
    public function getModel(): RankActiveRecord;
    public function setModel(?RankActiveRecord $model): void;
    public function getCollection(): ?ActiveDataProvider;
}
