<?php

declare(strict_types=1);

namespace bizley\podium\api;

use bizley\podium\api\base\Account;
use bizley\podium\api\base\Category;
use bizley\podium\api\base\Forum;
use bizley\podium\api\base\Group;
use bizley\podium\api\base\Member;
use bizley\podium\api\base\Message;
use bizley\podium\api\base\Poll;
use bizley\podium\api\base\Post;
use bizley\podium\api\base\Rank;
use bizley\podium\api\base\Thread;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\ServiceLocator;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;
use function is_array;

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
 * @property Account $account
 * @property Category $category
 * @property Forum $forum
 * @property Group $group
 * @property Member $member
 * @property Message $message
 * @property Poll $poll
 * @property Post $post
 * @property Rank $rank
 * @property Thread $thread
 *
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

    /**
     * Podium constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        foreach ($this->coreComponents() as $id => $component) {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            } elseif (is_array($config['components'][$id]) && !isset($config['components'][$id]['class'])) {
                $config['components'][$id]['class'] = $component['class'];
            }
        }

        parent::__construct($config);
    }

    /**
     * @throws InvalidConfigException
     */
    public function init() // BC signature
    {
        parent::init();

        $this->prepareTranslations();
        $this->completeComponents();
    }

    /**
     * Returns the configuration of core Podium components.
     */
    public function coreComponents(): array
    {
        return [
            'account' => [
                'class' => Account::class,
                'podiumBridge' => true,
            ],
            'category' => ['class' => Category::class],
            'forum' => ['class' => Forum::class],
            'group' => ['class' => Group::class],
            'member' => ['class' => Member::class],
            'message' => ['class' => Message::class],
            'poll' => ['class' => Poll::class],
            'post' => ['class' => Post::class],
            'rank' => ['class' => Rank::class],
            'thread' => ['class' => Thread::class],
        ];
    }

    /**
     * Returns account component.
     * @return Account|null|object
     * @throws InvalidConfigException
     */
    public function getAccount()
    {
        return $this->get('account');
    }

    /**
     * Returns category component.
     * @return Category|null|object
     * @throws InvalidConfigException
     */
    public function getCategory()
    {
        return $this->get('category');
    }

    /**
     * Returns forum component.
     * @return Forum|null|object
     * @throws InvalidConfigException
     */
    public function getForum()
    {
        return $this->get('forum');
    }

    /**
     * Returns group component.
     * @return Group|null|object
     * @throws InvalidConfigException
     */
    public function getGroup()
    {
        return $this->get('group');
    }

    /**
     * Returns member component.
     * @return Member|null|object
     * @throws InvalidConfigException
     */
    public function getMember()
    {
        return $this->get('member');
    }

    /**
     * Returns message component.
     * @return Message|null|object
     * @throws InvalidConfigException
     */
    public function getMessage()
    {
        return $this->get('message');
    }

    /**
     * Returns poll component.
     * @return Poll|null|object
     * @throws InvalidConfigException
     */
    public function getPoll()
    {
        return $this->get('poll');
    }

    /**
     * Returns post component.
     * @return Post|null|object
     * @throws InvalidConfigException
     */
    public function getPost()
    {
        return $this->get('post');
    }

    /**
     * Returns rank component.
     * @return Rank|null|object
     * @throws InvalidConfigException
     */
    public function getRank()
    {
        return $this->get('rank');
    }

    /**
     * Returns thread component.
     * @return Thread|null|object
     * @throws InvalidConfigException
     */
    public function getThread()
    {
        return $this->get('thread');
    }

    public function prepareTranslations(): void
    {
        Yii::$app->getI18n()->translations['podium.*'] = [
            'class' => PhpMessageSource::class,
            'sourceLanguage' => 'en',
            'forceTranslation' => true,
            'basePath' => __DIR__ . '/messages',
        ];
    }

    /**
     * Sets Podium reference for custom components.
     * Custom component should be child of PodiumComponent class.
     * @throws InvalidConfigException
     */
    public function completeComponents(): void
    {
        $components = $this->getComponents();

        foreach ($components as $id => $component) {
            if (ArrayHelper::remove($component, 'podiumBridge', false)) {
                $component['podium'] = $this;
                $this->set($id, $component);
            }
        }
    }
}
