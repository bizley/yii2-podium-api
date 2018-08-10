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
    /**
     * @var array [table => [row1 columns => values], [row2 columns => values], ...]
     */
    public $fixtures = [];

    /**
     * @var string
     */
    protected static $driverName = 'mysql';

    /**
     * @var array
     */
    protected static $database = [
        'dsn' => 'mysql:host=127.0.0.1;dbname=podium_test',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
    ];

    /**
     * @var Connection
     */
    protected static $db;

    /**
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
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
            'id' => 'PodiumAPITest',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__ . '/../vendor/',
            'controllerMap' => [
                'migrate' => [
                    'class' => EchoMigrateController::class,
                    'migrationPath' => __DIR__ . '/../migrations/',
                    'interactive' => false,
                ],
            ],
            'components' => [
                'db' => static::getConnection(),
                'podium' => [
                    'class' => Podium::class
                ],
            ],
        ], $config));
    }

    /**
     * @param string $route
     * @param array $params
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
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

    /**
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     */
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

    /**
     * @throws \yii\db\Exception
     */
    public function fixturesUp(): void
    {
        foreach ($this->fixtures as $table => $data) {
            foreach ($data as $row) {
                static::$db->createCommand()->insert($table, $row)->execute();
            }
        }
    }
    /**
     * @throws \yii\db\Exception
     */
    public function fixturesDown(): void
    {
        static::$db->createCommand('SET FOREIGN_KEY_CHECKS=0;')->execute();
        foreach ($this->fixtures as $table => $data) {
            static::$db->createCommand()->truncateTable($table)->execute();
        }
        static::$db->createCommand('SET FOREIGN_KEY_CHECKS=1;')->execute();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws \yii\db\Exception
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
    }
}
