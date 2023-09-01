<?php

namespace tcCore\Http\Helpers;

use Illuminate\Http\Request;
use tcCore\Http\Controllers\TemporaryLoginController;

class RequestCacheHelper
{
    private static $cache = [];

    public static function get($identifier, $callback, $clear = false)
    {
        // always run callback in unit tests because user id are not always pointing to the same user / user roles
        if (app()->runningUnitTests()) {
            return $callback();
        }

        if (self::notIn($identifier) || $clear) {
            self::put($identifier, $callback());
        }

        return self::retrieve($identifier);
    }

    private static function retrieve($identifier)
    {
        if (array_key_exists(static::getClass(), self::$cache) && array_key_exists(static::getMethod(), self::$cache[static::getClass()]) && array_key_exists($identifier, self::$cache[static::getClass()][static::getMethod()])) {
            return self::$cache[static::getClass()][static::getMethod()][$identifier];
        }

        return null;
    }

    private static function put($identifier, $value)
    {
        self::$cache[static::getClass()][static::getMethod()][$identifier] = $value;
    }

    private static function notIn($identifier)
    {
        return self::retrieve( $identifier) === null;
    }

    private static function getClass()
    {
        $trace = debug_backtrace();
        foreach ($trace as $item) {
            if (array_key_exists('class', $item) && $item['class'] !== self::class) {
                return $item['class'];
            }
        }
    }

    private static function getMethod()
    {
        $trace = debug_backtrace();
        foreach ($trace as $item) {
            if (array_key_exists('class', $item) && $item['class'] !== self::class) {
                return $item['function'];
            }
        }
    }
}
