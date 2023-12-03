<?php
namespace PressDo;
class Config 
{
    protected static array $configs = [];

    /**
     * Initialize configs.
     */
    protected static function init()
    {
        if(empty(static::$configs)) {
            static::$configs = json_decode(file_get_contents('data/global/config.json'), true);
            static::$configs['mark_config'] = json_decode(file_get_contents('mark/'.static::$configs['mark'].'/config.json'), true);
        }
    }

    /**
     * get config value
     */
    public static function get(string $key, mixed $default = null)
    {
        static::init();
        return static::$configs[$key] ?? $default;
    }

    /**
     * get All array
     */
    public static function all()
    {
        static::init();
        return static::$configs;
    }
}