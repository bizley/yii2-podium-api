<?php

namespace bizley\podium\api\components;

use bizley\podium\api\Podium;
use bizley\podium\api\repositories\RepositoryInterface;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\di\Instance;

/**
 * General Podium Component
 *
 * @property object|ActiveRecord $repo
 */
abstract class Component extends \yii\base\Component
{
    /**
     * @var Podium Engine
     */
    public $podium;

    /**
     * @var array|string|RepositoryInterface Repository configuration
     */
    public $repositoryConfig;

    private $_repo;

    /**
     * @param bool $clear whether to reset repository data first
     * @return object|ActiveRecord
     * @throws InvalidConfigException
     */
    public function getRepo($clear = false)
    {
        if (null === $this->_repo || $clear === true) {
            $this->_repo = Instance::ensure($this->repositoryConfig, RepositoryInterface::class, $this->podium);
        }
        return $this->_repo;
    }
}
