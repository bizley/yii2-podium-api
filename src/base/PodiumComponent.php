<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\Podium;
use yii\base\Component;

/**
 * Class PodiumComponent
 * @package bizley\podium\api\base
 */
abstract class PodiumComponent extends Component
{
    /**
     * @var Podium
     */
    public $podium;
}
