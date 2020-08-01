<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\BookmarkerInterface;
use yii\base\Event;

/**
 * Class BookmarkEvent
 * @package bizley\podium\api\events
 */
class BookmarkEvent extends Event
{
    /**
     * @var bool whether model can be marked
     */
    public bool $canMark = true;

    /**
     * @var BookmarkerInterface|null
     */
    public ?BookmarkerInterface $model = null;
}
