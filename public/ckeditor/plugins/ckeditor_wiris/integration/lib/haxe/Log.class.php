<?php

class haxe_Log
{
    public function __construct()
    {
    }

    public static function trace($v, $infos = null)
    {
        return call_user_func_array(self::$trace, [$v, $infos]);
    }

    public static $trace = null;

    public static function clear()
    {
        return call_user_func(self::$clear);
    }

    public static $clear = null;

    public function __toString()
    {
        return 'haxe.Log';
    }
}
haxe_Log::$trace = [new _hx_lambda([], 'haxe_Log_0'), 'execute'];
haxe_Log::$clear = [new _hx_lambda([], 'haxe_Log_1'), 'execute'];
function haxe_Log_0($v, $infos)
{
    _hx_trace($v, $infos);
}
function haxe_Log_1()
{
}
