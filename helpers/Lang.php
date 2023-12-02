<?php
namespace PressDo;
class Lang 
{
    protected static array $items = [];

    /**
     * Initialize configs.
     */
    protected static function init()
    {
        if(empty(static::$items)) {
            static::$items = json_decode(file_get_contents('data/language/'.Config::get('language').'.json'), true);
        }
    }

    /**
     * get config value
     */
    public static function get(string $key, mixed $default = null)
    {
        static::init();
        return static::$items[$key] ?? $default;
    }

    /**
     * all config value
     */
    public static function all()
    {
        static::init();
        return static::$items;
    }
}