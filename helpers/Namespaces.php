<?php
namespace PressDo;
class Namespaces
{
    protected static array $items = [];

    /**
     * Initialize namespaces.
     */
    protected static function init()
    {
        if(empty(static::$items)) {
            static::$items = json_decode(file_get_contents('data/global/namespace.json'), true);
        }
    }

    /**
     * get namespace text
     */
    public static function get(string $key, mixed $default = null)
    {
        static::init();
        return static::$items[$key] ?? $default;
    }

    /**
     * get namespace text
     */
    public static function getRaw(string $key, mixed $default = null)
    {
        static::init();
        return array_search($key, static::$items) ?? $default;
    }

    /**
     * all namespace list
     */
    public static function all()
    {
        static::init();
        return static::$items;
    }
}