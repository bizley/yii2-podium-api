<?php

namespace bizley\podium\api\components;

use yii\base\Event;

/**
 * Class PodiumEvent
 * @package bizley\podium\api\repositories
 */
class PodiumEvent extends Event
{
    /**
     * @var bool whether to continue running the method.
     */
    public $isValid = true;
}
