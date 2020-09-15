<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit;

use bizley\podium\api\components\Account;
use bizley\podium\api\components\Category;
use bizley\podium\api\components\Forum;
use bizley\podium\api\components\Group;
use bizley\podium\api\components\Member;
use bizley\podium\api\components\Message;
use bizley\podium\api\components\Poll;
use bizley\podium\api\components\Post;
use bizley\podium\api\components\Rank;
use bizley\podium\api\components\Thread;
use bizley\podium\api\Podium;
use bizley\podium\tests\AppTestCase;
use Yii;

class PodiumTest extends AppTestCase
{
    private Podium $podium;

    protected function setUp(): void
    {
        $this->podium = new Podium();
    }

    public function testCoreComponents(): void
    {
        self::assertSame(
            [
                'account' => [
                    'class' => Account::class,
                    'podiumBridge' => true,
                ],
                'category' => ['class' => Category::class],
                'forum' => ['class' => Forum::class],
                'group' => ['class' => Group::class],
                'member' => ['class' => Member::class],
                'message' => ['class' => Message::class],
                'poll' => ['class' => Poll::class],
                'post' => ['class' => Post::class],
                'rank' => ['class' => Rank::class],
                'thread' => ['class' => Thread::class],
            ],
            $this->podium->coreComponents()
        );
    }

    public function testI18nInit(): void
    {
        $translations = Yii::$app->getI18n()->translations['podium.*'];

        self::assertSame('yii\i18n\PhpMessageSource', $translations['class']);
        self::assertSame('en', $translations['sourceLanguage']);
        self::assertTrue($translations['forceTranslation']);
        self::assertStringEndsWith('/src/messages', $translations['basePath']);
    }

    public function testCompleteComponent(): void
    {
        self::assertInstanceOf(Podium::class, $this->podium->getAccount()->getPodium());
    }

    public function testSettingCore(): void
    {
        $components = $this->podium->components;

        self::assertArrayHasKey('account', $components);
        self::assertArrayHasKey('category', $components);
        self::assertArrayHasKey('forum', $components);
        self::assertArrayHasKey('group', $components);
        self::assertArrayHasKey('member', $components);
        self::assertArrayHasKey('message', $components);
        self::assertArrayHasKey('poll', $components);
        self::assertArrayHasKey('post', $components);
        self::assertArrayHasKey('rank', $components);
        self::assertArrayHasKey('thread', $components);
    }

    public function testGetAccount(): void
    {
        self::assertInstanceOf(Account::class, $this->podium->getAccount());
    }

    public function testGetCategory(): void
    {
        self::assertInstanceOf(Category::class, $this->podium->getCategory());
    }

    public function testGetForum(): void
    {
        self::assertInstanceOf(Forum::class, $this->podium->getForum());
    }

    public function testGetGroup(): void
    {
        self::assertInstanceOf(Group::class, $this->podium->getGroup());
    }

    public function testGetMember(): void
    {
        self::assertInstanceOf(Member::class, $this->podium->getMember());
    }

    public function testGetMessage(): void
    {
        self::assertInstanceOf(Message::class, $this->podium->getMessage());
    }

    public function testGetPoll(): void
    {
        self::assertInstanceOf(Poll::class, $this->podium->getPoll());
    }

    public function testGetPost(): void
    {
        self::assertInstanceOf(Post::class, $this->podium->getPost());
    }

    public function testGetRank(): void
    {
        self::assertInstanceOf(Rank::class, $this->podium->getRank());
    }

    public function testGetThread(): void
    {
        self::assertInstanceOf(Thread::class, $this->podium->getThread());
    }
}
