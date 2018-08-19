<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\api\base\Account;
use bizley\podium\api\base\Category;
use bizley\podium\api\base\Forum;
use bizley\podium\api\base\Member;
use bizley\podium\api\base\Post;
use bizley\podium\api\base\Rank;
use bizley\podium\api\base\Thread;
use bizley\podium\api\Podium;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Application;
use yii\web\User;

/**
 * Class PodiumTest
 * @package bizley\podium\tests
 */
class PodiumTest extends DbTestCase
{
    public static function setUpBeforeClass(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
        if (static::$db) {
            static::$db->close();
        }
    }

    /**
     * @param array $config
     * @param string $appClass
     */
    protected static function mockApplication(array $config = [], string $appClass = Application::class): void
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'PodiumAPITest',
            'basePath' => __DIR__,
            'vendorPath' => __DIR__ . '/../vendor/',
            'components' => [
                'podium' => [
                    'class' => Podium::class,
                ],
                'urlManager' => [
                    'showScriptName' => true,
                ],
                'request' => [
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                ],
            ],
        ], $config));
    }

    protected function setUp(): void
    {
    }

    public function tearDown(): void
    {
        Yii::$app = null;
    }

    public function testVersion(): void
    {
        static::mockApplication();
        $this->assertEquals('1.0.0', $this->podium()->getVersion());
    }

    public function testAddDefaultClassToComponentConfig(): void
    {
        static::mockApplication([
            'components' => [
                'podium' => [
                    'class' => Podium::class,
                    'components' => [
                        'account' => [
                            'property' => 1,
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertEquals(Account::class, $this->podium()->getComponents()['account']['class']);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testGetAccount(): void
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
        $this->assertInstanceOf(Account::class, $this->podium()->getAccount());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testGetCategory(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Category::class, $this->podium()->getCategory());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testGetForum(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Forum::class, $this->podium()->getForum());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     * @throws \yii\db\Exception
     */
    public function testGetMember(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Member::class, $this->podium()->getMember());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testGetPost(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Post::class, $this->podium()->getPost());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testGetThread(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Thread::class, $this->podium()->getThread());
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function testGetRank(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Rank::class, $this->podium()->getRank());
    }
}
