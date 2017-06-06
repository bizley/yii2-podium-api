<?php

namespace bizley\podium\api\tests\props;

use bizley\podium\api\dictionaries\Dictionary;

/**
 * Test Dictionary
 */
abstract class TestDictionary extends Dictionary
{
    const FIRST = 0;
    const SECOND = 1;
    const THIRD = 2;

    /**
     * @inheritdoc
     */
    public static function dictionary()
    {
        return [
            self::FIRST => 'First',
            self::SECOND => 'Second',
            self::THIRD => 'Third',
        ];
    }
}
