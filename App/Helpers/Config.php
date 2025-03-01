<?php
namespace PressDo\App\Helpers;

class Config 
{
    private static array $Configs = [];
    
    private static $db = null;

    /**
     * Connect Database.
     * @return \PDO
     */
    protected static function db(): \PDO
    {
        if(!self::$db){
            self::$db = Database::getInstance();
            return self::$db;
        }else
            return self::$db;
    }

    /**
     * Initialize configs.
     */
    private static function init(): void
    {
        if (empty(static::$Configs)) {
            $instance = self::db();
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
    public static function get(string $key, string $implodeDelimiter=''): mixed
    {
        self::init();
        $res = static::$Configs[$key];
        if (!empty($implodeDelimiter) && is_countable($key))
            $res = implode($implodeDelimiter, $res);
        return $res;
    }

    /**
     * get All array
     */
    public static function all(): array
    {
        self::init();
        return static::$Configs;
    }
    
    public static function getPreference(): array
    {
        $instance = self::db();
        $d = $instance->query("SELECT `key`, `value` FROM config");
        return $d->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);
    }
    
    public static function set(string $key, string $name): void
    {
        $db = self::db();
        $d = $db->prepare("INSERT INTO config (`key`, `value`) VALUES (?, ?)");
        $d->execute([$key, $name]);
    }
    
    public static function delete(string $key, string $name): void
    {
        $db = self::db();
        $d = $db->prepare("DELETE FROM config WHERE `key`=? AND `value`=?");
        $d->execute([$key, $name]);
    }
    
    public static function setBulk(array $data)
    {
        $db = self::db();
        $str = implode(', ', array_fill(0, count($data) / 2, '(?, ?)'));
        $db->query("DELETE FROM config");
        $d = $db->prepare("INSERT INTO config (`key`, `value`) VALUES ".$str);
        $d->execute($data);
    }
}