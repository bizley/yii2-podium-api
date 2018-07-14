<?php

declare(strict_types=1);

namespace bizley\podium\api;

use bizley\podium\api\base\Access;
use bizley\podium\api\base\Admin;
use bizley\podium\api\base\Category;
use bizley\podium\api\base\Forum;
use bizley\podium\api\base\Group;
use bizley\podium\api\base\Member;
use bizley\podium\api\base\Poll;
use bizley\podium\api\base\Post;
use bizley\podium\api\base\Thread;
use bizley\podium\api\repos\AcquaintanceRepo;
use bizley\podium\api\repos\MemberRepo;
use yii\base\InvalidConfigException;
use yii\di\ServiceLocator;

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
 *
 * @property Member $member
 * @property Admin $admin
 * @property Access $access
 */
class Podium extends ServiceLocator
{
    private $version = '1.0.0';

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Returns the configuration of core Podium components.
     */
    public function coreComponents(): array
    {
        return [
            'access' => [
                'class' => Access::class,
            ],
            'member' => [
                'class' => Member::class,
                'repositories' => [
                    'member' => [
                        'class' => MemberRepo::class,
                    ],
                    'acquaintance' => [
                        'class' => AcquaintanceRepo::class,
                    ],
                ],
            ],
            'admin' => [
                'class' => Admin::class,
            ],
            'group' => [
                'class' => Group::class,
            ],
            'category' => [
                'class' => Category::class,
            ],
            'forum' => [
                'class' => Forum::class,
            ],
            'thread' => [
                'class' => Thread::class,
            ],
            'post' => [
                'class' => Post::class,
            ],
            'poll' => [
                'class' => Poll::class,
            ]
        ];
    }

    /**
     * Returns access component.
     * @return Access|null|object
     * @throws InvalidConfigException
     */
    public function getAccess() // BC declaration
    {
        return $this->get('access');
    }

    /**
     * Returns member component.
     * @return Member|null|object
     * @throws InvalidConfigException
     */
    public function getMember() // BC declaration
    {
        return $this->get('member');
    }

    /**
     * Returns admin component.
     * @return Admin|null|object
     * @throws InvalidConfigException
     */
    public function getAdmin() // BC declaration
    {
        return $this->get('admin');
    }

    /**
     * Returns group component.
     * @return Group|null|object
     * @throws InvalidConfigException
     */
    public function getGroup() // BC declaration
    {
        return $this->get('group');
    }

    /**
     * Returns category component.
     * @return Category|null|object
     * @throws InvalidConfigException
     */
    public function getCategory() // BC declaration
    {
        return $this->get('category');
    }

    /**
     * Returns forum component.
     * @return Forum|null|object
     * @throws InvalidConfigException
     */
    public function getForum() // BC declaration
    {
        return $this->get('forum');
    }

    /**
     * Returns thread component.
     * @return Thread|null|object
     * @throws InvalidConfigException
     */
    public function getThread() // BC declaration
    {
        return $this->get('thread');
    }

    /**
     * Returns post component.
     * @return Post|null|object
     * @throws InvalidConfigException
     */
    public function getPost() // BC declaration
    {
        return $this->get('post');
    }

    /**
     * Returns poll component.
     * @return Poll|null|object
     * @throws InvalidConfigException
     */
    public function getPoll() // BC declaration
    {
        return $this->get('poll');
    }

    public function __construct($config = [])
    {
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (\is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }
        parent::__construct($config);
    }
}
