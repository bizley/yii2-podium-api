<?php

declare(strict_types=1);

namespace bizley\podium\api\interfaces;

use bizley\podium\api\ars\ThumbActiveRecord;

interface ActiveRecordThumbRepositoryInterface extends ThumbRepositoryInterface
{
    public function getModel(): ThumbActiveRecord;
    public function setModel(?ThumbActiveRecord $model): void;
}
