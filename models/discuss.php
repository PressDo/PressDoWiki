<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function getLatestComments($urlstr)
    {
        $db = self::db();
        $b = $db->prepare("SELECT `no`,contributor,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 3");
        $b->execute([$urlstr]);
        $arr = array_reverse($b->fetchAll(\PDO::FETCH_ASSOC));
        if($arr[0]['no'] !== '1' && $b->rowCount() == '3'){
            $c = $db->prepare("SELECT `no`,contributor,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? AND `no`='1'");
            $c->execute([$urlstr]);
            $arr = array_merge([$c->fetch(\PDO::FETCH_ASSOC)],$arr);
        }
        return $arr;
    }
}