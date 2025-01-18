<?php
namespace PressDo;
require 'models/common.php';

use ErrorException;
use PDOException;

class Models extends baseModels
{
    /**
     * get boolean if document is starred
     * 
     * @param int $uuid        Document ID
     * @param string $username  username
     * @return bool
     */
    public static function if_starred(int $uuid, string $username): bool
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `document`=? AND `user`=?");
            $d->execute([$uuid, $username]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 별표여부 조회 중 오류 발생');
        }
        $bool = ($d->rowCount() < 1)? false : true;
        return $bool;
    }

    /**
     * get numbers of star in this document
     * 
     * @param int $uuid        Document UUID
     * @return int
     */
    public static function count_stars(string $uuid): int
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT count(*) as cnt FROM `starred` WHERE `document`=?");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 별표개수 조회 중 오류 발생');
        }
        return intval($d->fetch()['cnt']);
    }

    public static function get_forlinks(string $uuid)
    {
        $db = self::db();
        try {
            $d = $db->prepare("SELECT `type`, `namespace`, `title` FROM `links` WHERE `from_uuid`=UNHEX(?)");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 순링크 조회 중 오류 발생');
        }
        return $d->fetchAll(\PDO::FETCH_GROUP);
    }

    public static function update_forlinks(string $uuid, array $links)
    {
        $db = self::db();
        $addvals = [];
        $parvals = [];
        if(count($links['redirect']) > 0){
            // 리다이렉트 문서 (링크가 항상 하나임)
            array_push($addvals, '(?,?,UNHEX(?),?)');
            list($namespace, $title) = WikiPage::parse_title($links['redirect'][0]);
            array_push($parvals, $namespace, $title, $uuid, 'redirect');
        }else{
            foreach($links['link'] as $l){
                array_push($addvals, '(?,?,UNHEX(?),?)');
                list($namespace, $title) = WikiPage::parse_title($l);
                array_push($parvals, $namespace, $title, $uuid, 'link');
            }
            foreach($links['file'] as $l){
                array_push($addvals, '(?,?,UNHEX(?),?)');
                list($namespace, $title) = WikiPage::parse_title($l);
                array_push($parvals, $namespace, $title, $uuid, 'file');
            }
            foreach($links['include'] as $l){
                array_push($addvals, '(?,?,UNHEX(?),?)');
                list($namespace, $title) = WikiPage::parse_title($l);
                array_push($parvals, $namespace, $title, $uuid, 'include');
            }
        }
        try {
            $d = $db->prepare("DELETE FROM `links` WHERE `from_uuid`=UNHEX(?)");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 기존 순링크 삭제 중 오류 발생');
        }
        try {
            $d = $db->prepare("INSERT INTO `links`(namespace, title, from_uuid, type) VALUES".implode(', ', $addvals));
            $d->execute($parvals);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 순링크 갱신 중 오류 발생');
        }
        try {
            $d = $db->prepare("UPDATE `document` SET `backlink_updated`='1' WHERE `uuid`=UNHEX(?)");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 링크 갱신 반영 중 오류 발생');
        }
        return $d->fetchAll(\PDO::FETCH_ASSOC);
    }
}