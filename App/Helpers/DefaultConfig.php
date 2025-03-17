<?php
namespace PressDo\App\Helpers;

class DefaultConfig 
{
    private static array $DefConfig = [];

    /**
     * Initialize configs.
     */
    private static function init()
    {
        if (empty(static::$DefConfig)) {
            static::$DefConfig = json_decode(file_get_contents('../config/config.json'), true);
        }
    }

    /**
     * get config value
     */
    public static function get(string $key)
    {
        self::init();
        $res = static::$DefConfig[$key];

        if (is_array($res) && count($res) == 1)
            $res = $res[0];
        return $res;
    }

    public static function update(\PDO $instance): void
    {
        $d = $instance->query("SELECT `key`, `value` FROM config");
        var_dump($d);
        static::$DefConfig = array_merge(static::$DefConfig, $d->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN));
    }

    /**
     * get All array
     */
    public static function all()
    {
        self::init();
        return static::$DefConfig;
    }
}