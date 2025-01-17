<?php
namespace PressDo;
class Lang 
{
    protected static array $items = [];

    /**
     * Initialize languages.
     */
    protected static function init()
    {
        if(empty(static::$items)) {
            static::$items = json_decode(file_get_contents('data/language/'.Config::get('language').'.json'), true);
        }
    }

    /**
     * get specific language string
     */
    public static function get(string $key, mixed $default = null)
    {
        static::init();
        return static::$items[$key] ?? $default;
    }

    /**
     * get all language string
     */
    public static function all()
    {
        static::init();
        return static::$items;
    }
}