<?php

namespace bizley\podium\api\dictionaries;

/**
 * Permission Dictionary
 */
abstract class Permission extends Dictionary
{
    const MEMBER_ACQUAINTANCE = 1;

    /**
     * @inheritdoc
     */
    public static function dictionary()
    {
        return [
            self::MEMBER_ACQUAINTANCE => 'Member Acquaintance',
        ];
    }
}
