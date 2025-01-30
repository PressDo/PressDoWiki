<?php
namespace PressDo\app\Helpers;

class Namespaces
{
    public const DOCUMENT = '문서';

    public const FILE = '파일';

    public const USER = '사용자';

    protected static array $Namespaces = [];

    /**
     * Initialize namespaces.
     */
    protected static function init(): void
    {
        if(empty(static::$Namespaces)) {
            static::$Namespaces = json_decode(file_get_contents('../config/namespace.json'), true);
        }
    }

    /**
     * all namespace list
     */
    public static function all(): array
    {
        self::init();
        return static::$Namespaces;
    }
}