<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\app\Core\Controller;

class Document extends \PressDo\app\Core\Model
{
    /**
     * Create new document
     * @param string $namespace
     * @param string $title
     * @throws ErrorException
     * @return string UUID of created document
     */
    public static function create(string $namespace, string $title): string
    {
        $db = self::db();
        $uuid = self::uuid2bin(self::generateUuid());

        try {
            $d = $db->prepare("INSERT INTO `document`(uuid,namespace,title) VALUES(?,?,?)");
            $d->execute([$uuid, $namespace, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 생성 중 오류 발생');
        }
        
        return self::bin2uuid($uuid);
    }

    public static function recreate(string $uuid): void
    {
        $db = self::db();
        $uuid = self::uuid2bin(self::generateUuid());

        try {
            $d = $db->prepare("UPDATE `document` SET `status`='normal' WHERE uuid=?");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 재생성 중 오류 발생');
        }
    }

    /**
     * Load document data
     * @param string $uuid     uuid of document
     * @param ?string $rev     revision uuid of document
     * @throws ErrorException
     * @return ?array       array (history data)
     */
    public static function load(string $uuid, ?string $rev=null): ?array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $sql = "SELECT h.uuid,h.content,h.comment,h.datetime,h.action,h.rev,h.count,h.reverted_version,h.contributor_m,h.contributor_i, h.edit_request_uri,h.acl_changed,h.moved_from,h.moved_to,h.is_hidden, d.status
        FROM `history` as h INNER JOIN `document` as d ON d.uuid = h.document WHERE h.`document`=?";

        if ($rev === null) {
            $sql .= " AND h.`is_hidden`='false' ORDER BY h.`datetime` DESC LIMIT 1";
            $param = [$uuid];
        } else {
            $rev = self::uuid2bin($rev);
            $sql .= " AND h.uuid=? ";
            $param = [$uuid, $rev];
        }
        
        $d = $db->prepare($sql);

        try {
            $d->execute($param);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 데이터 조회 중 오류 발생');
        }

        if ($d->rowCount() < 1)
            $res = null;
        else {
            $res = $d->fetch(PDO::FETCH_ASSOC);
            if ($res['content'] == null && $res['rev'] !== 1 && $res['status'] !== 'delete') {
                $q = $db->prepare("SELECT content FROM history WHERE content IS NOT NULL AND document=? AND rev < ? ORDER BY `datetime` DESC LIMIT 1");
                try {
                    $q->execute([$uuid, $res['rev']]);
                } catch (PDOException $err) {
                    throw new ErrorException($err->getMessage().': 문서 본문 조회 중 오류 발생');
                }

                if ($q->rowCount() === 1)
                    $res['content'] = $q->fetch(PDO::FETCH_ASSOC)['content'];               
            }
        }

        # return null if not found
        return $res;
    }

    /**
     * Save edited Document
     * @param string $uuid
     * @param string $content
     * @param string $comment
     * @param ?string $cont_m
     * @param ?string $cont_i
     * @param int $baserev
     * @param int $prevlen
     * @param string $action
     * @throws ErrorException
     * @return void
     */
    public static function save(string $uuid, string $content, string $comment, ?string $cont_m, ?string $cont_i, int $baserev, int $prevlen, string $action): void
    {
        $db = self::db();
        $cnt = iconv_strlen($content)-$prevlen;
        
        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin($cont_i);

        $uuid = self::uuid2bin($uuid);

        try {
            $g = $db->prepare("INSERT INTO `history`(uuid, document, content, comment, datetime, action, rev, count, contributor_m, contributor_i) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $g->execute([self::uuid2bin(self::generateUuid()), $uuid, $content, $comment, $_SERVER['REQUEST_TIME'], $action, $baserev+1, $cnt, $cont_m, $cont_i]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 편집 저장 중 오류 발생');
        }
    }

    /**
     * Move document
     * @param string $uuid
     * @param string $from
     * @param string $to
     * @param ?string $cont_m
     * @param ?string $cont_i
     * @param string $comment
     * @throws ErrorException
     * @return void
     */
    public static function move(string $uuid, string $from, string $to, ?string $cont_m, ?string $cont_i, int $baserev, string $comment): void
    {
        $db = self::db();

        [$toNS, $toT] = Controller::parseTitle($to);
        [$fromNS, $fromT] = Controller::parseTitle($from);

        $uuid = self::uuid2bin($uuid);

        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin($cont_i);

        $a = $db->prepare("UPDATE `document` SET `namespace`=?, `title`=? WHERE `uuid`=?");
        $a->execute([$toNS, $toT, $uuid]);

        $d = [
            self::uuid2bin(self::generateUuid()),
            $uuid,
            $comment,
            'move',
            $baserev + 1,
            0,
            $cont_m,
            $cont_i,
            $from,
            $to
        ];
        
        try {
            $b = $db->prepare("INSERT INTO `history`(uuid,document,comment,action,rev,count,contributor_m,contributor_i,moved_from,moved_to) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $b->execute($d);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 이동 중 오류 발생');
        }
    }

    public static function delete(string $uuid, ?string $cont_m, ?string $cont_i, int $length, int $baserev, string $comment): void
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);

        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin($cont_i);

        $a = $db->prepare("UPDATE `document` SET `status`='delete' WHERE `uuid`=?");
        $a->execute([$uuid]);

        $d = [
            self::uuid2bin(self::generateUuid()),
            $uuid,
            $comment,
            $baserev + 1,
            -$length,
            $cont_m,
            $cont_i
        ];
        
        try {
            $b = $db->prepare("INSERT INTO `history`(uuid,document,comment,action,rev,count,contributor_m,contributor_i) VALUES(?,?,?,'delete',?,?,?,?)");
            $b->execute($d);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 삭제 중 오류 발생');
        }
    }

    /**
     * Get UUID of the document.
     * @param   string $namespace     Document namespace (raw)
     * @param   string $title     Document Title
     * @return  int|bool       Document ID (false if not exist)
     */
    public static function getUuid($namespace, $title, &$backlinkrefreshed = false): string|bool
    {
        $db = self::db();

        try {
            $c = $db->prepare("SELECT uuid FROM `document` WHERE `namespace`=? AND BINARY `title`=?");
            $c->execute([$namespace, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 UUID 조회 중 오류 발생');
        }

        $f = $c->fetch(PDO::FETCH_ASSOC);
        if ($c->rowCount() < 1)
            return false;
        else {
            $backlinkrefreshed = boolval($f['backlink_updated']);
            return self::bin2uuid($f['uuid']);
        }
    }

    /**
     * Get title of the document by ID.
     *
     * @param   array $ids     Document BINARY UUID (least 1 doc)
     * @return  array       Document namespace, title
     */
    public static function getBulkTitle(array $ids): array
    {
        $db = self::db();
        try {
            $ORSTATEMENT = str_repeat(',?', count($ids) - 1);
            $c = $db->prepare("SELECT `docid`,`namespace`,`title` FROM `document` WHERE `docid` IN (?".$ORSTATEMENT.")");
            $c->execute($ids);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ID로 문서명 조회 중 오류 발생');
        }

        $res = $c->fetchAll(PDO::FETCH_ASSOC);
        
        return $res;
    }

    /**
     * Get title of the document by UUID.
     *
     * @param   string $uuid     Document UUID
     * @return  array|false       Document namespace, title. returns false if not exist.
     */
    public static function getTitleByUuid(string $uuid): array|false
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            $c = $db->prepare("SELECT `namespace`,`title`, `status` FROM `document` WHERE `uuid`=?");
            $c->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': ID로 문서명 조회 중 오류 발생');
        }

        $res = $c->rowCount() < 1 ? false : $c->fetch(PDO::FETCH_ASSOC);
        return $res;
    }

    /**
     * Get list of random documents
     * @param string $namespace
     * @param int $quantity
     * @throws \ErrorException
     * @return array
     */
    public static function getRandom(string $namespace='문서', int $quantity=1): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `namespace`,`title` FROM `document` WHERE `namespace`=? ORDER BY RAND() LIMIT ".$quantity);
            $d->execute([$namespace]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 무작위 문서 불러오기 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getOldestPages(int $from, int $count): array
    {
        $db = self::db();
        --$from;
        ++$count;
        
        try {
            $d = $db->query("SELECT d.namespace, d.title, MAX(h.`datetime`) as dt FROM `history` as h INNER JOIN `document` as d ON d.uuid = h.document
                WHERE NOT EXISTS (SELECT 1 FROM links as l WHERE l.from_uuid = h.document AND l.type = 'redirect') AND d.namespace != '사용자' GROUP BY h.`document` ORDER BY dt ASC LIMIT $from, $count");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 오래된 문서 불러오기 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getPagesByLength(string $order, int $from, int $count): array
    {
        $db = self::db();
        --$from;
        ++$count;
        
        $sql = "SELECT d.namespace, d.title, len FROM
            (SELECT document, `datetime`, CHAR_LENGTH(`content`) as len,
                RANK() OVER (PARTITION BY document ORDER BY `datetime` DESC) AS rnk FROM `history`
            ) AS h INNER JOIN `document` as d ON d.uuid = document WHERE d.namespace = '문서' AND rnk = 1 AND NOT EXISTS
            (SELECT 1 FROM links as l WHERE l.from_uuid = document AND l.type = 'redirect') GROUP BY `document` ORDER BY len $order LIMIT $from, $count";
        try {
            $d = $db->query($sql);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 길이순으로 문서 불러오기 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getNeededPages(string $namespace, int $from, int $count): array
    {
        $db = self::db();
        --$from;
        ++$count;
        try {
            $d = $db->prepare("SELECT l.namespace, l.title FROM `links` as l WHERE l.namespace = ? AND NOT EXISTS (SELECT 1 FROM document as d WHERE d.namespace = l.namespace AND d.title = l.title) LIMIT $from, $count");
            $d->execute([$namespace]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 작성이 필요한 문서 불러오기 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUncategorizedPages(string $namespace, int $from, int $count): array
    {
        $db = self::db();
        --$from;
        ++$count;
        try {
            $d = $db->prepare("SELECT d.namespace, d.title FROM document as d WHERE d.namespace = ? AND NOT EXISTS (SELECT 1 FROM links as l WHERE d.uuid = l.from_uuid AND l.type = 'category') ORDER BY d.title ASC LIMIT $from, $count");
            $d->execute([$namespace]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 분류되지 않은 문서 불러오기 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getOrphanedPages(string $namespace, string $fpuuid, int $from, int $count): array
    {
        $db = self::db();
        --$from;
        ++$count;
        try {
            $d = $db->prepare("SELECT d.namespace, d.title FROM document as d WHERE d.namespace = ? AND NOT EXISTS (SELECT 1 FROM links as l WHERE d.uuid = l.from_uuid AND l.type = 'category') ORDER BY d.title ASC LIMIT $from, $count");
            $d->execute([$namespace]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 분류되지 않은 문서 불러오기 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getLicensesAndCategories(): array
    {
        $db = self::db();
        try {
            $d = $db->query("SELECT title FROM document WHERE `namespace` = '틀' AND title LIKE '이미지 라이선스/%' AND title != '이미지 라이선스/'");
            $license = $d->fetchAll(PDO::FETCH_ASSOC);
            $d = $db->query("SELECT title FROM document WHERE `namespace` = '분류' AND title LIKE '파일/%' AND title != '파일/'");
            $category = $d->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서명 검색 중 오류 발생');
        }
        return ['License' => $license, 'Category' => $category];
    }
}