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
            $d = $db->prepare("SELECT count(username) as cnt, `gravatar_url`, `username`, `password` FROM `member` WHERE `username`=?");
            $d->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 유저 조회 중 오류 발생');
        }
        
        $user = $d->fetch(\PDO::FETCH_ASSOC);
        unset($d);
        
        // not found
        if($user['cnt'] !== 1 || !password_verify($pw, $user['password']))
            return false;
        else
            unset($user['cnt']);
        
        try {
            $d = $db->prepare("INSERT INTO `login_history`(username,ip,datetime) VALUES(?,?,?)");
            $d->execute([$user['username'], $ip, $dt]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 기록 중 오류 발생');
        }
        unset($d);
        
        try {
            $d = $db->prepare("UPDATE `member` SET `last_login_ua`=? WHERE `username`=?");
            $d->execute([$ua, $user['username']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 처리 중 오류 발생');
        }
        
        return $user;
    }
}