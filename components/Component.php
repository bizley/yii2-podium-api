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
        if (empty($this->repositories[$name])) {
            throw new InvalidConfigException("Repository \"$name\" has not been configured!");
        }
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

    /**
     * Loads repository of given name or returns repository if already provided.
     * @param array|int|RepositoryInterface $entity
     * @param string $name
     * @return RepositoryInterface
     * @throws InvalidConfigException
     * @throws \bizley\podium\api\repositories\RepoNotFoundException
     */
    public function loadRepo($entity, $name)
    {
        if (empty($this->repositories[$name])) {
            throw new InvalidConfigException("Repository \"$name\" has not been configured!");
        }
        if (is_array($this->repositories[$name])) {
            if (!empty($this->repositories[$name]['class'])) {
                $repository = $this->repositories[$name]['class'];
            } else {
                throw new InvalidConfigException("Missing \"class\" configuration for \"$name\" repository!");
            }
        } else {
            $repository = $this->repositories[$name];
        }
        if ($entity instanceof $repository) {
            return $entity;
        }
        return $this->getRepo($name, true)->fetch($entity);
    }
}
