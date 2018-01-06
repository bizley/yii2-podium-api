<?php

namespace bizley\podium\api\components;

use bizley\podium\api\Podium;
use bizley\podium\api\repositories\RepositoryInterface;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * General Podium Component
 */
abstract class Component extends \yii\base\Component
{
    /**
     * @var Podium Engine
     */
    public $podium;

    /**
     * @var array Repositories configuration
     */
    public $repositories = [];

    private $_repos = [];

    /**
     * @param string $name repository name key
     * @param bool $clear whether to reset repository data first
     * @return RepositoryInterface
     * @throws InvalidConfigException
     */
    public function getRepo($name, $clear = false)
    {
        if ($clear === true || empty($this->_repos[$name])) {
            $this->_repos[$name] = Instance::ensure($this->repositories[$name], RepositoryInterface::class, $this->podium);
        }
        return $this->_repos[$name];
    }

    /**
     * @param string $name
     * @return RepositoryInterface|mixed
     * @throws \yii\base\InvalidCallException
     * @throws InvalidConfigException
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (substr($name, -4) === 'Repo') {
            $repo = substr($name, 0, -4);
            if (array_key_exists($repo, $this->repositories)) {
                return $this->getRepo($repo);
            }
        }
        return parent::__get($name);
    }

    public function loadRepo($entity, $repository)
    {
        if ($entity instanceof $repository) {
            return $entity;
        }
        //TODO
    }
}
