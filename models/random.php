<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function get_random(): array
    {
        $db = self::db();
        try {
            $d = $db->query("SELECT `namespace`,`title` FROM `live_document_list` WHERE `namespace`='document' ORDER BY RAND() LIMIT 1");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 무작위 문서 불러오기 중 오류 발생');
        }
        return $d->fetch(\PDO::FETCH_ASSOC);
    }
}
