<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function move_document($uuid, $from, $to, $cont_m, $cont_i, $comment): void
    {
        $db = self::db();
        [$toNS, $toT] = WikiPage::parse_title($to);
        [$fromNS, $fromT] = WikiPage::parse_title($from);
        $c = self::load($fromNS, $fromT); // 편집기록에 들어갈 문서 데이터

        $a = $db->prepare("UPDATE `document` SET `namespace`=?, `title`=? WHERE `uuid`=?");
        $a->execute([$toNS, $toT, $uuid]);

        $d = [
            self::uuid2bin(self::uuid_generate()),
            $uuid,
            $comment,
            $_SERVER['REQUEST_TIME'],
            'move',
            $c['rev'] + 1,
            0,
            $cont_m,
            $cont_i,
            $from,
            $to
        ];

        //$a = $db->query("UPDATE `history` SET `is_latest`='false' WHERE `docid`='".$did."' AND `is_latest`='true'");
        $b = $db->prepare("INSERT INTO `history`(uuid,document,comment,datetime,action,rev,count,contributor_m,contributor_i,moved_from,moved_to) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
        $b->execute($d);
    }
}