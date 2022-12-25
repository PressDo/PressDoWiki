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
     * @param int $docid        Document ID
     * @param string $username  username
     * @return bool
     */
    public static function loadHistory(string $rawns, string $title=null, int|null $from=null, int|null $until=null) : array
    {
        $db = self::db();
        try {
            $id = Models::get_doc_id($rawns, $title);
            $nv = Models::get_version($rawns, $title);
            
            if(!empty($from))
                $str = 'DESC LIMIT '.$nv-$from.',';
            elseif(!empty($until))
                $str = 'ASC LIMIT '.$until-1 .',';
            else
                $str = 'DESC LIMIT';

            $d = $db->prepare("SELECT `comment`, `action`, `reverted_version`, `contributor`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri`, `count`, `rev` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` $str 31");
            $d->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 역사 조회 중 오류 발생');
        }

        if(!empty($until))
            $ra = array_reverse($d->fetchAll(\PDO::FETCH_ASSOC));
        else
            $ra = $d->fetchAll(\PDO::FETCH_ASSOC);

        return $ra;
    }
    
    public static function get_rev_time($rawns,$title, $rev)
    {
        $db = self::db();
        try {
            $id = Models::get_doc_id($rawns, $title);
            $d = $db->prepare("SELECT `datetime` FROM `document` WHERE `docid`=? AND `rev`=?");
            $d->execute([$id,$rev]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 리비전 시각 조회 중 오류 발생');
        }

        return $d->fetch(\PDO::FETCH_ASSOC)['datetime'];
    }
}