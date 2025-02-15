<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class History extends \PressDo\app\Core\Model
{
    /**
     * Get histories of document
     * @param string $uuid
     * @param ?int $from
     * @param ?int $until
     * @throws ErrorException
     * @return array
     */
    public static function load(string $uuid, ?int $from=null, ?int $until=null): array
    {
        $db = self::db();
        try {
            $nv = self::getVersion($uuid);
            $uuid = self::uuid2bin($uuid);
            
            if (!empty($from))
                $str = 'DESC LIMIT '.$nv-$from.',';
            elseif (!empty($until))
                $str = 'ASC LIMIT '.$until-1 .',';
            else
                $str = 'DESC LIMIT';

            $d = $db->prepare("SELECT uuid, `comment`, `action`, `reverted_version`, contributor_m, contributor_i, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri`, `count`, `rev` FROM `history` WHERE `document`=? AND `is_hidden`='false' ORDER BY `datetime` $str 31");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 역사 조회 중 오류 발생');
        }

        $ra = !empty($until) ? array_reverse($d->fetchAll(PDO::FETCH_ASSOC)) : $ra = $d->fetchAll(PDO::FETCH_ASSOC);

        return $ra;
    }

    /**
     * fetch recent 100 edit histories
     * @param string $logtype  type of edit
     * @return 
     */
    public static function recentChanges(string $logtype='all', bool $sidebar=false)
    {
        $db = self::db();

        $lt = [
            'create' => "AND h.`action`='create'",
            'revert' => "AND h.`action`='revert'",
            'move' => "AND h.`action`='move'",
            'delete' => "AND h.`action`='delete'",
            'all' => "AND document.namespace != '사용자'"
        ];

        if($sidebar)
            $quota = '15';
        else
            $quota = '100';

        try {
            $d = $db->query("SELECT h.`uuid`,h.`action`,h.`comment`,h.`reverted_version`,h.`count`,h.`contributor_m`, h.`contributor_i`,h.`document`, h.`acl_changed`, h.`moved_from`, h.`moved_to`, h.`datetime`, h.rev FROM `history` as h, document 
            WHERE BINARY h.`is_hidden`='false' AND h.document = document.uuid $lt[$logtype] ORDER BY `datetime` DESC LIMIT ".$quota);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 변경 가져오는 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function hideHistory($uuid)
    {
        $db = self::db();
        $d = $db->prepare("UPDATE `history` SET `is_hidden`='true' WHERE `uuid`=?");
        $d->execute([$uuid]);
    }
    
    public static function unhideHistory($uuid)
    {
        $db = self::db();
        $d = $db->prepare("UPDATE `history` SET `is_hidden`='false' WHERE `uuid`=?");
        $d->execute([$uuid]);
    }

    /**
     * Get the uuid of right before of given revision.
     * 
     * @param string $namespace
     * @param string $title
     * @return int
     */
    public static function getPrevUuid($document, int $rev): string
    {
        $db = self::db();
        $id = self::uuid2bin($document);
        try {
            $d = $db->prepare("SELECT `uuid` FROM `history` WHERE `document`=? AND `rev`=?");
            $d->execute([$id, $rev-1]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 버전 조회 중 오류 발생');
        }
        return self::bin2uuid($d->fetch(PDO::FETCH_ASSOC)['uuid']);
    }
}