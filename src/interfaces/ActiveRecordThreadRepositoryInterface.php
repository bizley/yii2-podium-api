<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\ars\ThreadActiveRecord;
use yii\data\ActiveDataProvider;

interface ActiveRecordThreadRepositoryInterface extends ThreadRepositoryInterface
{
    public function getModel(): ThreadActiveRecord;
    public function setModel(?ThreadActiveRecord $model): void;
    public function getCollection(): ?ActiveDataProvider;
}
