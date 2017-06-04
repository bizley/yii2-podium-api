<?php

use PHPUnit\Framework\TestCase;

class MemberTest extends TestCase
{
    protected static $driverName = 'mysql';
    protected static $database;
    /**
     * @var Connection
     */
    protected static $db;

    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(\yii\helpers\ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
        ], $config));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    /**
     * Clean up after test.
     * By default the application created with [[mockApplication]] will be destroyed.
     */
    protected function tearDown()
    {
        parent::tearDown();
        \Yii::$app = null;
    }

    public function testBindActionParams()
    {

    }
}