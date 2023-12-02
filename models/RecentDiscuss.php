<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function RecentDiscuss($from='thread', $status='normal', $order='DESC'): array
    {
        $db = self::db();

        try {
            $a = $db->query("SELECT * FROM `$from` WHERE `status`='".$status."' ORDER BY `last_comment` ".$order." LIMIT 100");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 토론 조회 중 오류 발생');
        }
        return $a->fetchAll(\PDO::FETCH_ASSOC);
    }
}