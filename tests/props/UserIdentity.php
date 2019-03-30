<?php

declare(strict_types=1);

namespace bizley\podium\tests\props;

use yii\base\BaseObject;
use yii\web\IdentityInterface;

/**
 * Class UserIdentity
 * @package bizley\podium\tests\props
 */
class UserIdentity extends BaseObject implements IdentityInterface
{
    /**
     * @var string
     */
    public $id;

    private static $users = [
        '10' => ['id' => '10'],
        '11' => ['id' => '11'],
    ];

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return isset(static::$users[$id]) ? new static(static::$users[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null) {}

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey() {}

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey) {}
}
