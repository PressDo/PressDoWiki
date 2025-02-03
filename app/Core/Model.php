<?php
namespace PressDo\app\Core;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\app\Helpers\Database;

class Model {
    private static $db = null;

    /**
     * Connect Database.
     * @return PDO
     */
    protected static function db(): PDO
    {
        if(!self::$db){
            self::$db = Database::getInstance();
            return self::$db;
        }else
            return self::$db;
    }

    /**
     * get list of threads in the document
     * @param string $uuid     uuid of document
     * @return array            
     */
    public static function getDocThread(string $uuid, $mode='normal'): array
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
    public static function getVersion(string $uuid): int
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
     * find ip by uuid
     * @param string $ip
     * @return mixed UUID of ip
     */
    public static function getIpUuid($ip)
    {
        $db = self::db();
        
        $d = $db->prepare("SELECT uuid FROM ip WHERE ip=?");
        $d->execute([inet_pton($ip)]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1){
            $uuid = self::generateUuid();
            $d = $db->prepare("INSERT INTO ip(uuid,ip) VALUES(?,?)");
            $d->execute([self::uuid2bin($uuid),inet_pton($ip)]);
            return $uuid;
        }else
            return self::bin2uuid($data['uuid']);
    }
    
    /**
     * check perms grantable with 'grant'
     */
    public static function specialPerms(string $uuid): array
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

    /**
     * Generate new UUID4 String.
     */
    public static function generateUuid(): string
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