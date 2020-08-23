<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

class BookmarkEvent extends Event
{
    /**
     * @var bool whether model can be marked
     */
    public bool $canMark = true;

    public ?RepositoryInterface $repository = null;
}
