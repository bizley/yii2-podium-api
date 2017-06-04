<?php

namespace bizley\podium\api\dictionaries;

/**
 * Status Dictionary
 */
abstract class Status extends Dictionary
{
    const REGISTERED = 0;
    const ACTIVE = 1;
    const BANNED = 2;

    /**
     * @inheritdoc
     */
    public static function dictionary()
    {
        return [
            self::REGISTERED => 'Registered',
            self::ACTIVE => 'Active',
            self::BANNED => 'Banned',
        ];
    }
}
