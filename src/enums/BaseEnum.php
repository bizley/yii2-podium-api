<?php

declare(strict_types=1);

namespace bizley\podium\api\enums;

/**
 * Class BaseEnum
 * @package bizley\podium\api\enums
 */
abstract class BaseEnum
{
    /**
     * Source of ENUM.
     * Returns array with keys being the ENUMs and values being the ENUMs description.
     * @return array
     * @codeCoverageIgnore
     */
    public static function data(): array
    {
        return [];
    }

    /**
     * Returns ENUMs description.
     * @return array
     */
    public static function values(): array
    {
        return array_values(static::data());
    }

    /**
     * Returns ENUMs.
     * @return array
     */
    public static function keys(): array
    {
        return array_keys(static::data());
    }

    /**
     * Returns ENUM description.
     * @param string $enum ENUM value
     * @param mixed $default what to return in case ENUM has not been found
     * @return mixed
     */
    public static function get(string $enum, $default = null)
    {
        return static::exists($enum) ? static::data()[$enum] : $default;
    }

    /**
     * Checks if ENUM is defined.
     * @param string $enum ENUM value
     * @return bool
     */
    public static function exists(string $enum): bool
    {
        return array_key_exists($enum, static::data());
    }
}
