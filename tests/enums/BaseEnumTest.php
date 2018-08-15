<?php

declare(strict_types=1);

namespace bizley\podium\tests\enums;

use bizley\podium\api\enums\BaseEnum;
use bizley\podium\tests\TestCase;

/**
 * Class BaseEnumTest
 * @package bizley\podium\tests\enums
 */
class BaseEnumTest extends TestCase
{
    private $_enum;

    public function getEnum()
    {
        if ($this->_enum === null) {
            $this->_enum = new class extends BaseEnum {
                public const ONE = 'one';
                public const TWO = 'two';
                public const THREE = 'three';

                public static function data(): array
                {
                    return [
                        self::ONE => 'one.desc',
                        self::TWO => 'two.desc',
                        self::THREE => 'three.desc',
                    ];
                }
            };
        }
        return $this->_enum;
    }

    /**
     * @covers \bizley\podium\api\enums\BaseEnum::data
     */
    public function testData(): void
    {
        $this->assertEquals([
            $this->getEnum()::ONE => 'one.desc',
            $this->getEnum()::TWO => 'two.desc',
            $this->getEnum()::THREE => 'three.desc',
        ], $this->getEnum()::data());
    }

    public function testValues(): void
    {
        $this->assertEquals(['one.desc', 'two.desc', 'three.desc'], $this->getEnum()::values());
    }

    public function testKeys(): void
    {
        $this->assertEquals([$this->getEnum()::ONE, $this->getEnum()::TWO, $this->getEnum()::THREE], $this->getEnum()::keys());
    }

    public function testGet(): void
    {
        $this->assertEquals('two.desc', $this->getEnum()::get($this->getEnum()::TWO));
        $this->assertEmpty($this->getEnum()::get('nonexisting'));
        $this->assertEquals('default', $this->getEnum()::get('nonexisting', 'default'));
    }

    public function testExists(): void
    {
        $this->assertTrue($this->getEnum()::exists($this->getEnum()::THREE));
        $this->assertFalse($this->getEnum()::exists('nonexisting'));
    }
}
