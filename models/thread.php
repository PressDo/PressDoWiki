<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function get_thread_info(string $urlstr): array
    {
        $db = self::db();
        $d = $db->prepare("SELECT `namespace`,title,topic,`status` FROM `thread` WHERE `urlstr`=?");
        $d->execute([$urlstr]);
        $e = $db->prepare("SELECT contributor FROM `thread_content` WHERE `urlstr`=? AND `no`='1'");
        $e->execute([$urlstr]);
        return $d->fetch(\PDO::FETCH_ASSOC) + $e->fetch(\PDO::FETCH_ASSOC);
    }

    public static function get_comments(string $urlstr): array
    {
        $db = self::db();
        $b = $db->prepare("SELECT `no`,contributor,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 30");
        $b->execute([$urlstr]);
        $arr = array_reverse($b->fetchAll(\PDO::FETCH_ASSOC));
        return $arr;
    }
}