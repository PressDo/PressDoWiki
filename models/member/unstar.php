<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * remove document from starred.
     * 
     * @param string $docid     ID of document
     * @param string $user      username
     * @return void
     */
    public static function unstar_document(int $docid, string $user) : void
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM `starred` WHERE `docid`=? AND `user`=?");
            $d->execute([$docid, $user]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표해제 중 오류 발생');
        }
    }
}