<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * login user
     * 
     * @param string $id        username
     * @param string $pw      userpw
     * @param string $dt       datetime
     * @param string $ip       user IP
     * @param string $ua       user-agent
     * @return array|bool        false or userdata
     */
    public static function login(string $id, string $pw, string $dt, string $ip, string $ua): array | bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `username`, `password`, `skin`, `uuid`,`email` FROM `member` WHERE `username`=? OR email=?");
            $d->execute([$id, $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 유저 조회 중 오류 발생');
        }
        
        $user = $d->fetch(\PDO::FETCH_ASSOC);
        
        // not found
        if($d->rowCount() !== 1 || !password_verify($pw, $user['password']))
            return false;
        
        try {
            $d = $db->prepare("INSERT INTO `login_history`(uuid,ip,datetime) VALUES(?,?,?)");
            $d->execute([$user['uuid'], $ip, $dt]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 기록 중 오류 발생');
        }
        unset($d);
        
        try {
            $d = $db->prepare("UPDATE `member` SET `last_login_ua`=? WHERE `uuid`=?");
            $d->execute([$ua, $user['uuid']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 처리 중 오류 발생');
        }
        $user['uuid'] = self::bin2uuid($user['uuid']);
        
        return $user;
    }
}