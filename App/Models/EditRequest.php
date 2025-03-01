<?php
namespace PressDo\App\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class EditRequest extends \PressDo\App\Core\Model
{
    /**
     * Create a new edit request.
     * @param string $slug
     * @param string $uuid
     * @param string $content
     * @param string $comment
     * @param mixed $cont_m
     * @param mixed $cont_i
     * @param int $rv
     * @throws \ErrorException
     * @return void
     */
    public static function create(string $slug, string $uuid, string $content, string $comment, ?string $cont_m, ?string $cont_i, int $rv): void
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);

        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin($cont_i);

        try {
            $e = $db->prepare("INSERT INTO `editrequest` (urlstr,document,comment,content,contributor_m,contributor_i,baserev) VALUES(?,?,?,?,?,?,?)");
            $e->execute([$slug, $uuid, $comment, $content, $cont_m, $cont_i, $rv]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 생성 중 오류 발생');
        }
    }
    
    /**
     * Get slug list of edit requests in given document.
     * @param string $uuid
     * @param mixed $mode
     * @throws \ErrorException
     * @return array
     */
    public static function getBulk(string $uuid, $mode='open'): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            if($mode === 'open'){
                $d = $db->prepare("SELECT urlstr FROM `editrequest` WHERE `document`=? AND `status`='open'");
            }elseif($mode === 'close'){
                $d = $db->prepare("SELECT urlstr FROM `editrequest` WHERE `document`=? AND (`status`='close' OR `status`='locked')");
            }
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 목록 조회 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get data about given edit request.
     * @param string $slug
     * @throws \ErrorException
     */
    public static function get(string $slug): ?array
    {
        $db = self::db();
        
        try {
            $d = $db->prepare("SELECT d.namespace, d.title, document, er.status, count, comment, content, contributor_m, contributor_i, baserev, datetime, lastedit, executor_m, executor_i, acceptrev, reason
                FROM `editrequest` er INNER JOIN document d ON d.uuid = document WHERE `urlstr`=?");
            $d->execute([$slug]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 정보 조회 중 오류 발생');
        }
        if ($d->rowCount() < 1)
            return null;
        else {
            $res = $d->fetch(PDO::FETCH_ASSOC);
            $res['document'] = self::bin2uuid($res['document']);
            return $res;
        }
    }
    
    /**
     * Modify given edit request.
     * @param string $slug
     * @param string $content
     * @param string $comment
     * @param int $newdifflen
     * @throws \ErrorException
     * @return void
     */
    public static function modify(string $slug, string $content, string $comment, int $newdifflen): void
    {
        $db = self::db();
        
        try {
            $d = $db->prepare("UPDATE `editrequest` SET content=?, comment=?, count=?, lastedit=unix_timestamp() WHERE `urlstr`=?");
            $d->execute([$content, $comment, $newdifflen, $slug]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 수정 중 오류 발생');
        }
    }
    
    public static function accept(string $slug, array $erdata, ?string $cont_m, ?string $cont_i, int $acceptrev): void
    {
        $db = self::db();
        $uuid = self::uuid2bin(self::generateUuid());

        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin($cont_i);
        
        try {
            $d = $db->prepare("UPDATE `editrequest` SET `status`='accepted', acceptrev=?, lastedit=unix_timestamp(), executor_m=?, executor_i=? WHERE `urlstr`=?");
            $d->execute([$acceptrev, $cont_m, $cont_i, $slug]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 승인 중 오류 발생');
        }

        try {
            $d = $db->prepare("INSERT INTO `history` (uuid, document, content, comment, action, rev, count, contributor_m, contributor_i, edit_request_uri) VALUES(?,?,?,?,'modify',?,?,?,?,?)");
            $d->execute([$uuid, self::uuid2bin($erdata['document']), $erdata['content'], $erdata['comment'], $acceptrev, $erdata['count'], $erdata['contributor_m'], $erdata['contributor_i'], $slug]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 저장 중 오류 발생');
        }
    }
    
    /**
     * Close or lock given edit request.
     * @param string $slug
     * @param string $reason
     * @param mixed $cont_m
     * @param mixed $cont_i
     * @param bool $lock        edit request will be locked if true
     * @throws \ErrorException
     * @return void
     */
    public static function close(string $slug, string $reason, ?string $cont_m, ?string $cont_i, bool $lock=false): void
    {
        $db = self::db();

        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin($cont_i);

        $status = $lock ? 'locked' : 'close';
        
        try {
            $d = $db->prepare("UPDATE `editrequest` SET `status`=?, lastedit=unix_timestamp(), executor_m=?, executor_i=?, reason=? WHERE `urlstr`=?");
            $d->execute([$status, $cont_m, $cont_i, $reason, $slug]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 닫기기 중 오류 발생');
        }
    }

    public static function reopen(string $slug): void
    {
        $db = self::db();
        
        try {
            $d = $db->prepare("UPDATE `editrequest` SET `status`='open', lastedit=unix_timestamp() WHERE `urlstr`=?");
            $d->execute([$slug]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 편집 요청 열기기 중 오류 발생');
        }
    }
}