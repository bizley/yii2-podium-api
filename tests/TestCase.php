<?php

namespace bizley\podium\api\tests;

use Yii;

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
     * @var \yii\db\Connection
     */
    protected static $db;

    public function setUp()
    {
        $this->mockApplication();

        $pdo_database = 'pdo_' . static::$driverName;
        if (!extension_loaded('pdo') || !extension_loaded($pdo_database)) {
            static::markTestSkipped('pdo and ' . $pdo_database . ' extension are required.');
        }

        static::runSilentMigration('migrate/up');
    }

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(\yii\helpers\ArrayHelper::merge([
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
            ],
            'modules' => [
                'podium-api' => [
                    'class' => \bizley\podium\api\PodiumApi::class
                ]
            ]
        ], $config));
    }

    protected static function runSilentMigration($route, $params = [])
    {
        ob_start();
        if (Yii::$app->runAction($route, $params) === \yii\console\Controller::EXIT_CODE_NORMAL) {
            echo 'Migration OK.';
            ob_end_clean();
        } else {
            echo 'Migration failed!';
            ob_end_flush();
        }
    }

    public function tearDown()
    {
        parent::tearDown();
        static::runSilentMigration('migrate/down');
        if (static::$db) {
            static::$db->close();
        }
        Yii::$app = null;
    }

    public static function getConnection()
    {
        if (static::$db === null) {
            $db = new \yii\db\Connection();
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

    protected function podium()
    {
        return Yii::$app->getModule('podium-api');
    }
}