<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\api\Podium;
use bizley\podium\tests\props\EchoMigrateController;
use Yii;
use yii\base\InvalidRouteException;
use yii\console\Application;
use yii\console\Exception as ConsoleException;
use yii\console\ExitCode;
use yii\db\Connection;
use yii\db\Exception as DbException;
use yii\helpers\ArrayHelper;
use yii\i18n\PhpMessageSource;

use function fwrite;
use function ob_end_clean;
use function ob_end_flush;
use function ob_start;

/**
 * Class TestCase
 * @package bizley\podium\tests
 */
abstract class DbTestCase extends TestCase
{
    /**
     * @var array [table => [row1 columns => values], [row2 columns => values], ...]
     */
    public array $fixtures = [];

    protected static string $driverName = 'mysql';
    protected static array $database = [];
    protected static Connection $db;
    public static array $params = [];

    public static function getParam(string $name, array $default = []): array
    {
        if (static::$params === []) {
            static::$params = require __DIR__ . '/config.php';
        }

        return static::$params[$name] ?? $default;
    }

    /**
     * @throws InvalidRouteException
     * @throws ConsoleException
     * @throws DbException
     */
    public static function setUpBeforeClass(): void
    {
        static::mockApplication();
        static::runSilentMigration('migrate/up');
    }

    /**
     * @param array $config
     * @param string $appClass
     * @throws DbException
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
                    'migrationNamespaces' => ['bizley\podium\api\migrations'],
                    'migrationPath' => null,
                    'interactive' => false,
                    'compact' => true,
                ],
            ],
            'components' => [
                'db' => static::getConnection(),
                'podium' => [
                    'class' => Podium::class
                ],
                'i18n' => [
                    'translations' => [
                        'podium.*' => [
                            'class' => PhpMessageSource::class,
                            'sourceLanguage' => 'en',
                            'forceTranslation' => true,
                            'basePath' => '@app/messages',
                        ],
                    ],
                ],
            ],
        ], $config));
    }

    /**
     * @param string $route
     * @param array $params
     * @throws InvalidRouteException
     * @throws ConsoleException
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
     * @throws InvalidRouteException
     * @throws ConsoleException
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
     * @throws DbException
     */
    public static function getConnection(): Connection
    {
        static::$database = static::getParam(static::$driverName);

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

    protected function podium(): Podium
    {
        return Yii::$app->podium;
    }

    /**
     * @throws DbException
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
     * @throws DbException
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
     * @throws DbException
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws DbException
     */
    protected function tearDown(): void
    {
        $this->fixturesDown();
    }
}
