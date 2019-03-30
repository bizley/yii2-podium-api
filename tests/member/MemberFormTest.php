<?php

declare(strict_types=1);

namespace bizley\podium\tests\member;

use bizley\podium\api\base\InsufficientDataException;
use bizley\podium\api\base\ModelNotFoundException;
use bizley\podium\api\enums\MemberStatus;
use bizley\podium\api\models\member\MemberForm;
use bizley\podium\api\repos\MemberRepo;
use bizley\podium\tests\DbTestCase;
use yii\base\Event;
use yii\base\NotSupportedException;
use function time;
use yii\helpers\ArrayHelper;

/**
 * Class MemberFormTest
 * @package bizley\podium\tests\member
 */
class MemberFormTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_member' => [
            [
                'id' => 1,
                'user_id' => '1',
                'username' => 'member',
                'slug' => 'member',
                'status_id' => MemberStatus::ACTIVE,
                'created_at' => 1,
                'updated_at' => 1,
            ],
        ],
    ];

    /**
     * @var array
     */
    protected $eventsRaised = [];

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdate(): void
    {
        Event::on(MemberForm::class, MemberForm::EVENT_BEFORE_EDITING, function () {
            $this->eventsRaised[MemberForm::EVENT_BEFORE_EDITING] = true;
        });
        Event::on(MemberForm::class, MemberForm::EVENT_AFTER_EDITING, function () {
            $this->eventsRaised[MemberForm::EVENT_AFTER_EDITING] = true;
        });

        $response = $this->podium()->member->edit([
            'id' => 1,
            'username' => 'username-updated',
        ]);
        $time = time();

        $this->assertTrue($response->result);

        $responseData = $response->data;
        $updatedAt = ArrayHelper::remove($responseData, 'updated_at');

        $this->assertLessThanOrEqual($time, $updatedAt);

        $this->assertEquals([
            'id' => 1,
            'user_id' => '1',
            'username' => 'username-updated',
            'slug' => 'member',
            'status_id' => MemberStatus::ACTIVE,
            'created_at' => 1,
        ], $responseData);

        $member = MemberRepo::findOne(['username' => 'username-updated']);
        $this->assertNotEmpty($member);
        $this->assertEquals('member', $member->slug);
        $this->assertEmpty(MemberRepo::findOne(['username' => 'member']));

        $this->assertArrayHasKey(MemberForm::EVENT_BEFORE_EDITING, $this->eventsRaised);
        $this->assertArrayHasKey(MemberForm::EVENT_AFTER_EDITING, $this->eventsRaised);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateEventPreventing(): void
    {
        $handler = static function ($event) {
            $event->canEdit = false;
        };
        Event::on(MemberForm::class, MemberForm::EVENT_BEFORE_EDITING, $handler);

        $this->assertFalse($this->podium()->member->edit([
            'id' => 1,
            'username' => 'username-updated',
        ])->result);

        $this->assertNotEmpty(MemberRepo::findOne(['username' => 'member']));
        $this->assertEmpty(MemberRepo::findOne(['username' => 'username-updated']));

        Event::off(MemberForm::class, MemberForm::EVENT_BEFORE_EDITING, $handler);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateLoadFalse(): void
    {
        $this->assertFalse($this->podium()->member->edit(['id' => 1])->result);
    }

    public function testFailedEdit(): void
    {
        $mock = $this->getMockBuilder(MemberForm::class)->setMethods(['save'])->getMock();
        $mock->method('save')->willReturn(false);

        $this->assertFalse($mock->edit()->result);
    }

    /**
     * @throws NotSupportedException
     */
    public function testCreate(): void
    {
        $this->expectException(NotSupportedException::class);
        (new MemberForm())->create();
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateNoId(): void
    {
        $this->expectException(InsufficientDataException::class);
        $this->podium()->member->edit([]);
    }

    /**
     * @throws InsufficientDataException
     * @throws ModelNotFoundException
     */
    public function testUpdateWrongId(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->podium()->member->edit(['id' => 10000]);
    }

    /**
     * @runInSeparateProcess
     * Keep last in class
     */
    public function testAttributeLabels(): void
    {
        $this->assertEquals([
            'username' => 'member.username',
            'slug' => 'member.slug',
        ], (new MemberForm())->attributeLabels());
    }
}
