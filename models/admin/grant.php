<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function grant_user(string $executor, string $username, array $perms, string $record): void
    {
        $db = self::db();
        $c = implode(',', $perms);
        $e = $db->prepare("UPDATE `member` SET `perm`=? WHERE `username`=?");
        $e->execute([$c,$username]);
        $f = $db->prepare("INSERT INTO `BlockHistory` (executor,target_member,datetime,action,granted) VALUES(?,?,?,'grant',?)");
        $f->execute([$executor, $username, $_SERVER['REQUEST_TIME'], $record]);
        unset($c,$e,$f);
    }
}