<?php

declare(strict_types=1);

namespace bizley\podium\tests\unit\enums;

use bizley\podium\api\enums\BaseEnum;
use PHPUnit\Framework\TestCase;

class BaseEnumTest extends TestCase
{
    private BaseEnum $enum;

    protected function setUp(): void
    {
        $this->enum = new class() extends BaseEnum {
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

    public function testData(): void
    {
        self::assertEquals(
            [
                $this->enum::ONE => 'one.desc',
                $this->enum::TWO => 'two.desc',
                $this->enum::THREE => 'three.desc',
            ],
            $this->enum::data()
        );
    }

    public function testValues(): void
    {
        self::assertEquals(['one.desc', 'two.desc', 'three.desc'], $this->enum::values());
    }

    public function testKeys(): void
    {
        self::assertEquals([$this->enum::ONE, $this->enum::TWO, $this->enum::THREE], $this->enum::keys());
    }

    public function testGet(): void
    {
        self::assertEquals('two.desc', $this->enum::get($this->enum::TWO));
        self::assertEmpty($this->enum::get('nonexisting'));
        self::assertEquals('default', $this->enum::get('nonexisting', 'default'));
    }

    public function testExists(): void
    {
        self::assertTrue($this->enum::exists($this->enum::THREE));
        self::assertFalse($this->enum::exists('nonexisting'));
    }
}
