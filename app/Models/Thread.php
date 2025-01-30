<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Thread extends \PressDo\app\Core\Model
{
    /**
     * Get uuid, topic, status and initial committer.
     * @param string $urlstr
     * @return array
     */
    public static function getInfo(string $urlstr): array
    {
        $db = self::db();
        $d = $db->prepare("SELECT `document`,topic,`status` FROM `thread` WHERE `urlstr`=?");
        $d->execute([$urlstr]);
        $dr = $d->fetch(PDO::FETCH_ASSOC);
        $dr['document'] = self::bin2uuid($dr['document']);
        $e = $db->prepare("SELECT contributor_i,contributor_m FROM `thread_content` WHERE `urlstr`=? AND `no`='1'");
        $e->execute([$urlstr]);
        return $dr + $e->fetch(PDO::FETCH_ASSOC);
    }

    public static function getComments(string $urlstr): array
    {
        $db = self::db();
        $b = $db->prepare("SELECT `no`,contributor_i,contributor_m,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 30");
        $b->execute([$urlstr]);
        $arr = array_reverse($b->fetchAll(PDO::FETCH_ASSOC));
        return $arr;
    }

    public static function recentDiscuss(string $from='thread', string $status='normal', string $order='DESC'): array
    {
        $db = self::db();

        try {
            $a = $db->query("SELECT * FROM `$from` WHERE `status`='".$status."' ORDER BY `last_comment` ".$order." LIMIT 100");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 최근 토론 조회 중 오류 발생');
        }
        return $a->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getLatestComments(string $urlstr): array
    {
        $db = self::db();
        $b = $db->prepare("SELECT `no`,contributor_m,contributor_i,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 3");
        $b->execute([$urlstr]);
        $arr = array_reverse($b->fetchAll(PDO::FETCH_ASSOC));

        // display first comment when there're more than 3 comments
        if ($arr[0]['no'] !== '1' && $b->rowCount() == '3') {
            $c = $db->prepare("SELECT `no`,contributor_m,contributor_i,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? AND `no`='1'");
            $c->execute([$urlstr]);
            $arr = array_merge([$c->fetch(PDO::FETCH_ASSOC)],$arr);
        }
        return $arr;
    }
}