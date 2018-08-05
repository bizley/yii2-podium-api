<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\interfaces\ModelInterface;
use yii\base\Event;

/**
 * Class ModelEvent
 * @package bizley\podium\api\events
 */
class ModelEvent extends Event
{
    /**
     * @var bool whether model can be created
     */
    public $canCreate = true;

    /**
     * @var bool whether model can be edited
     */
    public $canEdit = true;

    /**
     * @var ModelInterface
     */
    public $model;
}
