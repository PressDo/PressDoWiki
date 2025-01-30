<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Member extends \PressDo\app\Core\Model
{
    /**
     * login user
     * @param string $id        username
     * @param string $pw      userpw
     * @param string $dt       datetime
     * @param string $ip       user IP
     * @param string $ua       user-agent
     * @return array|bool        false or userdata
     */
    public static function login(string $id, string $pw, string $dt, string $ip, string $ua): array|bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `username`, `password`, `skin`, `uuid`,`email` FROM `member` WHERE `username`=? OR email=?");
            $d->execute([$id, $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 유저 조회 중 오류 발생');
        }
        
        $user = $d->fetch(PDO::FETCH_ASSOC);
        
        // not found
        if ($d->rowCount() !== 1 || !password_verify($pw, $user['password']))
            return false;
        
        try {
            $d = $db->prepare("INSERT INTO `login_history`(uuid,ip,datetime) VALUES(?,?,?)");
            $d->execute([$user['uuid'], $ip, $dt]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 기록 중 오류 발생');
        }
        
        try {
            $d = $db->prepare("UPDATE `member` SET `last_login_ua`=? WHERE `uuid`=?");
            $d->execute([$ua, $user['uuid']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 처리 중 오류 발생');
        }
        $user['uuid'] = self::bin2uuid($user['uuid']);
        
        return $user;
    }

    public static function exist($username='', $email=''): array|bool
    {
        $db = self::db();
        try{
            if (empty($email)) {
                $d = $db->prepare("SELECT username, email FROM member WHERE username=?");
                $d->execute([$username]);
            } else {
                $d = $db->prepare("SELECT username, email FROM member WHERE email=? AND username IS NOT NULL");
                $d->execute([$email]);
            }
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 조회 중 오류 발생');
        }
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1)
            return false;
        else
            return $data;
    }

    /**
     * register temporary data
     * @param string $email     entered email
     * @param string $ip        requestor's IP
     * @param bool $update      if email code already exists
     */
    public static function regCodeAdd(string $email, string $ip, bool $update=false): string
    {
        $db = self::db();
        $time = time();
        $ip = inet_pton($ip);
        try {
            if ($update) {
                $d = $db->prepare("UPDATE `member` SET registered=? WHERE email=?");
                $d->execute([$time, $email]);
            } else {
                $uuid = self::uuid2bin(self::generateUuid());
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
     * @param string $code     email verification code
     */
    public static function regCodeCheck(string $code, string $ip): bool|string
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
        $data = $d->fetch(PDO::FETCH_ASSOC);
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
     * @param string $email     entered email
     */
    public static function chkEmailInput(string $email): bool|string
    {
        $db = self::db();
        $time = time();
        try {
            $d = $db->prepare("SELECT `registered` FROM `member` WHERE email=? AND username IS NULL");
            $d->execute([$email]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 인증 코드 정보 확인 중 오류 발생');
        }
        if ($d->rowCount() < 1)
            return false;

        $data = $d->fetch(PDO::FETCH_ASSOC);
        
        if ($time > $data['registered'] + 86400)
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
    public static function register(string $username, string $password, string $email): bool
    {
        $db = self::db();
        $uuid = self::uuid2bin(self::generateUuid());
        $uuid2 = self::uuid2bin(self::generateUuid());
        $pw = password_hash($password, PASSWORD_BCRYPT);
        try {
            $d = $db->prepare("UPDATE member SET `username`=?, `password`=? WHERE `email`=?");
            $d->execute([$username, $pw, $email]);
            $d = $db->prepare("SELECT `uuid` FROM member WHERE `username`=?");
            $d->execute([$username]);
            $memberuuid = $d->fetch(PDO::FETCH_ASSOC)['uuid'];
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

    public static function getUserInfo(string $uuid)
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `email`, `perm`, `totp_secret` FROM `member` WHERE uuid=?");
            $d->execute([self::uuid2bin($uuid)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 정보 조회 중 오류 발생');
        }

        return $d->fetch(PDO::FETCH_ASSOC);
    }

    public static function getUserWebauthn(string $uuid)
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `name`, `registered`, `lastuse` FROM `webauthn` WHERE uuid=? ORDER BY registered ASC");
            $d->execute([self::uuid2bin($uuid)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 목록 조회 중 오류 발생');
        }

        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function grantPermissions(string $executor, string $username, array $perms, string $record): void
    {
        $db = self::db();
        $c = implode(',', $perms);
        $e = $db->prepare("UPDATE `member` SET `perm`=? WHERE `username`=?");
        $e->execute([$c,$username]);
        $f = $db->prepare("INSERT INTO `BlockHistory` (executor,target_member,datetime,action,granted) VALUES(?,?,?,'grant',?)");
        $f->execute([$executor, $username, $_SERVER['REQUEST_TIME'], $record]);
    }

    /**
     * find ip by uuid
     * @param string $uuid
     * @return mixed
     */
    public static function ipLookup($uuid)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $d = $db->prepare("SELECT ip FROM ip WHERE uuid=?");
        $d->execute([$uuid]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1)
            return false;
        else
            return inet_ntop($data['ip']);
    }

    /**
     * find username by uuid
     * @param string $uuid
     * @return mixed
     */
    public static function lookup($uuid)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $d = $db->prepare("SELECT username FROM member WHERE uuid=?");
        $d->execute([$uuid]);
        $data = $d->fetch(PDO::FETCH_ASSOC);
        if($d->rowCount() < 1)
            return false;
        else
            return $data['username'];
    }
}