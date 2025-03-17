<?php
namespace PressDo\App\Services\Mark;

use PressDo\App\Helpers\DefaultConfig;

class MarkHandler
{
    public static function load(string $content, array $options)
    {
        $markClassName = 'PressDo\App\Services\Mark\\'.DefaultConfig::get('wiki.mark').'\Loader';
        return $markClassName::loadMarkUp($content, $options);
    }
}