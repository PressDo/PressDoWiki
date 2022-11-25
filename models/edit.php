<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function save_document(string $rawns, string $title, string $content, string $comment, $id, $ip, int $baserev, int $prevlen): void
    {
        $db = self::db();
        $cnt = iconv_strlen($content)-$prevlen;
        $docid = self::get_doc_id($rawns,$title);

        try {
            $d = $db->query("UPDATE `document` SET `is_latest`='false' WHERE `docid`=$docid AND `is_latest`='true'");
            $g = $db->prepare("INSERT INTO `document`(docid, content, length, comment, datetime, action, rev, count, contributor_m, contributor_i, is_hidden,is_latest) VALUES(?,?,?,?,?,'modify',?,?,?,?,'false', 'true')");
            $g->execute([$docid, $content, iconv_strlen($content), $comment, $_SERVER['REQUEST_TIME'], $baserev+1, $cnt, $id, $ip]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 편집 저장 중 오류 발생');
        }
        unset($d, $g);
    }

    public static function create_document(string $rawns, string $title, string $content, string $comment, $id, $ip): void
    {
        $db = self::db();
        $cnt = iconv_strlen($content);

        try {
            $d = $db->prepare("INSERT INTO `live_document_list`(namespace,title) VALUES(?,?)");
            $d->execute([$rawns, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 생성 중 오류 발생');
        }

        $docid = self::get_doc_id($rawns,$title);

        try {
            $g = $db->prepare("INSERT INTO `document`(docid, content, length, comment, datetime, action, rev, count, contributor_m, contributor_i, is_hidden,is_latest) VALUES(?,?,?,?,?,'create','1',?,?,?,'false', 'true')");
            $g->execute([$docid, $content, iconv_strlen($content), $comment, $_SERVER['REQUEST_TIME'], $cnt, $id, $ip]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 저장 중 오류 발생');
        }
        unset($d,$g);
    }
}