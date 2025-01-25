<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * register temporary data
     * 
     * @param string $email     entered email
     * @param string $ip        requestor's IP
     * @param bool $update      if email code already exists
     */
    public static function regcodeadd(string $email, string $ip, bool $update=false): string
    {
        $db = self::db();
        $time = time();
        $ip = inet_pton($ip);
        try {
            if($update){
                $d = $db->prepare("UPDATE `member` SET registered=? WHERE email=?");
                $d->execute([$time, $email]);
            }else{
                $uuid = self::uuid2bin(self::uuid_generate());
                $d = $db->prepare("INSERT INTO `member`(uuid,email,registered,registered_ip) VALUES (?,?,?,?)");
                $d->execute([$uuid, $email, $time, $ip]);
            }
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이메일 인증 정보 등록 중 오류 발생');
        }
        $code = md5($email).'-'.md5($ip).'-'.hash('sha256', $uuid & decbin($time));
        return $code;
    }

    /**
     * register temporary data
     * 
     * @param string $code     email verification code
     */
    public static function regcodecheck(string $code, string $ip): bool|string
    {
        $db = self::db();
        $c = explode('-', $code);
        $ip = inet_pton($ip);
        try {
            $d = $db->prepare("SELECT `uuid`, `registered`, email, registered_ip FROM `member` WHERE MD5(email)=?");
            $d->execute([$c[0]]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이메일 인증 확인 중 오류 발생');
        }
        $data = $d->fetch(\PDO::FETCH_ASSOC);
        $c_code = md5($data['email']).'-'.md5($data['registered_ip']).'-'.hash('sha256', $data['uuid'] & decbin($data['registered']));
        if($code === $c_code){
            if($data['registered_ip'] == $ip)
                return $data['email'];
            else
                return 'err_ip_differs';
        }else
            return false;
    }

    /**
     * check if email code is already issued
     * 
     * @param string $email     entered email
     */
    public static function chkemailinput(string $email): bool|string
    {
        $db = self::db();
        $time = time();
        try {
            $d = $db->prepare("SELECT `registered` FROM `member` WHERE email=? AND username IS NULL");
            $d->execute([$email]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 인증 코드 정보 확인 중 오류 발생');
        }
        if($d->rowCount() < 1)
            return false;

        $data = $d->fetch(\PDO::FETCH_ASSOC);
        
        if($time > $data['registered'] + 86400)
            return true;
        else
            return 'err_already_sent';
    }

    /**
     * Register user information
     * @param string $username  username
     * @param string $password  password
     * @param string $email     email
     * @throws \ErrorException  PDOException
     * @return bool
     */
    public static function register_user(string $username, string $password, string $email): bool
    {
        $db = self::db();
        $uuid = self::uuid2bin(self::uuid_generate());
        $uuid2 = self::uuid2bin(self::uuid_generate());
        $pw = password_hash($password, PASSWORD_BCRYPT);
        try {
            $d = $db->prepare("UPDATE member SET `username`=?, `password`=? WHERE `email`=?");
            $d->execute([$username, $pw, $email]);
            $d = $db->prepare("SELECT `uuid` FROM member WHERE `username`=?");
            $d->execute([$username]);
            $memberuuid = $d->fetch(\PDO::FETCH_ASSOC)['uuid'];
            $d = $db->prepare("INSERT into document (`uuid`, `namespace`, `title`, `backlink_updated`) VALUES(?,'사용자',?,1)");
            $d->execute([$uuid, $username]);
            $d = $db->prepare("INSERT into history (`uuid`, `document`, `datetime`, `action`, `contributor_m`) VALUES(?,?,?,'create',?)");
            $d->execute([$uuid2, $uuid, time(), $memberuuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 회원가입 처리 중 오류 발생');
        }

        if($d->rowCount() < 1)
            return false;
        else
            return true;
    }
}