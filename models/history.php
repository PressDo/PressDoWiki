<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * get history data
     * 
     * @param int $uuid        Document ID
     * @param string $username  username
     * @return bool
     */
    public static function loadHistory(string $uuid, int|null $from=null, int|null $until=null) : array
    {
        $db = self::db();
        try {
            $nv = Models::get_version($uuid);
            $uuid = self::uuid2bin($uuid);
            
            if(!empty($from))
                $str = 'DESC LIMIT '.$nv-$from.',';
            elseif(!empty($until))
                $str = 'ASC LIMIT '.$until-1 .',';
            else
                $str = 'DESC LIMIT';
            $d = $db->prepare("SELECT uuid, `comment`, `action`, `reverted_version`, contributor_m, contributor_i, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri`, `count`, `rev` FROM `history` WHERE `document`=? AND `is_hidden`='false' ORDER BY `datetime` $str 31");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 역사 조회 중 오류 발생');
        }

        if(!empty($until))
            $ra = array_reverse($d->fetchAll(\PDO::FETCH_ASSOC));
        else
            $ra = $d->fetchAll(\PDO::FETCH_ASSOC);

        return $ra;
    }
    
    public static function get_rev_time($namespace,$title, $rev)
    {
        $db = self::db();
        try {
            $id = Models::get_doc_uuid($namespace, $title);
            $d = $db->prepare("SELECT `datetime` FROM `history` WHERE `uuid`=? AND `rev`=?");
            $d->execute([$id,$rev]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 리비전 시각 조회 중 오류 발생');
        }

        return $d->fetch(\PDO::FETCH_ASSOC)['datetime'];
    }
}