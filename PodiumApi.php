<?php

namespace bizley\podium\api;

use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;

/**
 * Podium API
 * Yii 2 Forum Engine
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @version 0.10.0
 * @license Apache License 2.0
 *
 * https://github.com/bizley/yii2-podium-api
 * Please report all issues at GitHub
 * https://github.com/bizley/yii2-podium-api/issues
 *
 * Podium requires Yii 2
 * http://www.yiiframework.com
 * https://github.com/yiisoft/yii2
 *
 * For Podium API documentation go to
 * https://github.com/bizley/yii2-podium-api/wiki
 *
 */
class PodiumApi extends Module
{
    /**
     * @inheritdoc
     */
    protected function defaultVersion()
    {
        return '0.10.0';
    }

    /**
     * @var array Podium components
     */
    public static $podiumComponents = [
        'member' => [
            'class' => \bizley\podium\api\components\Member::class,
            'repositoryConfig' => [
                'class' => \bizley\podium\api\repositories\Member::class,
                'as timestamp' => TimestampBehavior::class,
                'as slug' => [
                    'class' => SluggableBehavior::class,
                    'attribute' => 'username',
                ],
            ],
        ],
        'group' => [
            'class' => \bizley\podium\api\components\Group::class
        ],
        'category' => [
            'class' => \bizley\podium\api\components\Category::class
        ],
        'forum' => [
            'class' => \bizley\podium\api\components\Forum::class
        ],
        'thread' => [
            'class' => \bizley\podium\api\components\Thread::class
        ],
        'post' => [
            'class' => \bizley\podium\api\components\Post::class
        ],
        'poll' => [
            'class' => \bizley\podium\api\components\Poll::class
        ]
    ];

    /**
     * Initializes components.
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->customizeComponents();
        $this->completeComponents();
    }

    /**
     * Merges default components configuration with the one given by developer.
     * @throws InvalidConfigException
     */
    protected function customizeComponents()
    {
        foreach ($this->getComponents(true) as $id => $component) {
            if (is_array($component) && isset(static::$podiumComponents[$id])) {
                $this->set($id, array_merge(static::$podiumComponents[$id], $component, ['podium' => $this]));
            }
        }
    }

    /**
     * Configures missing default components.
     * @throws InvalidConfigException
     */
    protected function completeComponents()
    {
        $configuredComponents = $this->getComponents(true);
        foreach (static::$podiumComponents as $id => $component) {
            if (!isset($configuredComponents[$id])) {
                $this->set($id, array_merge($component, ['podium' => $this]));
            }
        }
    }
}
