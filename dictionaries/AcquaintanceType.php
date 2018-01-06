<?php

namespace bizley\podium\api\dictionaries;

/**
 * Acquaintance Type Dictionary
 */
abstract class AcquaintanceType extends Dictionary
{
    const FRIEND = 1;
    const IGNORE = 2;

    /**
     * @inheritdoc
     */
    public static function dictionary()
    {
        return [
            self::FRIEND => 'Friend',
            self::IGNORE => 'Ignore',
        ];
    }
}
