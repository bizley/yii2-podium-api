<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\api\base\Account;
use bizley\podium\api\base\Category;
use bizley\podium\api\base\Forum;
use bizley\podium\api\base\Group;
use bizley\podium\api\base\Member;
use bizley\podium\api\base\Message;
use bizley\podium\api\base\NoMembershipException;
use bizley\podium\api\base\Poll;
use bizley\podium\api\base\Post;
use bizley\podium\api\base\Rank;
use bizley\podium\api\base\Thread;
use bizley\podium\api\Podium;
use bizley\podium\tests\props\UserIdentity;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
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

    public function testAddBridgeToComponentConfig(): void
    {
        static::mockApplication([
            'components' => [
                'podium' => [
                    'class' => Podium::class,
                    'components' => [
                        'test' => [
                            'class' => Component::class,
                            'podiumBridge' => true,
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertInstanceOf(Podium::class, $this->podium()->getComponents()['test']['podium']);
    }

    /**
     * @throws InvalidConfigException
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
     * @throws InvalidConfigException
     */
    public function testGetCategory(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Category::class, $this->podium()->getCategory());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetForum(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Forum::class, $this->podium()->getForum());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetMember(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Member::class, $this->podium()->getMember());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetPost(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Post::class, $this->podium()->getPost());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetThread(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Thread::class, $this->podium()->getThread());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetRank(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Rank::class, $this->podium()->getRank());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetMessage(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Message::class, $this->podium()->getMessage());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetGroup(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Group::class, $this->podium()->getGroup());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetPoll(): void
    {
        static::mockApplication();
        $this->assertInstanceOf(Poll::class, $this->podium()->getPoll());
    }

    public function testNoMembershipExcName(): void
    {
        $this->assertEquals('No Membership Exception', (new NoMembershipException())->getName());
    }
}
