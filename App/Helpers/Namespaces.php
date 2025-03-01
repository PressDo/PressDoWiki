<?php
namespace PressDo\App\Helpers;

class Namespaces
{
    public static string $DOCUMENT;

    public static string $FILE;

    public static string $USER;

    public static string $CATEGORY;

    public static string $TEMPLATE;

    protected static array $Namespaces = [];

    /**
     * Initialize namespaces.
     */
    protected static function init(): void
    {
        if(empty(self::$Namespaces)) {
            $file = '../config/language/'.DefaultConfig::get('wiki.language').'/namespace.json';

            if (!file_exists($file))
                $file = '../config/language/ko-kr/namespace.php';

            $included = include $file;
            if (!is_countable($included) || count($included) < 5)
                die('Namespace file is corrupted. namespace.php file must have at least 5 namespaces.');

            self::$DOCUMENT = $included[0];
            self::$FILE = $included[1];
            self::$USER = $included[2];
            self::$CATEGORY = $included[3];
            self::$TEMPLATE = $included[4];

            self::$Namespaces = $included;
        }
    }

    public static function document(): string
    {
        self::init();
        return self::$DOCUMENT;
    }

    public static function file(): string
    {
        self::init();
        return self::$FILE;
    }

    public static function user(): string
    {
        self::init();
        return self::$USER;
    }

    public static function category(): string
    {
        self::init();
        return self::$CATEGORY;
    }

    public static function template(): string
    {
        self::init();
        return self::$TEMPLATE;
    }

    /**
     * all namespace list
     */
    public static function all(): array
    {
        self::init();
        return self::$Namespaces;
    }

    public static function validate($namespace): bool
    {
        self::init();
        return in_array($namespace, self::$Namespaces);
    }
}