<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\app\Core\Controller;

class Backlink extends \PressDo\app\Core\Model
{
    /**
     * Get backlinks of document
     * @param string $namespace
     * @param string $title
     * @param string $target_ns
     * @param mixed $type
     * @throws \ErrorException
     * @return array
     */
    public static function get(string $namespace, string $title, string $target_ns, ?string $type=null): array
    {
        $db = self::db();
        $params = [$namespace, $title, $target_ns];

        if ($type !== null) {
            $typstr = ' AND links.type=?';
            array_push($params, $type);
        } else
            $typstr = '';

        try {
            $c = $db->prepare("SELECT document.title as `title`, document.namespace as `namespace`, links.type as `type` FROM `links`,`document` WHERE links.from_uuid = document.uuid AND links.namespace=? AND links.title=? AND document.namespace=?".$typstr);
            $c->execute($params);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 역링크 조회 중 오류 발생');
        }
        
        return $c->fetchAll(PDO::FETCH_GROUP);
    }

    /**
     * Count backlinks in each namespace
     * @param string $namespace
     * @param string $title
     * @throws \ErrorException
     * @return array
     */
    public static function count(string $namespace, string $title): array
    {
        $db = self::db();

        try {
            $c = $db->prepare("SELECT document.namespace as `namespace`, COUNT(from_uuid) as cnt FROM `links`,`document` WHERE links.from_uuid = document.uuid AND links.namespace=? AND links.title=? GROUP BY document.namespace");
            $c->execute([$namespace, $title]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 역링크 개수 확인 중 오류 발생');
        }
        
        return $c->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update backlinks
     * @param string $uuid
     * @param array $links
     * @throws \ErrorException
     * @return array
     */
    public static function update(string $uuid, array $links)
    {
        $db = self::db();
        $uuid = self::uuid2bin($uuid);
        $addvals = [];
        $parvals = [];
        if(count($links['redirect']) > 0){
            // 리다이렉트 문서 (링크가 항상 하나임)
            array_push($addvals, '(?,?,?,?)');
            list($namespace, $title) = Controller::parseTitle($links['redirect'][0]);
            array_push($parvals, $namespace, $title, $uuid, 'redirect');
        }else{
            foreach($links['link'] as $l){
                array_push($addvals, '(?,?,?,?)');
                list($namespace, $title) = Controller::parseTitle($l);
                array_push($parvals, $namespace, $title, $uuid, 'link');
            }
            foreach($links['file'] as $l){
                array_push($addvals, '(?,?,?,?)');
                list($namespace, $title) = Controller::parseTitle($l);
                array_push($parvals, $namespace, $title, $uuid, 'file');
            }
            foreach($links['include'] as $l){
                array_push($addvals, '(?,?,?,?)');
                list($namespace, $title) = Controller::parseTitle($l);
                array_push($parvals, $namespace, $title, $uuid, 'include');
            }
        }
        try {
            $d = $db->prepare("DELETE FROM `links` WHERE `from_uuid`=?");
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
            $d = $db->prepare("UPDATE `document` SET `backlink_updated`='1' WHERE `uuid`=?");
            $d->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 링크 갱신 반영 중 오류 발생');
        }
        return $d->fetchAll(PDO::FETCH_ASSOC);
    }
}