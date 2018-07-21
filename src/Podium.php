<?php

declare(strict_types=1);

namespace bizley\podium\api;

use bizley\podium\api\base\Access;
use bizley\podium\api\base\Account;
use bizley\podium\api\base\Config;
use bizley\podium\api\base\Member;
use yii\base\InvalidConfigException;
use yii\di\ServiceLocator;
use yii\i18n\PhpMessageSource;

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
 * @property null|Access $access
 * @property null|Account $account
 * @property null|Config $config
 * @property null|Member $member
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
     * @throws InvalidConfigException
     */
    public function init(): void
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
            'account' => ['class' => Account::class],
            'member' => ['class' => Member::class],
            'access' => ['class' => Access::class],
            'config' => ['class' => Config::class],
        ];
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
     * Returns account component.
     * @return Account|null|object
     * @throws InvalidConfigException
     */
    public function getAccount()
    {
        return $this->get('account');
    }

    /**
     * Returns access component.
     * @return Access|null|object
     * @throws InvalidConfigException
     */
    public function getAccess()
    {
        return $this->get('access');
    }

    /**
     * Returns config component.
     * @return Config|null|object
     * @throws InvalidConfigException
     */
    public function getConfig()
    {
        return $this->get('config');
    }

    public function prepareTranslations(): void
    {
        \Yii::$app->getI18n()->translations['podium.*'] = [
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
            $component['podium'] = $this;
            $this->set($id, $component);
        }
    }
}
