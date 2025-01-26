<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function save_document(string $uuid, string $content, string $comment, string|null $cont_m, string|null $cont_i, int $baserev, int $prevlen, string $action='modify'): void
    {
        $db = self::db();
        $cnt = iconv_strlen($content)-$prevlen;
        
        if($cont_m !== null)
            $cont_m = self::uuid2bin($cont_m);
        elseif($cont_i !== null)
            $cont_i = self::uuid2bin(self::get_ip_uuid($cont_i));

        $uuid = self::uuid2bin($uuid);

        try {
            $g = $db->prepare("INSERT INTO `history`(uuid, document, content, comment, datetime, action, rev, count, contributor_m, contributor_i) VALUES(?,?,?,?,?,?,?,?,?,?,?)");
            $g->execute([self::uuid2bin(self::uuid_generate()), $uuid, $content, $comment, $_SERVER['REQUEST_TIME'], $action, $baserev+1, $cnt, $cont_m, $cont_i]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 편집 저장 중 오류 발생');
        }
        unset($d, $g);
    }

    public static function create_document(string $namespace, string $title): string
    {
        $db = self::db();
        $uuid = self::uuid2bin(self::uuid_generate());

        try {
            $d = $db->prepare("INSERT INTO `document`(uuid,namespace,title) VALUES(?,?,?)");
            $d->execute([$uuid, $namespace, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 생성 중 오류 발생');
        }
        
        return self::bin2uuid($uuid);
    }
}