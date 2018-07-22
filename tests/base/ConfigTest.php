<?php

declare(strict_types=1);

namespace bizley\podium\tests\base;

use bizley\podium\api\base\FixedSettingException;
use bizley\podium\api\enums\Setting;
use bizley\podium\api\Podium;
use bizley\podium\tests\DbTestCase;
use yii\db\Exception;

/**
 * Class ConfigTest
 * @package bizley\podium\tests\base
 */
class ConfigTest extends DbTestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'podium_config' => [
            [
                'param' => 'testparam',
                'value' => 'testvalue'
            ]
        ],
    ];

    /**
     * @throws Exception
     */
    public static function setUpBeforeClass(): void
    {
        static::mockApplication([
            'components' => [
                'podium' => [
                    'class' => Podium::class,
                    'components' => [
                        'config' => [
                            'settings' => [
                                'name' => 'PodiumTest',
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        static::runSilentMigration('migrate/up');
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->fixturesUp();
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void
    {
        $this->fixturesDown();
    }

    public function testDefaultValues(): void
    {
        $this->assertEquals([
            Setting::NAME => 'Podium',
            Setting::MAINTENANCE_MODE => '0',
            Setting::MEMBERS_VISIBLE => '1',
            Setting::POLLS_ALLOWED => '1',
            Setting::MIN_POSTS_FOR_HOT => '20',
            Setting::MERGE_POSTS => '1',
            Setting::REGISTRATION_ALLOWED => '1',
        ], $this->podium()->config->getDefaultValues());
    }

    /**
     * @throws FixedSettingException
     */
    public function testSettingValue(): void
    {
        $this->assertTrue($this->podium()->config->setValue('param', 'value'));
    }

    public function testGettingValue(): void
    {
        $this->assertEquals('testvalue', $this->podium()->config->getValue('testparam'));
    }

    /**
     * @throws FixedSettingException
     */
    public function testSettingFixedValue(): void
    {
        $this->expectException(FixedSettingException::class);
        $this->podium()->config->setValue('name', 'not-set');
    }

    public function testGettingFixedValue(): void
    {
        $this->assertEquals('PodiumTest', $this->podium()->config->getValue('name'));
    }

    public function testGettingDefaultValue(): void
    {
        $this->assertEquals('defaultvalue', $this->podium()->config->getValue('nonexisting-param', 'defaultvalue'));
    }

    /**
     * @throws FixedSettingException
     * @throws Exception
     */
    public function testGettingCachedValue(): void
    {
        $this->podium()->config->setValue('cachedparam', 'cachedvalue');
        $this->fixturesDown();
        $this->assertEquals('cachedvalue', $this->podium()->config->getValue('cachedparam'));
    }

    /**
     * @throws FixedSettingException
     */
    public function testUpdatingValue(): void
    {
        $this->podium()->config->setValue('testparam', 'new-testvalue');
        $this->assertEquals('new-testvalue', $this->podium()->config->getValue('testparam'));
    }
}
