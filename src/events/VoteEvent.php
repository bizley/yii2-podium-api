<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\VoterInterface;
use yii\base\Event;

/**
 * Class VoteEvent
 * @package bizley\podium\api\events
 */
class VoteEvent extends Event
{
    /**
     * @var bool whether model can be voted for
     */
    public bool $canVote = true;

    /**
     * @var VoterInterface|null
     */
    public ?VoterInterface $model = null;
}
