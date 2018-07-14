<?php

declare(strict_types=1);

namespace bizley\podium\api\base;

use bizley\podium\api\models\Friendship;
use bizley\podium\api\models\FriendshipInterface;
use bizley\podium\api\models\Ignoring;
use bizley\podium\api\models\IgnoringInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class Member
 * @package bizley\podium\api\base
 */
class Member extends Component
{
    /**
     * @var string|array|Friendship friendship handler
     * It can be component name, configuration array, class name, or Friendship object.
     * Target instance must implement FriendshipInterface.
     */
    public $friendship = Friendship::class;

    /**
     * @var string|array|Ignoring ignoring handler
     * It can be component name, configuration array, class name, or Ignoring object.
     * Target instance must implement IgnoringInterface.
     */
    public $ignoring = Ignoring::class;

    /**
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();
        $this->friendship = Instance::ensure($this->friendship, FriendshipInterface::class);
        $this->ignoring = Instance::ensure($this->ignoring, IgnoringInterface::class);
    }
}
