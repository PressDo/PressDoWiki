<?php
namespace PressDo\App\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Thread extends \PressDo\App\Core\Model
{
    /**
     * Get uuid, topic, status and initial committer.
     * @param string $urlstr
     * @return array
     */
    public static function getInfo(string $urlstr): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT t.`document`,t.topic,t.`status`, c.contributor_m as init_m, c.contributor_i as init_i FROM `thread` t 
                JOIN thread_content c ON t.urlstr = c.urlstr WHERE t.`urlstr`= ? AND c.no = '1'");
            $d->execute([$urlstr]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 토론 정보 조회 중 오류 발생');
        }
        return $d->fetch(PDO::FETCH_ASSOC);
    }

    public static function getComments(string $urlstr): array
    {
        $db = self::db();
        try {
            $b = $db->prepare("SELECT `no`,contributor_i,contributor_m,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 30");
            $b->execute([$urlstr]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 토론 댓글 조회 중 오류 발생');
        }
        $arr = array_reverse($b->fetchAll(PDO::FETCH_ASSOC));
        return $arr;
    }

    public static function recentDiscuss(string $from='thread', string $status='normal', string $order='DESC'): array
    {
        $db = self::db();

        if ($status == 'close')
            $locked = "OR `status`='locked'";
        else
            $locked = '';

        if ($from == 'editrequest') {
            $sql = "SELECT urlstr, document, contributor_m, contributor_i, `lastedit`, count FROM editrequest WHERE `status`=? ORDER BY `datetime` DESC LIMIT 100";
        } elseif ($from == 'thread') {
            $sql = "SELECT t.urlstr, t.document, t.topic, `datetime` as last_comment, contributor_m, contributor_i, rnk FROM 
                (SELECT urlstr, `datetime`, contributor_m, contributor_i, RANK() OVER (PARTITION BY urlstr ORDER BY `datetime` DESC) as rnk FROM thread_content) as x
                INNER JOIN `thread` AS t ON t.urlstr = x.urlstr 
                WHERE `status`=? $locked AND `rnk` = 1 ORDER BY `last_comment` $order LIMIT 100";
        }

        try {
            $a = $db->prepare($sql);
            $a->execute([$status]);
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