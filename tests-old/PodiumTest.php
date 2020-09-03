<?php

declare(strict_types=1);

namespace bizley\podium\tests;

use bizley\podium\api\components\Account;
use bizley\podium\api\components\Category;
use bizley\podium\api\components\Forum;
use bizley\podium\api\components\Group;
use bizley\podium\api\components\Member;
use bizley\podium\api\components\Message;
use bizley\podium\api\components\ModelNotFoundException;
use bizley\podium\api\components\NoMembershipException;
use bizley\podium\api\components\Poll;
use bizley\podium\api\components\Post;
use bizley\podium\api\components\Rank;
use bizley\podium\api\components\Thread;
use bizley\podium\api\InsufficientDataException;
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
        self::assertEquals('1.0.0', $this->podium()->getVersion());
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
        self::assertEquals(Account::class, $this->podium()->getComponents()['account']['class']);
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
        self::assertInstanceOf(Podium::class, $this->podium()->getComponents()['test']['podium']);
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
        self::assertInstanceOf(Account::class, $this->podium()->getAccount());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetCategory(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Category::class, $this->podium()->getCategory());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetForum(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Forum::class, $this->podium()->getForum());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetMember(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Member::class, $this->podium()->getMember());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetPost(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Post::class, $this->podium()->getPost());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetThread(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Thread::class, $this->podium()->getThread());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetRank(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Rank::class, $this->podium()->getRank());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetMessage(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Message::class, $this->podium()->getMessage());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetGroup(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Group::class, $this->podium()->getGroup());
    }

    /**
     * @throws InvalidConfigException
     */
    public function testGetPoll(): void
    {
        static::mockApplication();
        self::assertInstanceOf(Poll::class, $this->podium()->getPoll());
    }

    public function testNoMembershipExcName(): void
    {
        self::assertEquals('No Membership Exception', (new NoMembershipException())->getName());
    }

    public function testInsufficientDataExcName(): void
    {
        self::assertEquals('Insufficient Data Exception', (new InsufficientDataException())->getName());
    }

    public function testModelNotFoundExcName(): void
    {
        self::assertEquals('Model Not Found Exception', (new ModelNotFoundException())->getName());
    }
}
