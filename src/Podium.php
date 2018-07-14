<?php

declare(strict_types=1);

namespace bizley\podium\api;

use bizley\podium\api\base\Access;
use bizley\podium\api\base\Admin;
use bizley\podium\api\base\Category;
use bizley\podium\api\base\Forum;
use bizley\podium\api\base\Group;
use bizley\podium\api\base\Member;
use bizley\podium\api\base\Membership;
use bizley\podium\api\base\Poll;
use bizley\podium\api\base\Post;
use bizley\podium\api\base\Thread;
use yii\base\InvalidConfigException;
use yii\di\ServiceLocator;

/**
 * Podium API
 * Yii 2 Forum Engine
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @version 1.0.0
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
 * @property null|Member $member
 * @property null|Membership $membership
 * @property string $version
 */
class Podium extends ServiceLocator
{
    private $_version = '1.0.0';

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->_version;
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

    /**
     * Returns the configuration of core Podium components.
     */
    public function coreComponents(): array
    {
        return [
            'member' => ['class' => Member::class],
            'membership' => ['class' => Membership::class],
        ];
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
     * Returns membership component.
     * @return Membership|null|object
     * @throws InvalidConfigException
     */
    public function getMembership() // BC declaration
    {
        return $this->get('membership');
    }
}
