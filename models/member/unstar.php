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
     * @param string $uuid      UUID of document
     * @param string $user      UUID of user
     * @return void
     */
    public static function unstar_document(string $uuid, string $user): void
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM `starred` WHERE `document`=? AND `user`=?");
            $d->execute([self::uuid2bin($uuid), self::uuid2bin($user)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표해제 중 오류 발생');
        }
    }
}