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
     * all namespace list
     */
    public static function all()
    {
        static::init();
        return static::$items;
    }
}