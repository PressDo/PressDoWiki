<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * get starred document list
     * 
     * @param string $username  username
     * @return array
     */
    public static function get_starred(string $username) : array
    {
        $db = self::db();
        try{
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `user`=?");
            $d->execute([$username]);
            return $d->fetchAll(\PDO::FETCH_COLUMN);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 문서함 조회 중 오류 발생');
        }
    }

    public static function get_modified_date(array $docidset): array
    {
        $db = self::db();
        try{
            $ORSTATEMENT = str_repeat(',?', count($docidset) - 1);
            $d = $db->prepare("SELECT `docid`, `datetime` FROM `document` WHERE `is_latest`='true' AND `docid` IN (?".$ORSTATEMENT.") ORDER BY `datetime`");
            $d->execute($docidset);
            return $d->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서함 문서 수정일자 조회 중 오류 발생');
        }
    }
}