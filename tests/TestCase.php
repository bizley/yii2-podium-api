<?php

namespace bizley\podium\api\tests;

use bizley\podium\api\Podium;
use bizley\podium\api\tests\props\EchoMigrateController;
use Yii;
use yii\console\Controller;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    public static $params;
    protected static $driverName = 'mysql';
    protected static $database = [
        'dsn' => 'mysql:host=127.0.0.1;dbname=podiumtest',
        'username' => 'root',
        'password' => '',
    ];

    /**
     * @var Connection
     */
    protected static $db;

    protected function setUp()
    {
        static::mockApplication();
        static::runSilentMigration('migrate/up');
    }

    protected static function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'PodiumApiTest',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__ . '/../../../',
            'controllerMap' => [
                'migrate' => [
                    'class' => EchoMigrateController::class,
                    'migrationNamespaces' => ['bizley\podium\api\migrations'],
                    'migrationPath' => null,
                    'interactive' => false
                ],
            ],
            'components' => [
                'db' => static::getConnection(),
                'podium' => Podium::class
            ],
        ], $config));
    }

    protected static function runSilentMigration($route, $params = [])
    {
        ob_start();
        if (Yii::$app->runAction($route, $params) === Controller::EXIT_CODE_NORMAL) {
            echo 'Migration OK.';
            ob_end_clean();
        } else {
            echo 'Migration failed!';
            ob_end_flush();
        }
    }

    protected function tearDown()
    {
        static::runSilentMigration('migrate/down');
        if (static::$db) {
            static::$db->close();
        }
        Yii::$app = null;
    }

    public static function getConnection()
    {
        if (static::$db === null) {
            $db = new Connection();
            $db->dsn = static::$database['dsn'];
            if (isset(static::$database['username'])) {
                $db->username = static::$database['username'];
                $db->password = static::$database['password'];
            }
            if (isset(static::$database['attributes'])) {
                $db->attributes = static::$database['attributes'];
            }
            if (!$db->isActive) {
                $db->open();
            }
            static::$db = $db;
        }
        return static::$db;
    }

    /**
     * @return Podium
     */
    protected function podium()
    {
        return Yii::$app->podium;
    }
}