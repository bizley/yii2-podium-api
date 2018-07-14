<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\api\Podium;
use bizley\podium\tests\props\EchoMigrateController;
use Yii;
use yii\console\Application;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\helpers\ArrayHelper;

/**
 * Class TestCase
 * @package bizley\podium\tests
 */
abstract class DbTestCase extends TestCase
{
    protected static $driverName = 'mysql';
    protected static $database = [
        'dsn' => 'mysql:host=127.0.0.1;dbname=podiumtest',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ];

    /**
     * @var Connection
     */
    protected static $db;

    /**
     * @throws \yii\db\Exception
     */
    public static function setUpBeforeClass(): void
    {
        static::mockApplication();
        static::runSilentMigration('migrate/up');
    }

    /**
     * @param array $config
     * @param string $appClass
     * @throws \yii\db\Exception
     */
    protected static function mockApplication(array $config = [], string $appClass = Application::class): void
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'PodiumApiTest',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__ . '/../',
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

    /**
     * @param string $route
     * @param array $params
     */
    protected static function runSilentMigration(string $route, array $params = []): void
    {
        ob_start();
        if (Yii::$app->runAction($route, $params) === ExitCode::OK) {
            ob_end_clean();
        } else {
            fwrite(STDOUT, "\nMigration failed!\n");
            ob_end_flush();
        }
    }

    public static function tearDownAfterClass(): void
    {
        static::runSilentMigration('migrate/down', ['all']);
        if (static::$db) {
            static::$db->close();
        }
        Yii::$app = null;
    }

    /**
     * @return Connection
     * @throws \yii\db\Exception
     */
    public static function getConnection(): Connection
    {
        if (static::$db === null) {
            $db = new Connection();
            $db->dsn = static::$database['dsn'];
            $db->charset = static::$database['charset'];
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
    protected function podium(): Podium
    {
        return Yii::$app->podium;
    }
}
