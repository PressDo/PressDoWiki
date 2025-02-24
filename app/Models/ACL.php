<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\app\Models\Document;

class ACL extends \PressDo\app\Core\Model
{
    /**
     * Get associative array about aclgroup (groupid => name)
     * @throws \ErrorException
     * @return array
     */
    public static function aclgroups(): array
    {
        $db = self::db();

        try {
            $a = $db->query("SELECT `groupid`, `name` FROM aclgroups ORDER BY groupid");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACLGroup 목록 조회 중 오류 발생');
        }
        
        return $a->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Get perms of user account. (all permissions)
     *
     * @param array $member      member object (require username and uuid)
     * @param array &$perms      permission array
     * @param $document         document uuid
     * @return void            list of perms
     */
    public static function getAccountPerms(string $uuid, string $username, array &$perms, $document=null): void
    {
        $db = self::db();
        $memberuuid = self::uuid2bin($uuid);

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
            
            if (Document::getTitleByUuid(self::bin2uuid($document))['title'] == $username)
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
     * @param ?string $ip
     * @param ?string $uuid
     * @return array            user's aclgroup list ['Group Name': [0: ['id', 'groupid', 'comment', 'until']]]
     */
    public static function getUserAclgroups(?string $ip=null, ?string $uuid=null): array
    {
        $db = self::db();

        $sql = "SELECT target_aclgroup, id, groupid, comment, `until`, `datetime` FROM BlockHistory JOIN aclgroups ON `name` = target_aclgroup WHERE target_aclgroup IS NOT NULL AND (`until`>=unix_timestamp() OR `until`=0) AND ";
        if (!empty($uuid)) {
            $sql .= "target_member=?";
            $uparam = self::uuid2bin($uuid);
        } else {
            $sql .= " (? & mask_to_bin(mask)) = (target_ip & mask_to_bin(mask))";
            $uparam = inet_pton($ip);
        }
        // id가 2개면 add와 remove가 하나씩 있음
        $sql .= " GROUP BY id HAVING COUNT(*) = 1";

        $a = $db->prepare($sql);

        try {
            $a->execute([$uparam]);
        } catch (PDOException $err) {
            //if ($err->getCode() == '22003')
            //    throw new ErrorException($err->getMessage().': 사용자의 ACL Group 조회 중 오류 발생. IP:'.$ip);
            throw new ErrorException($err->getMessage().': 사용자의 ACL Group 조회 중 오류 발생');
        }
        
        return $a->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
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
            WHERE `uuid`=? AND ".($access!==null? "`access`=? AND": "")." (`until`>=unix_timestamp() OR `until`=0) ORDER BY `id` ASC");
            $d->execute(
                ($access===null? [$uuid]:[$uuid, $access])
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
            $d = $db->prepare('SELECT `id`,`condition`,`access`,`action`,`until` as `expired` FROM `acl_namespace` WHERE `namespace`=? AND '.($access!==null? "`access`=? AND": "").' (`until`>=unix_timestamp() OR `until`=0) ORDER BY `id` ASC');
            $d->execute(
                ($access === null ? [$rawns] : [$rawns, $access])
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
            $cont_i = self::uuid2bin($editdata['contributor_i']);

        $h_uuid = self::uuid2bin(self::generateUuid());

        try {
            $d = $db->prepare("INSERT INTO `acl_document` (uuid, `condition`,`access`,`action`,`until`) VALUES (?,?,?,?,?)");
            $d->execute([$uuid, $acldata['condition'], $acldata['access'], $acldata['action'], $acldata['until']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 ACL 추가 중 오류 발생');
        }
        try {
            $d = $db->prepare("INSERT INTO `history` (uuid, document, rev, `action`, contributor_m, contributor_i, acl_changed) VALUES(?,?,?,?,'acl',?,?,?)");
            $d->execute([$h_uuid, $uuid, $editdata['baserev'], $cont_m, $cont_i, $editdata['acl_changed']]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 ACL 추가내용 기록 중 오류 발생');
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
            $cont_i = self::uuid2bin($editdata['contributor_i']);

        try {
            $db->query("DELETE FROM acl_document WHERE id=$id");
            $h_uuid = self::uuid2bin(self::generateUuid());
            $d = $db->prepare("INSERT INTO `history` (uuid, document, rev, `action`, contributor_m, contributor_i, acl_changed) VALUES(?,?,?,?,'acl',?,?,?)");
            $d->execute([$h_uuid, self::uuid2bin($editdata['uuid']), $editdata['baserev'], $cont_m, $cont_i, $editdata['acl_changed']]);
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

    /**
     * Get list of members in ACL Group.
     * @param string $group
     * @param mixed $from
     * @param mixed $until
     * @throws \ErrorException
     * @return array
     */
    public static function getAclgroupMembers(string $group, ?int $from, ?int $until): array
    {
        $db = self::db();
        if ($from !== null)
            $scope = 'AND id <= '.$from;
        elseif ($until !== null)
            $scope = 'AND id >= '.$until;
        else
            $scope = '';

        try {
            $d = $db->prepare("SELECT id, target_ip, mask, target_member, comment, `datetime`, until 
            FROM BlockHistory WHERE target_aclgroup=? AND (`until`>=unix_timestamp() OR `until`=0) $scope GROUP BY id HAVING COUNT(*) < 2 ORDER BY `datetime` DESC LIMIT 50");
            $d->execute([$group]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL그룹 구성원 조회 중 오류 발생');
        }

        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getAclgroupMinMaxIdx(string $group): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT MAX(b.id) AS max, MIN(b.id) AS min FROM BlockHistory b WHERE target_aclgroup=? AND (`until`>=unix_timestamp() OR `until`=0) GROUP BY b.id HAVING COUNT(*) < 2");
            $d->execute([$group]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL그룹 인덱스 조회 중 오류 발생');
        }
        
        return $d->fetch(PDO::FETCH_ASSOC);
    }

    public static function addtoGroup(?string $executor_m, ?string $executor_i, ?string $target_i, ?string $target_m, string $target_group, string $comment, int $until): void
    {
        $db = self::db();
        if (!empty($target_m)) {
            $target_m = self::uuid2bin($target_m);
            $ip = $mask = null;
        } elseif (!empty($target_i)) {
            $target_m = null;
            [$ip, $mask] = explode('/', $target_i);
            $ip = inet_pton($ip);
        }

        if (!empty($executor_m)) {
            $executor_m = self::uuid2bin($executor_m);
            $executor_i = null;
        } elseif (!empty($executor_i)) {
            $executor_i = self::uuid2bin($executor_i);
            $executor_m = null;
        }

        try {
            $d = $db->prepare("INSERT INTO BlockHistory (executor_m, executor_i, target_ip, mask, target_member, target_aclgroup, comment, until, `action`)
            VALUES(?,?,?,?,?,?,?,?,'aclgroup_add')");
            $d->execute([$executor_m, $executor_i, $ip, $mask, $target_m, $target_group, $comment, $until]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL그룹에 추가 중 오류 발생');
        }
    }

    /**
     * Get target ACL Group name by execution ID
     * @param int $id
     * @throws \ErrorException
     */
    public static function groupIdLookup(int $id): ?string
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT target_aclgroup FROM BlockHistory WHERE id=? AND (`until`>=unix_timestamp() OR `until`=0) GROUP BY id HAVING COUNT(*) < 2");
            $d->execute([$id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ID로 ACL그룹 조회 중 오류 발생');
        }
        
        return $d->fetch(PDO::FETCH_ASSOC)['target_aclgroup'];
    }

    public static function removefromGroup(?string $executor_m, ?string $executor_i, int $id, string $comment): void
    {
        $db = self::db();

        if (!empty($executor_m)) {
            $executor_m = self::uuid2bin($executor_m);
            $executor_i = null;
        } elseif (!empty($executor_i)) {
            $executor_i = self::uuid2bin($executor_i);
            $executor_m = null;
        }

        try {
            $d = $db->prepare("INSERT INTO BlockHistory (id, executor_m, executor_i, target_ip, mask, target_member, target_aclgroup, comment, `action`, until)
            SELECT ?,?,?,target_ip,mask,target_member,target_aclgroup,?,'aclgroup_remove',0 FROM BlockHistory WHERE id=?");
            $d->execute([$id, $executor_m, $executor_i, $comment, $id]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL그룹에서 제거 중 오류 발생');
        }
    }

    public static function addACLGroup(string $name): bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("INSERT INTO aclgroups (`name`) VALUES (?)");
            $d->execute([$name]);
        } catch (PDOException $err) {
            if ($err->getCode() == '23000') // duplicate input
                return false;
            else
                throw new ErrorException($err->getMessage().': ACL그룹 생성 중 오류 발생');
        }
        return true;
    }

    public static function deleteACLGroup(string $name): void
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM aclgroups WHERE `name`=?");
            $d->execute([$name]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL그룹 생성 중 오류 발생');
        }
    }

    public static function groupAddDuplicate(?string $uuid, ?string $cidr, string $group): bool
    {
        $db = self::db();

        if ($uuid !== null) {
            $uuid = self::uuid2bin($uuid);
            $sql = "SELECT 1 FROM BlockHistory WHERE target_member=? AND target_aclgroup=? AND (`until`>=unix_timestamp() OR `until`=0) GROUP BY id HAVING COUNT(*) < 2";
            $param = [$uuid, $group];
        } elseif ($cidr !== null) {
            [$ip, $mask] = explode('/', $cidr);
            $ip = inet_pton($ip);
            $sql = "SELECT 1 FROM BlockHistory WHERE target_ip=? AND mask=? AND target_aclgroup=? AND (`until`>=unix_timestamp() OR `until`=0) GROUP BY id HAVING COUNT(*) < 2";
            $param = [$ip, $mask, $group];
        }

        try {
            $d = $db->prepare($sql);
            $d->execute($param);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ACL그룹 중복 조회 중 오류 발생');
        }
        
        return $d->rowCount() > 0;
    }
}