<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * add document to starred.
     * 
     * @param string $docid     ID of document
     * @param string $user      username
     * @return void
     */
    public static function star_document(int $docid, string $user) : void
    {
        $db = self::db();
        try {
            $d = $db->prepare("INSERT INTO `starred`(docid, user) VALUES(?,?)");
            $d->execute([$docid, $user]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표 중 오류 발생');
        }
    }
}