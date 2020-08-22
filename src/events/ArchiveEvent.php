<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\RepositoryInterface;
use yii\base\Event;

class ArchiveEvent extends Event
{
    /**
     * @var bool whether model can be archived
     */
    public bool $canArchive = true;

    /**
     * @var bool whether model can be revived
     */
    public bool $canRevive = true;

    public ?RepositoryInterface $repository = null;
}
