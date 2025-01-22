<?php
namespace PressDo;
require_once 'models/common.php';

use ErrorException;
use PDOException;
use \PDO as PDO;

class ACLModels extends baseModels
{
    public static function aclgroups($id)
    {
        $db = self::db();

        try {
            $a = $db->query("SELECT * FROM aclgroups", PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACLGroup 목록 조회 중 오류 발생');
        }
        return $a;
    }

    /**
     * Get perms of user account. (all permissions)
     *
     * @param object $member      member object
     * @param array $perms      permission array
     * @param $document         document uuid
     * @return array            list of perms
     */
    public static function get_account_perms(object $member, array &$perms, $document=null): void
    {
        $db = self::db();
        $memberuuid = self::uuid2bin($member->uuid);

        try {
            $c = $db->prepare("SELECT `perm`, `registered` FROM `member` WHERE `uuid`=?");
            $c->execute([$memberuuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 권한 목록 조회 중 오류 발생');
        }
        $fetch = $c->fetch(PDO::FETCH_ASSOC);
        $perms = array_merge($perms, explode(',',$fetch['perm']));
        array_push($perms, 'member');

        if($document !== null){
            try {
                $c = $db->prepare("SELECT count(*) as cnt FROM `history` WHERE `contributor_m`=? AND `document`=?");
                $c->execute([$memberuuid, $document]);
            } catch (PDOException $err) {
                throw new ErrorException($err->getMessage().': 문서 기여 횟수 조회 중 오류 발생');
            }
            if($c->rowCount() > 0)
                array_push($perms, 'document_contributor', 'contributor');
            
            if(self::get_doc_title(self::bin2uuid($document))['title'] == $member->username)
                array_push($perms, 'match_username_and_document_title');
        }

        if(!in_array('contributor', $perms)){
            try {
                $c = $db->prepare("SELECT count(*) as cnt FROM `history` WHERE `contributor_m`=?");
                $c->execute([$memberuuid]);
            } catch (PDOException $err) {
                throw new ErrorException($err->getMessage().': 위키 기여 횟수 조회 중 오류 발생');
            }
            if($c->rowCount() > 0)
                array_push($perms, 'contributor');
        }

        if($_SERVER['REQUEST_TIME'] - $fetch['registered'] > 1296000)
            array_push($perms, 'member_signup_15days_ago');
    }

    /**
     * Get user's ACL Groups.
     * 
     * @param object $session   session object
     * @return array            user's aclgroup list
     */
    public static function get_user_aclgroups(object $session): array|bool
    {
        $db = self::db();

        $sql = "SELECT target_aclgroup,target_id FROM BlockHistory WHERE target_aclgroup IS NOT NULL AND (`until`>=? OR `until`=0) AND ";
        if($session->member){
            $sql .= "target_member=?";
            $uparam = self::uuid2bin($session->member->uuid);
        }else{
            $sql .= " (CONV(HEX(?), 16, 10) & ~((1 << (32 - `mask`)) - 1)) = (CONV(HEX(target_ip), 16, 10) & ~((1 << (32 - `mask`)) - 1))";
            $uparam = inet_pton($session->ip);
        }
        // target_id가 2개면 add와 remove가 하나씩 있음
        $sql .= " GROUP BY target_id HAVING COUNT(*) = 1";

        $a = $db->prepare($sql);

        try {
            $a->execute([$_SERVER['REQUEST_TIME'], $uparam]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자의 ACL Group 조회 중 오류 발생');
        }
        
        return $a->fetchAll(PDO::FETCH_GROUP);
    }

    /**
     * Get perms of user in document.
     *
     * @param string $uuid        UUID of document
     * @param object $session   session object
     * @return array            list of perms
     */
    public static function get_document_perms(string $uuid, object $session): array
    {
        $db = self::db();
        $perms = [];
        try {
            $c = $db->prepare("SELECT count(*) as `cnt` FROM `history` WHERE `contributor`=?");
            $session->member? $c->execute(['m:'.$session->member->username]):$c->execute(['i:'.$session->ip]);
            $d = $db->prepare("SELECT count(*) as `cnt` FROM `history` WHERE `contributor`=? AND `uuid`=?");
            $session->member? $d->execute(['m:'.$session->member->username, $uuid]):$d->execute(['i:'.$session->ip, $uuid]);
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
     * @param int $uuid        ID of document
     * @param string $access    type of action
     * @return array ACL set of document
     */
    public static function fetch_doc_acl(string $uuid, string $access=null): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `id`,`condition`,`access`,`action`,`until` as `expired` FROM `acl_document` 
            WHERE `uuid`=? AND ".($access!==null? "`access`=? AND": "")." (`until`>=? OR `until`=0) AND `deleted`=0 ORDER BY `id` ASC");
            //var_dump($db);
            $d->execute(
                ($access===null? [$uuid, $_SERVER['REQUEST_TIME']]:[$uuid, $access, $_SERVER['REQUEST_TIME']])
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
    public static function fetch_ns_acl(string $rawns, string $access=null): array
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
}