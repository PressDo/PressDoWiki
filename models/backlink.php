<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    public static function get_backlink(string $namespace, string $title, string $target_ns, $type=null)
    {
        $db = self::db();
        $params = [$namespace, $title, $target_ns];

        if($type !== null){
            $typstr = ' AND links.type=?';
            array_push($params, $type);
        }else
            $typstr = '';

        try {
            $c = $db->prepare("SELECT document.title as `title`, document.namespace as `namespace`, links.type as `type` FROM `links`,`document` WHERE links.from_uuid = document.uuid AND links.namespace=? AND links.title=? AND document.namespace=?".$typstr);
            $c->execute($params);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 역링크 조회 중 오류 발생');
        }

        $res = $c->fetchAll(\PDO::FETCH_GROUP);
        
        return $res;
    }

    public static function count_backlink(string $namespace, string $title)
    {
        $db = self::db();

        try {
            $c = $db->prepare("SELECT document.namespace as `namespace`, COUNT(from_uuid) as cnt FROM `links`,`document` WHERE links.from_uuid = document.uuid AND links.namespace=? AND links.title=? GROUP BY document.namespace");
            $c->execute([$namespace, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 역링크 개수 확인 중 오류 발생');
        }

        $res = $c->fetchAll(\PDO::FETCH_ASSOC);
        
        return $res;
    }
}