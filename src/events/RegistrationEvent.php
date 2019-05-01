<?php

declare(strict_types=1);

namespace bizley\podium\api\events;

use bizley\podium\api\models\member\MemberRegisterer;
use yii\base\Event;

/**
 * Class RegistrationEvent
 * @package bizley\podium\api\events
 */
class RegistrationEvent extends Event
{
    /**
     * @var bool whether member can be registered
     */
    public $canRegister = true;

    /**
     * @var MemberRegisterer
     */
    public $model;
}
