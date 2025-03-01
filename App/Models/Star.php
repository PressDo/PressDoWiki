<?php
namespace PressDo\App\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Star extends \PressDo\App\Core\Model
{
    /**
     * add document to starred.
     * 
     * @param string $uuid      UUID of document
     * @param string $user      UUID of user
     * @return void
     */
    public static function star(string $uuid, string $user): void
    {
        $db = self::db();
        try {
            $d = $db->prepare("INSERT INTO `starred`(document, user) VALUES(?,?)");
            $d->execute([self::uuid2bin($uuid), self::uuid2bin($user)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표 중 오류 발생');
        }
    }

    /**
     * remove document from starred.
     * 
     * @param string $uuid      UUID of document
     * @param string $user      UUID of user
     * @return void
     */
    public static function unstar(string $uuid, string $user): void
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM `starred` WHERE `document`=? AND `user`=?");
            $d->execute([self::uuid2bin($uuid), self::uuid2bin($user)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표해제 중 오류 발생');
        }
    }

    /**
     * get starred document list
     * 
     * @param string $uuid  uuid
     * @return array BINARY UUID
     */
    public static function getStarred(string $uuid) : array
    {
        $db = self::db();
        try{
            $d = $db->prepare("SELECT `document` FROM `starred` WHERE `user`=?");
            $d->execute([$uuid]);
            return $d->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 문서함 조회 중 오류 발생');
        }
    }

    public static function getStarredModifiedDate(array $uuidset): array
    {
        $db = self::db();
        try{
            $ORSTATEMENT = str_repeat(',?', count($uuidset) - 1);
            $d = $db->prepare("SELECT `document`, `datetime` FROM `history` WHERE `is_latest`='true' AND `document` IN (?".$ORSTATEMENT.") ORDER BY `datetime`");
            $d->execute($uuidset);
            return $d->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서함 문서 수정일자 조회 중 오류 발생');
        }
    }

    /**
     * get boolean if document is starred
     * 
     * @param int $uuid        Document UUID
     * @param string $user     User UUID
     * @return bool
     */
    public static function ifStarred(string $uuid, string $user): bool
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $user = self::uuid2bin($user);
        try {
            $d = $db->prepare("SELECT * FROM `starred` WHERE `document`=? AND `user`=?");
            $d->execute([$uuid, $user]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 별표여부 조회 중 오류 발생');
        }
        return !($d->rowCount() < 1);
    }

    /**
     * get numbers of star in this document
     * 
     * @param int $uuid        Document UUID
     * @return int
     */
    public static function count(string $uuid): int
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("SELECT count(*) as cnt FROM `starred` WHERE `document`=?");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 별표개수 조회 중 오류 발생');
        }
        return intval($d->fetch()['cnt']);
    }

}