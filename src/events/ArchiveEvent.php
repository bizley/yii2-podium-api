<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\ArchiverInterface;
use yii\base\Event;

/**
 * Class RemoveEvent
 * @package bizley\podium\api\events
 */
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

    /**
     * @var ArchiverInterface|null
     */
    public ?ArchiverInterface $model = null;
}
