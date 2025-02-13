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
    public static function login(string $id, string $pw, string $ip, string $ua): array|bool
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
            $d = $db->prepare("INSERT INTO `login_history`(uuid,ip) VALUES(?,?)");
            $d->execute([$user['uuid'], $ip]);
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

    /**
     * Check if member exists.
     * @param mixed $username
     * @param mixed $email
     * @throws \ErrorException
     * @return array|bool           Member's uuid, username and email
     */
    public static function exist($username='', $email=''): array|bool
    {
        $db = self::db();
        try{
            if (empty($email)) {
                $d = $db->prepare("SELECT uuid, username, email, last_login_ua FROM member WHERE username=?");
                $d->execute([$username]);
            } else {
                $d = $db->prepare("SELECT uuid, username, email, last_login_ua FROM member WHERE email=? AND username IS NOT NULL");
                $d->execute([$email]);
            }
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 조회 중 오류 발생');
        }
        $data = $d->fetch(PDO::FETCH_ASSOC);
        $data['uuid'] = self::bin2uuid($data['uuid']);
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

    public static function setTotp(string $uuid, string $secret)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        
        try {
            $e = $db->prepare("UPDATE `member` SET `totp_secret`=? WHERE `uuid`=?");
            $e->execute([$secret,$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': TOTP 등록 중 오류 발생');
        }
    }

    public static function removeTotp(string $uuid)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        
        try {
            $e = $db->prepare("UPDATE `member` SET `totp_secret`=NULL WHERE `uuid`=?");
            $e->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': TOTP 삭제 중 오류 발생');
        }
    }

    public static function getUserWebauthn(string $uuid): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `name`, `registered`, `lastuse`, client_data FROM `webauthn` WHERE uuid=? ORDER BY registered ASC");
            $d->execute([self::uuid2bin($uuid)]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 목록 조회 중 오류 발생');
        }

        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function saveUserWebauthn(string $uuid, string $name, string $data): void
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        
        try {
            $e = $db->prepare("INSERT INTO `webauthn` (uuid, `name`, client_data) VALUES (?, ?, ?)");
            $e->execute([$uuid,$name,$data]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 추가 중 오류 발생');
        }
    }

    public static function deleteUserWebauthn(string $uuid, string $name): void
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM `webauthn` WHERE uuid=? AND `name`=?");
            $d->execute([self::uuid2bin($uuid), $name]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 삭제 중 오류 발생');
        }
    }

    public static function logWebauthnUsage(string $name)
    {
        $db = self::db();
        try {
            $d = $db->prepare("UPDATE `webauthn` SET lastuse=UNIX_TIMESTAMP() WHERE `name`=?");
            $d->execute([$name]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 사용 기록 중 오류 발생');
        }
    }

    public static function grantPermissions(string $executoruuid, string $targetuuid, array $perms, string $record): void
    {
        $db = self::db();
        $executoruuid = self::uuid2bin($executoruuid);
        $targetuuid = self::uuid2bin($targetuuid);
        $c = implode(',', $perms);
        try {
            $e = $db->prepare("UPDATE `member` SET `perm`=? WHERE `uuid`=?");
            $e->execute([$c,$targetuuid]);
            $f = $db->prepare("INSERT INTO `BlockHistory` (id,executor_m,target_member,action,granted) VALUES(0,?,?,'grant',?)");
            $f->execute([$executoruuid, $targetuuid, $record]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': Webauthn 목록 조회 중 오류 발생');
        }
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
     * @return string|false username (false if not exist)
     */
    public static function lookup($uuid): string|bool
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

    public static function loginHistory(string $id, string $execuuid, $from=null, $until=null)
    {
        $db = self::db();
        $exec = self::uuid2bin($execuuid);
        $target = self::uuid2bin($id);
        $sqlstr = '';

        if($from !== null)
            $sqlstr = "AND `datetime`<=$from";
        elseif($until !== null)
            $sqlstr = "AND `datetime`>=$until";

        

        $d = $db->prepare("SELECT ip, `datetime` FROM `login_history` WHERE `uuid`=? $sqlstr ORDER BY `datetime` DESC LIMIT 50");
        $d->execute([$target]);
        $e = $db->prepare("INSERT INTO `BlockHistory` (id,executor_m,target_member,action) VALUES(0,?,?,'login_history')");
        $e->execute([$exec, $target]);
        return $d->fetchAll();
    }

    public static function getLogintimeEnd(string $uuid): array
    {
        $db = self::db();
        $exec = self::uuid2bin($uuid);

        $d = $db->prepare("SELECT MAX(`datetime`) AS max, MIN(`datetime`) AS min FROM `login_history` WHERE `uuid`=? ORDER BY `datetime`");
        $d->execute([$exec]);
        return $d->fetch();
    }
}