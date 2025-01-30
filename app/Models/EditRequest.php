<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class EditRequest extends \PressDo\app\Core\Model
{
    public static function new($ns, $t, $con, $com, $id, $ip, $rv, $url)
    {
        global $db;
        $d = $db->prepare("SELECT `docid` FROM `document` WHERE BINARY `namespace`=? AND BINARY `title`=?");
        $d->execute([$ns,$t]);
        $docid = $d->fetch()['docid'];
        $e = $db->prepare("INSERT INTO `edit_request`(urlstr,docid,status,comment,content,contributor_m,contributor_i,base_revision,datetime,lastedit) VALUES(?,?,'open',?,?,?,?,?,?,?)");
        $e->execute([$url, $docid, $com, $con, $id, $ip, $rv, $_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME']]);
    }
    
    public static function get(string $uuid, $mode='normal'): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            if($mode === 'normal'){
                $d = $db->prepare("SELECT urlstr FROM `editrequest` WHERE `document`=? AND (`status`='normal' OR `status`='pause')");
            }elseif($mode === 'closed'){
                $d = $db->prepare("SELECT urlstr FROM `editrequest` WHERE `document`=? AND `status`='close'");
            }
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 편집 요청 목록 조회 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function modify()
    {
    
    }
    
    public static function accept()
    {
    
    }
    
    public static function close()
    {
    
    }
}