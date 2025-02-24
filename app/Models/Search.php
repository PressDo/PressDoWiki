<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Search extends \PressDo\app\Core\Model
{
    public static function updateIndex(string $uuid, string $text): void
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        try {
            $d = $db->prepare("UPDATE search_index SET `text`=? WHERE document=?");
            $d->execute([$text, $uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 색인 업데이트 중 오류 발생');
        }
    }

    public static function softSearch(string $namespace, string $toplevel, string $midlevel, string $lowlevel): array
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `namespace`, `title` FROM document WHERE `namespace` = :ns AND (`title` REGEXP :q OR `title` REGEXP :c OR `title` REGEXP :r)
                ORDER BY CASE WHEN `title` REGEXP :q THEN 1 WHEN `title` REGEXP :c THEN 2 WHEN `title` REGEXP :r THEN 3 END, title ASC LIMIT 10");
            $d->execute(['ns' => $namespace, 'q' => $toplevel, 'c' => $midlevel, 'r' => $lowlevel]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서명 검색 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function hardSearch(string $keystring, string $target, ?string $namespace): array
    {
        $db = self::db();
        try {
            $db->query("SHOW VARIABLES LIKE 'innodb_ft_min_token_size'");
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': InnoDB 매개변수 확인 중 오류 발생');
        }

        $sql = "SELECT d.namespace, d.title, s.text FROM search_index s JOIN document d ON d.uuid = s.document WHERE";
        $args = [$keystring];
        switch ($target) {
            case 'title_content':
                $sql .= ' MATCH(`text`) AGAINST(? IN BOOLEAN MODE) OR ';
                array_push($args, $keystring);
                // no break
            case 'title':
                $sql .= 'd.title = ?'; // 제목에도 IN BOOLEAN 적용 필요
                break;
            case 'content':
                $sql .= ' MATCH(`text`) AGAINST(? IN BOOLEAN MODE)';
                break;
            case 'raw':
                // raw 검색 구현 필요
        }

        if (!empty($namespace)){
            $sql .= " AND d.namespace = ?";
            array_push($args, $namespace);
        }

        try {
            $c = $db->prepare($sql);
            $c->execute($args);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 검색 실행 중 오류 발생');
        }
        return $c->fetchAll(PDO::FETCH_ASSOC);
    }
}