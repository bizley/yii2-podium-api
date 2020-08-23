<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

class VoteEvent extends Event
{
    /**
     * @var bool whether model can be voted for
     */
    public bool $canVote = true;

    public ?RepositoryInterface $repository = null;
}
