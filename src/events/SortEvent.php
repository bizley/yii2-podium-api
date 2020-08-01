<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use yii\base\Event;

/**
 * Class SortEvent
 * @package bizley\podium\api\events
 */
class SortEvent extends Event
{
    /**
     * @var bool whether models can be sorted
     */
    public bool $canSort = true;
}
