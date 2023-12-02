<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function move_document($did, $from, $to, $id, $comment): void
    {
        $db = self::db();
        list($toNSraw, $toNS, $toT) = WikiPage::parse_title($to);
        list($fromNSraw, $fromNS, $fromT) = WikiPage::parse_title($from);
        $c = self::load($fromNSraw, $fromT); // 편집기록에 들어갈 문서 데이터

        $a = $db->prepare("UPDATE `live_document_list` SET `namespace`=?, `title`=? WHERE `docid`=?");
        $a->execute([$toNSraw, $toT, $did]);

        $d = [
            $did,
            $c['content'],
            $c['length'],
            $comment,
            $_SERVER['REQUEST_TIME'],
            'move',
            $c['rev'] + 1,
            0,
            $id,
            $from,
            $to
        ];

        $a = $db->query("UPDATE `document` SET `is_latest`='false' WHERE `docid`='".$did."' AND `is_latest`='true'");
        $b = $db->prepare("INSERT INTO `document`(docid,content,length,comment,datetime,action,rev,count,contributor,moved_from,moved_to) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        $b->execute($d);
    }
}