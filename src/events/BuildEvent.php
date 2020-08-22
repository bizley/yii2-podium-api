<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

class BuildEvent extends Event
{
    /**
     * @var bool whether model can be created
     */
    public bool $canCreate = true;

    /**
     * @var bool whether model can be edited
     */
    public bool $canEdit = true;

    public ?RepositoryInterface $repository = null;
}
