<?php
namespace PressDo\app\Helpers;

class Namespaces
{
    public const DOCUMENT = '문서';

    public const FILE = '파일';

    public const USER = '사용자';

    public const CATEGORY = '분류';

    public const TEMPLATE = '틀';

    protected static array $Namespaces = [];

    /**
     * Initialize namespaces.
     */
    protected static function init(): void
    {
        if(empty(static::$Namespaces)) {
            $file = '../config/language/'.DefaultConfig::get('wiki.language').'/namespace.json';

            if (!file_exists($file))
                $file = '../config/language/ko-kr/namespace.json';

            static::$Namespaces = array_merge(
                [
                    self::DOCUMENT,
                    self::FILE,
                    self::USER,
                    self::CATEGORY,
                    self::TEMPLATE
                ],
                json_decode(file_get_contents($file), true)
            );
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

    public static function validate($namespace): bool
    {
        self::init();
        return in_array($namespace, static::$Namespaces);
    }
}