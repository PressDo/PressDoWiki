<?php
namespace PressDo;

require 'helpers/DB.php';

use ErrorException;
use PDOException;
use \PDO as PDO;

class baseModels {
    private static $db = null;

    /**
     * Connect Database.
     * @return PDO
     */
    public static function db() : PDO
    {
        if(!self::$db){
            self::$db = \PressDo\DB::getInstance();
            return self::$db;
        }else
            return self::$db;
    }

    /**
     * Get ID of the document.
     *
     * @param   string $rawns     Document namespace (raw)
     * @param   string $title     Document Title
     * @return  int|bool       Document ID
     */
    public static function get_doc_id($rawns, $title): int|bool
    {
        $db = self::db();

        try {
            $c = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND BINARY `title`=?");
            $c->execute([$rawns, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 ID 조회 중 오류 발생');
        }

        if($c->rowCount() < 1)
            $res = false;
        else
            $res = intval($c->fetch(PDO::FETCH_ASSOC)['docid']);
        
        return $res;
    }

    /**
     * Get ID of the document.
     *
     * @param   string $id     Document namespace (raw)
     * @return  array       Document ID
     */
    public static function get_doc_title(string $id): array
    {
        $db = self::db();

        try {
            $c = $db->prepare("SELECT `namespace`,`title` FROM `live_document_list` WHERE `docid`=?");
            $c->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ID로 문서명 조회 중 오류 발생');
        }

        if($c->rowCount() < 1)
            $res = false;
        else
            $res = $c->fetch(PDO::FETCH_ASSOC);
        
        return $res;
    }

    /**
     * Load document data
     * 
     * @param string $rawns     namespace of document
     * @param string $title     title of document
     * @param int|null $rev     revision of document
     * @return null|array       array(raw namespace, Namespace, Title)
     */
    public static function load(string $rawns, string $title, int|null $rev=null): null | array
    {
        $db = self::db();
        $sql = "SELECT d.content,d.length,d.comment,d.datetime,d.action,d.rev,d.count,d.reverted_version,d.contributor_m,d.contributor_i, d.edit_request_uri,d.acl_changed,d.moved_from,d.moved_to,d.is_hidden,d.is_latest FROM `document` as d INNER JOIN `live_document_list` as l ON l.docid = d.docid WHERE BINARY l.`namespace`=? AND BINARY l.`title`=? AND `is_hidden`='false' ORDER BY d.`datetime`";
        if($rev === null){
            $d = $db->prepare($sql.' DESC LIMIT 1');
        } else {
            $d = $db->prepare($sql.' ASC LIMIT '.$rev-1 .', 1');
        }

        try {
            $d->execute([$rawns, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 데이터 조회 중 오류 발생');
        }

        # return null if not found
        return ($d->rowCount() < 1)? null : $d->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * get list of threads in the document
     * @param string $rawns     namespace of document
     * @param string $title     title of document
     * @return array            
     */
    public static function get_doc_thread(string $rawns, string $title, $mode='normal') : array
    {
        $db = self::db();
        try {
            if($mode === 'normal'){
                $d = $db->prepare("SELECT urlstr,topic FROM `thread_list` WHERE BINARY `namespace`=? AND BINARY `title`=? AND (`status`='normal' OR `status`='pause')");
            }elseif($mode === 'closed'){
                $d = $db->prepare("SELECT urlstr,topic FROM `thread_list` WHERE BINARY `namespace`=? AND BINARY `title`=? AND `status`='close'");
            }
            $d->execute([$rawns, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 토론 목록 조회 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get perms of user account.
     *
     * @param string $username  username
     * @return array            list of perms
     */
    public static function get_account_perms(string $username): array
    {
        $db = self::db();
        try {
            $c = $db->prepare("SELECT `perm`, `registered` FROM `member` WHERE `username`=?");
            $c->execute([$username]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 권한 목록 조회 중 오류 발생');
        }
        $fetch = $c->fetch(PDO::FETCH_ASSOC);
        $perms = explode(',',$fetch['perm']);

        if($_SERVER['REQUEST_TIME'] - $fetch['registered'] > 1296000)
            array_push($perms, 'member_signup_15days_ago');

        array_push($perms, 'member');

        return $perms;
    }

    /**
     * Get perms of user in document.
     *
     * @param int $docid        ID of document
     * @param object $session   session object
     * @return array            list of perms
     */
    public static function get_document_perms(int $docid, object $session): array
    {
        $db = self::db();
        $perms = [];
        try {
            if($session->member){
                $c = $db->prepare("SELECT count(*) as `cnt` FROM `document` WHERE `contributor_m`=?");
                $c->execute([$session->member->username]);
                $d = $db->prepare("SELECT count(*) as `cnt` FROM `document` WHERE `contributor_m`=? AND `docid`=?");
                $d->execute([$session->member->username, $docid]);
            }else{
                $c = $db->prepare("SELECT count(*) as `cnt` FROM `document` WHERE `contributor_i`=?");
                $c->execute([$session->ip]);
                $d = $db->prepare("SELECT count(*) as `cnt` FROM `document` WHERE `contributor_i`=? AND `docid`=?");
                $d->execute([$session->ip, $docid]);
            }
            
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 권한 목록 조회 중 오류 발생');
        }

        if($c->fetch(PDO::FETCH_ASSOC)['cnt'] > 0)
            array_push($perms, 'contributor');

        if($d->fetch(PDO::FETCH_ASSOC)['cnt'] > 0)
            array_push($perms, 'document_contributor');

        return $perms;
    }

    /**
     * Get valid ACL settings of document.
     * 
     * @param int $docid        ID of document
     * @param string $access    type of action
     * @return array ACL set of document
     */
    public static function fetch_doc_acl(int $docid, string $access=null) : array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `id`,`condition`,`access`,`action`,`until` as `expired` FROM `acl_document` WHERE `docid`=? AND ".($access!==null? "`access`=? AND": "")." (`until`>=? OR `until`=0) AND `deleted`=0 ORDER BY `id` ASC");
            $d->execute(
                ($access===null? [$docid, $_SERVER['REQUEST_TIME']]:[$docid, $access, $_SERVER['REQUEST_TIME']])
            );
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 권한 목록 조회 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get valid ACL settings of namespace.
     * 
     * @param string $rawns     target namespace
     * @param string $access    type of action
     * @return array ACL set of namespace
     */
    public static function fetch_ns_acl(string $rawns, string $access=null) : array
    {
        $db = self::db();
        try {
            $d = $db->prepare('SELECT `id`,`condition`,`access`,`action`,`until` as `expired` FROM `acl_namespace` WHERE `namespace`=? AND '.($access!==null? "`access`=? AND": "").' (`until`>=? OR `until`=0) AND `deleted`=0 ORDER BY `id` ASC');
            $d->execute(
                ($access===null? [$rawns, $_SERVER['REQUEST_TIME']]:[$rawns, $access, $_SERVER['REQUEST_TIME']])
            );
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이름공간 권한 목록 조회 중 오류 발생');
        }
        
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get if this user is in ACL Group.
     * 
     * @param object $session   session object
     * @param string $aclgroup  name of aclgroup
     * @return bool             if this user is in aclgroup
     */
    public static function in_aclgroup(object $session, string $aclgroup, $mode=null) : bool
    {
        $db = self::db();
        $username = ($session->member)? $session->member->username : null;
        

        if(strpos($session->ip,'.') !== false){
            $mode = 'ipv4';
            $ip = WikiCore::ipv62long($session->ip);

        }elseif(strpos($session->ip,':') !== false){
            $mode = 'ipv6';
            $ip = ip2long($session->ip);
        }

        $sql = "SELECT count(*) as cnt,id,`datetime`,comment,until FROM `BlockHistory` WHERE target_aclgroup=? AND "
            .($username ? "target_member=?" : "(? BETWEEN from_ip AND to_ip) AND ipver=?")
            ." AND `action`='aclgroup_add' AND (`until`>=? OR `until`=0) AND removed IS NULL ORDER BY datetime";
        

        try {
            if($username){
                // find by username
                $a = $db->prepare($sql);
                $a->execute([$aclgroup,$username,$_SERVER['REQUEST_TIME']]);
            }else{
                //find by ip
                if($mode == 'CIDR') {
                    // Check CIDR Duplicate
                    $a = $db->prepare("SELECT count(*) as cnt FROM `BlockHistory` WHERE target_aclgroup=? AND display_ip=? AND action='aclgroup_add' AND (`until`>=? OR `until`=0) AND removed IS NULL ORDER BY datetime");
                    $a->execute([$aclgroup,$session->ip,$_SERVER['REQUEST_TIME']]);
                    if($a->fetch()['cnt'] > 0)
                        return true;
                    else
                        return false;
                }
                
                $a = $db->prepare($sql);
                $a->execute([$aclgroup,$ip,$mode,$_SERVER['REQUEST_TIME']]);
            }
            
            $b = $a->fetch();
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL Group 포함 여부 조회 중 오류 발생');
        }
        if(intval($b['cnt']) < 1)
            return false;
        else
            return true;
    }
    
    public static function get_version(string $rawns, string $title) : string
    {
        $db = self::db();
        $id = self::get_doc_id($rawns, $title);
        try {
            $d = $db->prepare("SELECT `rev` FROM `document` WHERE `docid`=? ORDER BY `rev` DESC LIMIT 1");
            $d->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 버전 조회 중 오류 발생');
        }
        return $d->fetch(PDO::FETCH_ASSOC)['rev'];
    }

    public static function exist(string $rawns, string $title) : bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT count(*) as cnt FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $d->execute([$rawns,$title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 검색 중 오류 발생');
        }
        if(intval($d->fetch(PDO::FETCH_ASSOC)['cnt']) > 0)
            return true;
        else
            return false;
    }
    
    public static function special_perms(string $username) : array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `perm` FROM `member` WHERE `username`=?");
            $d->execute([$username]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 특별권한 조회 중 오류 발생');
        }
        return explode(',', $d->fetch(PDO::FETCH_ASSOC)['perm']);
    }
}