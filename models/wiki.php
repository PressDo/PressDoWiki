<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * get boolean if document is starred
     * 
     * @param int $docid        Document ID
     * @param string $username  username
     * @return bool
     */
    public static function if_starred(int $docid, string $username) : bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `docid`=? AND `user`=?");
            $d->execute([$docid, $username]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 별표여부 조회 중 오류 발생');
        }
        $bool = ($d->rowCount() < 1)? false : true;
        return $bool;
    }

    /**
     * get numbers of star in this document
     * 
     * @param int $docid        Document ID
     * @return int
     */
    public static function count_stars(int $docid) : int
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT count(*) as cnt FROM `starred` WHERE `docid`=?");
            $d->execute([$docid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 별표개수 조회 중 오류 발생');
        }
        return intval($d->fetch()['cnt']);
    }
}