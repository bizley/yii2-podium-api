<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

use function array_key_exists;
use function array_keys;
use function array_values;

abstract class BaseEnum
{
    /**
     * Source of ENUM.
     * Returns array with keys being the ENUMs and values being the ENUMs description.
     *
     * @codeCoverageIgnore
     */
    public static function data(): array
    {
        return [];
    }

    /**
     * Returns ENUMs description.
     */
    public static function values(): array
    {
        return array_values(static::data());
    }

    /**
     * Returns ENUMs.
     */
    public static function keys(): array
    {
        return array_keys(static::data());
    }

    /**
     * Returns ENUM description.
     *
     * @param string $enum    ENUM value
     * @param mixed  $default what to return in case ENUM has not been found
     *
     * @return mixed
     */
    public static function get(string $enum, $default = null)
    {
        return static::exists($enum) ? static::data()[$enum] : $default;
    }

    /**
     * Checks if ENUM is defined.
     *
     * @param string $enum ENUM value
     */
    public static function exists(string $enum): bool
    {
        return array_key_exists($enum, static::data());
    }
}
