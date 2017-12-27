<?php

namespace bizley\podium\api;

use bizley\podium\api\components\Category;
use bizley\podium\api\components\Forum;
use bizley\podium\api\components\Group;
use bizley\podium\api\components\Member;
use bizley\podium\api\components\Poll;
use bizley\podium\api\components\Post;
use bizley\podium\api\components\Thread;
use bizley\podium\api\repositories\Member as MemberRepo;
use yii\base\InvalidConfigException;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\di\ServiceLocator;
use yii\helpers\ArrayHelper;

/**
 * Podium API
 * Yii 2 Forum Engine
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @version 0.11.0
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
 */
class Podium extends ServiceLocator
{
    protected $version = '0.11.0';

    /**
     * @var array Podium components
     */
    public static $podiumComponents = [
        'member' => [
            'class' => Member::class,
            'repositoryConfig' => [
                'class' => MemberRepo::class,
            ],
        ],
        'group' => [
            'class' => Group::class
        ],
        'category' => [
            'class' => Category::class
        ],
        'forum' => [
            'class' => Forum::class
        ],
        'thread' => [
            'class' => Thread::class
        ],
        'post' => [
            'class' => Post::class
        ],
        'poll' => [
            'class' => Poll::class
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
            if (\is_array($component) && isset(static::$podiumComponents[$id])) {
                $this->set($id, ArrayHelper::merge(static::$podiumComponents[$id], $component, ['podium' => $this]));
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
                $this->set($id, ArrayHelper::merge($component, ['podium' => $this]));
            }
        }
    }
}
