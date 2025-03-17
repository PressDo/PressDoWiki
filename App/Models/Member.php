<?php
namespace PressDo\App\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Member extends \PressDo\App\Core\Model
{
    /**
     * check member info
     * @param string $id        username/email
     * @param string $pw      userpw
     * @return array|bool        false or array(username, uuid, password)
     */
    public static function checkMember(string $id, string $pw): array|bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `username`, `password`, `uuid` FROM `member` WHERE `username`=:id OR email=:id");
            $d->execute(['id' => $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 유저 조회 중 오류 발생');
        }
        
        $user = $d->fetch(PDO::FETCH_ASSOC);
        
        // not found
        if ($d->rowCount() !== 1 || !password_verify($pw, $user['password']))
            return false;
        
        $user['uuid'] = self::bin2uuid($user['uuid']);
        return $user;
    }

    /**
     * login user
     * @param string $uuid        uuid
     * @param string $ip       user IP
     * @param string $ua       user-agent
     * @return array|bool        false or userdata
     */
    public static function login(string $uuid, string $ip, string $ua): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("SELECT `username`, `skin`, `email` FROM `member` WHERE `uuid`=?");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 유저 조회 중 오류 발생');
        }
        
        $user = $d->fetch(PDO::FETCH_ASSOC);
        
        try {
            $d = $db->prepare("INSERT INTO `login_history`(uuid,ip) VALUES(?,?)");
            $d->execute([$uuid, $ip]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 기록 중 오류 발생');
        }
        
        try {
            $d = $db->prepare("UPDATE `member` SET `last_login_ua`=? WHERE `uuid`=?");
            $d->execute([$ua, $uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 로그인 처리 중 오류 발생');
        }
        
        return $user;
    }

    /**
     * Check if member exists.
     * @param mixed $username
     * @param mixed $email
     * @throws \ErrorException
     * @return array|bool           Member's uuid, username and email; false if not exists
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
        if($d->rowCount() < 1)
            return false;
        else {
            $data['uuid'] = self::bin2uuid($data['uuid']);
            return $data;
        }
    }

    /**
     * register temporary data
     * @param string $email     entered email
     * @param string $ip        requestor's IP
     * @param bool $update      if email code already exists
     */
    public static function regCodeAdd(string $email, string $ip): string
    {
        $db = self::db();
        $ip = inet_pton($ip);
        $key = random_bytes(64);
        try {
            $d = $db->prepare("DELETE FROM `email_keys` WHERE email=?");
            $d->execute([$email]);
            
            $d = $db->prepare("INSERT INTO `email_keys`(email,ip,`key`) VALUES (?,?,?)");
            $d->execute([$email, $ip, $key]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이메일 인증 정보 등록 중 오류 발생');
        }

        $code = md5($email).'-'.bin2hex($key);
        return $code;
    }

    /**
     * register temporary data
     * @param string $code     email verification code
     */
    public static function regCodeCheck(string $code, string $ip, bool $strict_ip = false): bool|string
    {
        $db = self::db();
        $c = explode('-', $code);
        $ip = inet_pton($ip);
        try {
            $d = $db->prepare("SELECT email, ip FROM `email_keys` WHERE MD5(email)=? AND `key`=? AND `time` >= unix_timestamp()-86400");
            $d->execute([$c[0], hex2bin($c[1])]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이메일 인증 확인 중 오류 발생');
        }
        if ($d->rowCount() < 1)
            return false;

        $data = $d->fetch(PDO::FETCH_ASSOC);
        
        if(!$strict_ip || $data['ip'] == $ip)
            return $data['email'];
        else
            return 'err_ip_differs';
    }

    public static function regCodeUnset(string $email)
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM `email_keys` WHERE email=?");
            $d->execute([$email]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이메일 인증 확인 중 오류 발생');
        }
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

    public static function updatePassword(string $email, string $password): void
    {
        $db = self::db();
        $pw = password_hash($password, PASSWORD_BCRYPT);
        try {
            $d = $db->prepare("UPDATE `member` SET `password`=? WHERE email=?");
            $d->execute([$pw, $email]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 비밀번호 변경 중 오류 발생');
        }
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
    public static function ipLookup($uuid): bool|string
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

    /**
     * Save cookie value to database.
     * @param string $uuid
     * @param string $name
     * @param int $length
     * @return string value of saved cookie (in hex)
     */
    public static function saveCookies(string $uuid, string $name, int $length): string
    {
        $db = self::db();
        $exec = self::uuid2bin($uuid);
        $token = random_bytes(16);

        $d = $db->prepare("INSERT INTO cookies (user, `name`, `value`, expiry) VALUES (?,?,?,?)");
        $d->execute([$exec, $name, $token, $_SERVER['REQUEST_TIME'] + $length]);
        return bin2hex($token);
    }

    public static function checkCookie(string $name, string $value): string|null
    {
        $db = self::db();
        $token = hex2bin($value);

        $d = $db->prepare("SELECT user FROM cookies WHERE `name`=? AND `value`=?");
        $d->execute([$name, $token]);
        if ($d->rowCount() > 0)
            return self::bin2uuid($d->fetch(PDO::FETCH_ASSOC)['user']);
        else
            return null;
    }

    public static function deleteCookie(string $uuid, string $name, string $value): void
    {
        $db = self::db();
        $exec = self::uuid2bin($uuid);
        $token = self::uuid2bin($value);

        $d = $db->prepare("DELETE FROM cookies WHERE user=? AND `name`=? AND `value`=?");
        $d->execute([$exec, $name, $token]);
    }
}