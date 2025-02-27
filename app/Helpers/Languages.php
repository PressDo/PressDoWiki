<?php
namespace PressDo\app\Helpers;

class Languages
{
    private static array $Languages = [];

    /**
     * Initialize languages.
     */
    private static function init()
    {
        if(empty(static::$Languages)) {
            static::$Languages = json_decode(file_get_contents('../config/language/'.DefaultConfig::get('wiki.language').'/string.json'), true);
        }
    }

    /**
     * get specific language string
     */
    public static function get(string ...$keys)
    {
        self::init();
        $res = static::$Languages;
        foreach ($keys as $k) {
            $res = $res[$k] ?? null;
        }
        return $res;
    }

    /**
     * get all language string
     */
    public static function all(): array
    {
        self::init();
        return static::$Languages;
    }
}