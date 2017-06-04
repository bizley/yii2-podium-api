<?php

namespace bizley\podium\api\dictionaries;

/**
 * General Dictionary
 * Provides method to get single value based on a key.
 */
abstract class Dictionary
{
    /**
     * Returns dictionary.
     * Child class should override this method.
     * @return array
     */
    public static function dictionary()
    {
        return [];
    }

    /**
     * Returns dictionary value based on the given key or null if key has not been found.
     * @param string $key
     * @return string|null
     */
    public static function get($key)
    {
        $dictionary = static::dictionary();

        if (isset($dictionary[$key])) {
            return $dictionary[$key];
        }

        return null;
    }
}
