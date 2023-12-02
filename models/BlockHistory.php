<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function get_block_history(string $type='text', string $query='', $from=null, int $until=1): array
    {
        $db = self::db();
        if($from !== null)
            $sqlstr = "id<=$from AND id>=$until";
        else
            $sqlstr = "id>=$until";

        try{
            if(!empty($query) && $type == 'author'){
                $a = $db->prepare("SELECT id,executor,display_ip,target_member,target_aclgroup,target_id,comment,`datetime`,until,`action`,granted FROM `BlockHistory` WHERE `executor`=? AND $sqlstr ORDER BY id ASC LIMIT 100");
                $a->execute([trim($query)]);
            }elseif(!empty($query) && $type == 'text'){
                $a = $db->prepare("SELECT id,executor,display_ip,target_member,target_aclgroup,target_id,comment,`datetime`,until,`action`,granted FROM `BlockHistory` WHERE (`display_ip` LIKE ? OR `comment` LIKE ? OR `target_member` LIKE ? OR `target_aclgroup` LIKE ? OR `id` LIKE ? ) AND $sqlstr ORDER BY id ASC LIMIT 100");
                $q = '%'.$query.'%';
                $a->execute([$q,$q,$q,$q,$q]);
            }else{
                $a = $db->query("SELECT id,executor,display_ip,target_member,target_aclgroup,target_id,comment,`datetime`,until,`action`,granted FROM `BlockHistory` WHERE $sqlstr ORDER BY id ASC LIMIT 100");
            }

        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 차단 기록 로드 중 오류 발생');
        }
        return $a->fetchAll();
    }

    /**
     * Get total count of block history in certain condition.
     * Used in order to get latest history ID.
     * @return array [$max, $min]
     */
    public static function get_block_history_count($type, $query): array
    {
        $db = self::db();
        try {
            if(!empty($query) && $type == 'author'){
                $a = $db->prepare("SELECT max(id) as max, min(id) as min FROM `BlockHistory` WHERE `executor`=?");
                $a->execute([trim($query)]);
            }elseif(!empty($query) && $type == 'text'){
                $a = $db->prepare("SELECT max(id) as max, min(id) as min FROM `BlockHistory` WHERE (`display_ip` LIKE ? OR `comment` LIKE ? OR `target_member` LIKE ? OR `target_aclgroup` LIKE ? OR `id` LIKE ? )");
                $q = '%'.$query.'%';
                $a->execute([$q,$q,$q,$q,$q]);
            }else{
                $a = $db->query("SELECT max(id) as max, min(id) as min FROM `BlockHistory`");
            }
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 차단 기록 인덱스 조회 중 오류 발생');
        }
        return $a->fetch();
    }
}