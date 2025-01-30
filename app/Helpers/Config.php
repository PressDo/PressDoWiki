<?php
namespace PressDo\app\Helpers;

class Config 
{
    private static array $Configs = [];

    /**
     * Initialize configs.
     */
    private static function init()
    {
        if(empty(static::$Configs)) {
            static::$Configs = json_decode(file_get_contents('../config/config.json'), true);
            //static::$configs['mark_config'] = json_decode(file_get_contents('mark/'.static::$configs['mark'].'/config.json'), true);
        }
    }

    /**
     * get config value
     */
    public static function get(string ...$keys)
    {
        self::init();
        $res = static::$Configs;
        foreach ($keys as $k) {
            $res = $res[$k] ?? null;
        }
        return $res;
    }

    /**
     * get All array
     */
    public static function all()
    {
        self::init();
        return static::$Configs;
    }
}