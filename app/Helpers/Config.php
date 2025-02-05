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
        if (empty(static::$Configs)) {
            $instance = Database::getInstance();
            $d = $instance->query("SELECT `key`, `value` FROM config");
            $cset = array_merge(DefaultConfig::all(), $d->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN));
            $carray = [];
            foreach ($cset as $k => $c) {
                if (is_countable($c) && count($c) === 1)
                    $carray[$k] = $c[0];
                else
                    $carray[$k] = $c;
            }
            static::$Configs = $carray;
        }
    }

    /**
     * get config value
     */
    public static function get(string $key)
    {
        self::init();
        $res = static::$Configs[$key];
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