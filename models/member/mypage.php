<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function get_user_info(string $uuid)
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `email`, `perm`, `totp_secret` FROM `member` WHERE uuid=?");
            $d->execute([self::uuid2bin($uuid)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 정보 조회 중 오류 발생');
        }

        return $d->fetch(\PDO::FETCH_ASSOC);
    }

    public static function get_user_webauthn(string $uuid)
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `name`, `registered`, `lastuse` FROM `webauthn` WHERE uuid=? ORDER BY registered ASC");
            $d->execute([self::uuid2bin($uuid)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 목록 조회 중 오류 발생');
        }

        return $d->fetchAll(\PDO::FETCH_ASSOC);
    }
}