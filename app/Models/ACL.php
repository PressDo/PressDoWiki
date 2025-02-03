<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\app\Models\Document;

class ACL extends \PressDo\app\Core\Model
{
    public static function aclgroups(): array
    {
        $db = self::db();

        try {
            $a = $db->query("SELECT `groupid`, `name` FROM aclgroups");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACLGroup 목록 조회 중 오류 발생');
        }
        return $a->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
    }

    /**
     * Get perms of user account. (all permissions)
     *
     * @param array $member      member object
     * @param array $perms      permission array
     * @param $document         document uuid
     * @return array            list of perms
     */
    public static function getAccountPerms(array $member, array &$perms, $document=null): void
    {
        $db = self::db();
        $memberuuid = self::uuid2bin($member['uuid']);

        try {
            $c = $db->prepare("SELECT `perm`, `registered` FROM `member` WHERE `uuid`=?");
            $c->execute([$memberuuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 권한 목록 조회 중 오류 발생');
        }
        $fetch = $c->fetch(PDO::FETCH_ASSOC);
        $perms = array_merge($perms, explode(',',$fetch['perm']));
        array_push($perms, 'member');

        if ($document !== null) {
            try {
                $c = $db->prepare("SELECT count(*) as cnt FROM `history` WHERE `contributor_m`=? AND `document`=?");
                $c->execute([$memberuuid, $document]);
            } catch (PDOException $err) {
                throw new ErrorException($err->getMessage().': 문서 기여 횟수 조회 중 오류 발생');
            }
            if ($c->rowCount() > 0)
                array_push($perms, 'document_contributor', 'contributor');
            
            if (Document::getTitleByUuid(self::bin2uuid($document))['title'] == $member['username'])
                array_push($perms, 'match_username_and_document_title');
        }

        if (!in_array('contributor', $perms)) {
            try {
                $c = $db->prepare("SELECT count(*) as cnt FROM `history` WHERE `contributor_m`=?");
                $c->execute([$memberuuid]);
            } catch (PDOException $err) {
                throw new ErrorException($err->getMessage().': 위키 기여 횟수 조회 중 오류 발생');
            }
            if ($c->rowCount() > 0)
                array_push($perms, 'contributor');
        }

        if ($_SERVER['REQUEST_TIME'] - $fetch['registered'] > 1296000)
            array_push($perms, 'member_signup_15days_ago');
    }

    /**
     * Get user's ACL Groups.
     * 
     * @return array            user's aclgroup list
     */
    public static function getUserAclgroups(array $session): array|bool
    {
        $db = self::db();

        $sql = "SELECT target_aclgroup,target_id FROM BlockHistory WHERE target_aclgroup IS NOT NULL AND (`until`>=? OR `until`=0) AND ";
        if ($session['member']) {
            $sql .= "target_member=?";
            $uparam = self::uuid2bin($session['member']['uuid']);
        } else {
            $sql .= " (CONV(HEX(?), 16, 10) & ~((1 << (32 - `mask`)) - 1)) = (CONV(HEX(target_ip), 16, 10) & ~((1 << (32 - `mask`)) - 1))";
            $uparam = inet_pton($session['ip']);
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
     * Get valid ACL settings of document.
     * 
     * @param int $uuid        ID of document
     * @param string $access    type of action
     * @return array ACL set of document
     */
    public static function fetchDocACL(string $uuid, string $access=null): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("SELECT `id`,`condition`,`access`,`action`,`until` as `expired` FROM `acl_document` 
            WHERE `uuid`=? AND ".($access!==null? "`access`=? AND": "")." (`until`>=? OR `until`=0) ORDER BY `id` ASC");
            $d->execute(
                ($access===null? [$uuid, $_SERVER['REQUEST_TIME']]:[$uuid, $access, $_SERVER['REQUEST_TIME']])
            );
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 권한 목록 조회 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getDocRule(int $id): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `uuid`, `condition`,`access`,`action` FROM `acl_document` WHERE `id`=?");
            $d->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL 규칙 조회 중 오류 발생');
        }
        return $d->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get valid ACL settings of namespace.
     * 
     * @param string $rawns     target namespace
     * @param string $access    type of action
     * @return array ACL set of namespace
     */
    public static function fetchNSACL(string $rawns, string $access=null): array
    {
        $db = self::db();
        try {
            $d = $db->prepare('SELECT `id`,`condition`,`access`,`action`,`until` as `expired` FROM `acl_namespace` WHERE `namespace`=? AND '.($access!==null? "`access`=? AND": "").' (`until`>=? OR `until`=0) ORDER BY `id` ASC');
            $d->execute(
                ($access === null ? [$rawns, $_SERVER['REQUEST_TIME']] : [$rawns, $access, $_SERVER['REQUEST_TIME']])
            );
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이름공간 권한 목록 조회 중 오류 발생');
        }
        
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get valid ACL settings of document.
     * 
     * @param int $uuid        ID of document
     * @param string $acldata  ACL dataset
     * @return array ACL set of document
     */
    public static function addDocACL(string $uuid, array $acldata, array $editdata): void
    {
        $db = self::db();
        ++$editdata['baserev'];

        $uuid = self::uuid2bin($uuid);

        if($editdata['contributor_m'] !== null)
            $cont_m = self::uuid2bin($editdata['contributor_m']);
        elseif($editdata['contributor_i'] !== null)
            $cont_i = self::uuid2bin(self::getIpUuid($editdata['contributor_i']));

        try {
            $d = $db->prepare("INSERT INTO `acl_document` (uuid, `condition`,`access`,`action`,`until`) VALUES (?,?,?,?,?)");
            $d->execute([$uuid, $acldata['condition'], $acldata['access'], $acldata['action'], $acldata['until']]);
            $h_uuid = self::uuid2bin(self::generateUuid());
            $d = $db->prepare("INSERT INTO `history` (uuid, document, rev, `datetime`, `action`, contributor_m, contributor_i, acl_changed) VALUES(?,?,?,?,'acl',?,?,?)");
            $d->execute([$h_uuid, $uuid, $editdata['baserev'], time(), $cont_m, $cont_i, $editdata['acl_changed']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 ACL 추가 중 오류 발생');
        }
    }

    /**
     * Get valid ACL settings of document.
     * 
     * @param int $namespace        ID of document
     * @param string $acldata    type of action
     * @return array ACL set of document
     */
    public static function addNSACL(string $namespace, array $acldata): void
    {
        $db = self::db();
        try {
            $d = $db->prepare("INSERT INTO `acl_namespace` (`namespace`,`condition`,`access`,`action`,`until`) VALUES (?,?,?,?,?)");
            $d->execute([$namespace, $acldata['condition'], $acldata['access'], $acldata['action'], $acldata['until']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이름공간 ACL 추가 중 오류 발생');
        }
    }

    public static function isDuplicate(string $typ, string $uuid_or_ns, string $access, string $condition): bool
    {
        if ($typ == 'doc'){
            $type = 'document';
            $uuid_or_ns = self::uuid2bin($uuid_or_ns);
        }elseif ($typ == 'ns')
            $type = 'namespace';

        $db = self::db();
        try {
            $d = $db->prepare("SELECT 1 FROM `acl_$type` WHERE ".($type == 'namespace' ? $type : 'uuid')."=? AND `condition`=? AND `access`=?");
            $d->execute([$uuid_or_ns, $condition, $access]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이름공간 ACL 추가 중 오류 발생');
        }

        return ($d->rowCount() > 0);
    }

    public static function deleteDocACL(int $id, array $editdata): void
    {
        $db = self::db();
        ++$editdata['baserev'];

        if($editdata['contributor_m'] !== null)
            $cont_m = self::uuid2bin($editdata['contributor_m']);
        elseif($editdata['contributor_i'] !== null)
            $cont_i = self::uuid2bin(self::getIpUuid($editdata['contributor_i']));

        try {
            $db->query("DELETE FROM acl_document WHERE id=$id");
            $h_uuid = self::uuid2bin(self::generateUuid());
            $d = $db->prepare("INSERT INTO `history` (uuid, document, rev, `datetime`, `action`, contributor_m, contributor_i, acl_changed) VALUES(?,?,?,?,'acl',?,?,?)");
            $d->execute([$h_uuid, self::uuid2bin($editdata['uuid']), $editdata['baserev'], time(), $cont_m, $cont_i, $editdata['acl_changed']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 ACL 삭제 중 오류 발생');
        }
    }

    public static function deleteNSACL(int $id): void
    {
        $db = self::db();
        try {
            $db->query("DELETE FROM acl_namespace WHERE id=$id");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 이름공간 ACL 삭제 중 오류 발생');
        }
    }
}