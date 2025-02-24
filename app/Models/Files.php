<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;
use PressDo\app\Core\Controller;

class Files extends \PressDo\app\Core\Model
{
    public static function save(string $fileuuid, string $filehash, int $width, int $height): void
    {
        $db = self::db();
        $hash = hex2bin($filehash);
        $uuid = self::uuid2bin($fileuuid);

        try {
            $g = $db->prepare("INSERT INTO `files`(uuid, `hash`, width, height) VALUES (?,?,?,?)");
            $g->execute([$uuid, $hash, $width, $height]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 파일 데이터 저장 중 오류 발생');
        }
    }

    public static function load(string $fileuuid): array
    {
        $db = self::db();
        $uuid = self::uuid2bin($fileuuid);

        try {
            $g = $db->prepare("SELECT `hash`, width, height FROM `files` WHERE uuid=?");
            $g->execute([$uuid]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 파일 데이터 저장 중 오류 발생');
        }
        $dataset = $g->fetch(PDO::FETCH_ASSOC);
        $dataset['hash'] = bin2hex($dataset['hash']);
        return $dataset;
    }

    /**
     * 파일 해시가 이미 존재하는지 확인
     * @param string $hash
     * @throws \ErrorException
     * @return bool
     */
    public static function findHash(string $hash): bool
    {
        $db = self::db();
        $H = hex2bin($hash);

        try {
            $g = $db->prepare("SELECT 1 FROM `files` WHERE `hash`=?");
            $g->execute([$H]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 파일 해시 조회 중 오류 발생');
        }
        return $g->rowCount() > 0;
    }
}