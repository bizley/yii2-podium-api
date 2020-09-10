<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use PHPUnit\Framework\TestCase;
use Yii;
use yii\console\Application;

class AppTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        new Application(
            [
                'id' => 'PodiumAPITest',
                'basePath' => __DIR__,
                'vendorPath' => __DIR__ . '/../vendor/',
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        Yii::$app = null;
    }
}
