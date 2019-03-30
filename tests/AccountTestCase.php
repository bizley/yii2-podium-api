<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\InvalidRouteException;
use yii\web\User;

/**
 * Class AccountTestCase
 * @package bizley\podium\tests
 */
abstract class AccountTestCase extends DbTestCase
{
    /**
     * @throws InvalidRouteException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     */
    public static function setUpBeforeClass(): void
    {
        static::mockApplication([
            'components' => [
                'user' => [
                    'class' => User::class,
                    'identityClass' => UserIdentity::class,
                    'enableSession' => false,
                    'loginUrl' => null,
                ],
            ],
        ]);
        static::runSilentMigration('migrate/up');
    }

    protected function tearDown(): void
    {
        Yii::$app->user->setIdentity(null);
    }
}
