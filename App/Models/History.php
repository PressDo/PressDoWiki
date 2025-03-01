<?php
namespace PressDo\App\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\App\Helpers\Namespaces;

class History extends \PressDo\App\Core\Model
{
    /**
     * Get histories of document
     * @param string $uuid
     * @param ?int $from
     * @param ?int $until
     * @throws ErrorException
     * @return array
     */
    public static function load(string $uuid, ?int $from=null, ?int $until=null, int $count=31): array
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

            $d = $db->prepare("SELECT uuid, `comment`, `action`, `reverted_version`, contributor_m, contributor_i, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri`, `count`, `rev` FROM `history` WHERE `document`=? ORDER BY `datetime` $str $count");
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
    public static function recentChanges(string $logtype='all')
    {
        $db = self::db();

        $lt = [
            'create' => "AND h.`action`='create'",
            'revert' => "AND h.`action`='revert'",
            'move' => "AND h.`action`='move'",
            'delete' => "AND h.`action`='delete'",
            'all' => "AND document.namespace != '".Namespaces::user().'\'',
            'recent' => "AND document.namespace = '".Namespaces::document().'\''
        ];

        try {
            $d = $db->query("SELECT h.`uuid`,h.`action`,h.`comment`,h.`reverted_version`,h.`count`,h.`contributor_m`, h.`contributor_i`,h.`document`, h.`acl_changed`, h.`moved_from`, h.`moved_to`, h.`datetime`, h.rev FROM `history` as h, document 
            WHERE h.document = document.uuid $lt[$logtype] ORDER BY `datetime` DESC LIMIT 100");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 변경 가져오는 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRecentSidebar(): array
    {
        $db = self::db();
        $sql = "SELECT h.`uuid`,h.`action`,h.`comment`,h.`reverted_version`,h.`count`,h.`contributor_m`, h.`contributor_i`,h.`document`, h.`acl_changed`, h.`moved_from`, h.`moved_to`, h.`datetime`, h.rev FROM
            (SELECT `uuid`,`action`,`comment`,`reverted_version`,`count`,`contributor_m`, `contributor_i`,`document`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, rev,
            RANK() OVER (PARTITION BY document ORDER BY `datetime` DESC) AS rnk FROM `history`) as h
            INNER JOIN document ON h.document = document.uuid WHERE rnk=1 AND `namespace`= '".Namespaces::document()."' ORDER BY `datetime` DESC LIMIT 15";

        try {
            $d = $db->query($sql);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 변경 (사이드바) 가져오는 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the uuid of right before of given revision.
     * 
     * @param string $namespace
     * @param string $title
     * @return int
     */
    public static function getPrevUuid(string $document, int $rev): string
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

    public static function countContributionDocument(string $uuid): string
    {
        $db = self::db();
        $id = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("SELECT count(*) as cnt FROM `history` WHERE contributor_m = :id OR contributor_i = :id");
            $d->execute(['id' => $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 기여내역 세는 중 오류 발생');
        }
        return $d->fetch(PDO::FETCH_ASSOC)['cnt'];
    }
    
    public static function getContributionDocument(string $uuid, int $count, $from=null, $until=null)
    {
        $db = self::db();
        $id = self::uuid2bin($uuid);
        if (!empty($from))
            $limit = ($count - $from).', 100';
        elseif (!empty($until))
            $limit = ($count - $from - 100 < 1 ? 1 : $count - $from - 100).','.($count - $from);
        else
            $limit = '100';


        try {
            $d = $db->prepare("SELECT `namespace`, `title`, h.`uuid`,h.`action`,h.`comment`,h.`reverted_version`,h.`count`,h.`document`, h.`acl_changed`, h.`moved_from`, h.`moved_to`, h.`datetime`, h.rev FROM `history` as h, document 
                WHERE h.document = document.uuid AND (contributor_m = :id OR contributor_i = :id) ORDER BY `datetime` DESC LIMIT $limit");
            $d->execute(['id' => $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 기여내역 가져오는 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRecentContributionDiscuss(string $uuid)
    {
        $db = self::db();
        $id = self::uuid2bin($uuid);

        try {
            $d = $db->prepare("SELECT `namespace`, title, t.urlstr, topic, `datetime`, `no` FROM thread_content t INNER JOIN `thread` x ON t.urlstr = x.urlstr INNER JOIN `document` AS d ON x.document = d.uuid
                WHERE (contributor_m = :id OR contributor_i = :id) AND `datetime` >= unix_timestamp() - 2592000 ORDER BY `datetime` DESC LIMIT 100");
            $d->execute(['id' => $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 토론 기여내역 가져오는 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getContributionEditrequest(string $uuid)
    {
        $sql = "SELECT urlstr, document, contributor_m, contributor_i, `datetime` FROM editrequest ORDER BY `datetime` DESC LIMIT 100";
        
    }
}