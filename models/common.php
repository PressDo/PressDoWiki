<?php
namespace PressDo;

require 'helpers/DB.php';

use ErrorException;
use PDOException;
use \PDO as PDO;

class baseModels {
    private static $db = null;

    /**
     * Connect Database.
     * @return PDO
     */
    public static function db(): PDO
    {
        if(!self::$db){
            self::$db = \PressDo\DB::getInstance();
            return self::$db;
        }else
            return self::$db;
    }

    public static function member_exist($username): string|bool
    {
        $db = self::db();
        $d = $db->prepare("SELECT username, count(*) as cnt FROM member WHERE username=?");
        $d->execute([$username]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($data['cnt'] < 1)
            return false;
        else
            return $data['username'];
    }

    /**
     * find ip by uuid
     * @param string $ip
     * @return mixed UUID of ip
     */
    public static function get_ip_uuid($ip)
    {
        $db = self::db();
        
        $d = $db->prepare("SELECT uuid FROM ip WHERE ip=?");
        $d->execute([inet_pton($ip)]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1){
            $uuid = self::uuid_generate();
            $d = $db->prepare("INSERT INTO ip(uuid,ip) VALUES(?,?)");
            $d->execute([self::uuid2bin($uuid),inet_pton($ip)]);
            return $uuid;
        }else
            return self::bin2uuid($data['uuid']);
    }

    /**
     * find ip by uuid
     * @param string $uuid
     * @return mixed
     */
    public static function ip_lookup($uuid)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $d = $db->prepare("SELECT ip FROM ip WHERE uuid=?");
        $d->execute([$uuid]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1)
            return false;
        else
            return inet_ntop($data['ip']);
    }

    /**
     * find username by uuid
     * @param string $uuid
     * @return mixed
     */
    public static function member_lookup($uuid)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $d = $db->prepare("SELECT username FROM member WHERE uuid=?");
        $d->execute([$uuid]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1)
            return false;
        else
            return $data['username'];
    }

    /**
     * Get UUID of the document.
     *
     * @param   string $namespace     Document namespace (raw)
     * @param   string $title     Document Title
     * @return  int|bool       Document ID (false if not exist)
     */
    public static function get_doc_uuid($namespace, $title, &$backlinkrefreshed = false): string|bool
    {
        $db = self::db();

        try {
            $c = $db->prepare("SELECT uuid FROM `document` WHERE `namespace`=? AND BINARY `title`=?");
            $c->execute([$namespace, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 UUID 조회 중 오류 발생');
        }

        $f = $c->fetch(PDO::FETCH_ASSOC);
        if($c->rowCount() < 1)
            return false;
        else{
            $backlinkrefreshed = boolval($f['backlink_updated']);
            return self::bin2uuid($f['uuid']);
        }
    }

    /**
     * Get title of the document with ID.
     *
     * @param   array $ids     Document UUID (least 1 doc)
     * @return  array       Document namespace, title
     */
    public static function get_bulk_doc_title(array $ids): array
    {
        $db = self::db();
        $params = [];
        foreach($ids as $i)
            array_push($params, self::uuid2bin($i));
        

        try {
            $ORSTATEMENT = str_repeat(',?', count($ids) - 1);
            $c = $db->prepare("SELECT `docid`,`namespace`,`title` FROM `document` WHERE `docid` IN (?".$ORSTATEMENT.")");
            $c->execute($params);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ID로 문서명 조회 중 오류 발생');
        }

        $res = $c->fetchAll(PDO::FETCH_ASSOC);
        
        return $res;
    }

    /**
     * Get title of the document with UUID.
     *
     * @param   string $uuid     Document UUID
     * @return  array       Document namespace, title
     */
    public static function get_doc_title(string $uuid): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);

        try {
            $c = $db->prepare("SELECT `namespace`,`title` FROM `document` WHERE `uuid`=?");
            $c->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ID로 문서명 조회 중 오류 발생');
        }

        if($c->rowCount() < 1)
            $res = false;
        else
            $res = $c->fetch(PDO::FETCH_ASSOC);
        
        return $res;
    }

    /**
     * Load document data
     * 
     * @param string $uuid     uuid of document
     * @param int|null $rev     revision uuid of document
     * @return null|array       array(Namespace, Title)
     */
    public static function load(string $uuid, string|null $rev=null): null | array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $sql = "SELECT h.uuid,h.content,h.length,h.comment,h.datetime,h.action,h.rev,h.count,h.reverted_version,h.contributor_m,h.contributor_i, h.edit_request_uri,h.acl_changed,h.moved_from,h.moved_to,h.is_hidden
        FROM `history` as h INNER JOIN `document` as d ON d.uuid = h.document WHERE h.`document`=?";
        if($rev === null){
            $sql .= " AND h.`is_hidden`='false' ORDER BY h.`datetime` DESC LIMIT 1";
            $param = [$uuid];
        } else {
            $rev = self::uuid2bin($rev);
            $sql .= " AND h.uuid=? ";
            $param = [$uuid, $rev];
        }
        //$sql .= " AND `is_hidden`='false' ORDER BY h.`datetime`";
        $d = $db->prepare($sql);

        try {
            $d->execute($param);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 데이터 조회 중 오류 발생');
        }

        # return null if not found
        return ($d->rowCount() < 1)? null : $d->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * get list of threads in the document
     * @param string $uuid     uuid of document
     * @return array            
     */
    public static function get_doc_thread(string $uuid, $mode='normal'): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            if($mode === 'normal'){
                $d = $db->prepare("SELECT urlstr,topic FROM `thread` WHERE `document`=? AND (`status`='normal' OR `status`='pause')");
            }elseif($mode === 'closed'){
                $d = $db->prepare("SELECT urlstr,topic FROM `thread` WHERE `document`=? AND `status`='close'");
            }
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 토론 목록 조회 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get the number of latest revision.
     * 
     * @param string $namespace
     * @param string $title
     * @return int
     */
    public static function get_version(string $uuid): int
    {
        $db = self::db();
        $id = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("SELECT `rev` FROM `history` WHERE `document`=? ORDER BY `rev` DESC LIMIT 1");
            $d->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 버전 조회 중 오류 발생');
        }
        return intval($d->fetch(PDO::FETCH_ASSOC)['rev']);
    }

    /**
     * Get the uuid of right before of given revision.
     * 
     * @param string $namespace
     * @param string $title
     * @return int
     */
    public static function get_before_uuid($document, int $rev): string
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
    
    /**
     * check perms grantable with 'grant'
     */
    public static function special_perms(string $uuid): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("SELECT `perm` FROM `member` WHERE `uuid`=?");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 특별권한 조회 중 오류 발생');
        }
        return explode(',', $d->fetch(PDO::FETCH_ASSOC)['perm']);
    }

    public static function uuid_generate(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public static function bin2uuid(string $uuid): string
    {
        $uuid = bin2hex($uuid);
        return substr($uuid, 0, 8).'-'.substr($uuid, 8, 4).'-'.substr($uuid, 12, 4).'-'.substr($uuid, 16);
    }

    public static function uuid2bin(string $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid));
    }
}