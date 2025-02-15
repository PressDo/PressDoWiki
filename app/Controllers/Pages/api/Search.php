<?php
namespace PressDo\app\Controllers\Pages\api;

use PressDo\app\Models\Document;
use PressDo\app\Core\Controller;

class Search extends Controller
{
    public function makeData(): never
    {
        [$namespace, $title] = self::parseTitle($_GET['q']);
        $searchPattern = '^';
        $chosungStr = '^';
        foreach (mb_str_split($title) as $t) {
            $searchPattern .= self::getChosungRange($t);
            $chosungStr .= self::getChosungRange(self::ko_head($t));
        }
        $search = Document::softSearch($namespace, $searchPattern, $chosungStr);
        $resultSet = [];
        
        foreach($search as $f){
            array_push($resultSet, ['namespace' => $f['namespace'], 'title' => $f['title'], 'forceShowNamespace' => self::forceShowNamespace($f['namespace'], $f['title'])]);
        }

        Header('Content-type: application/json; charset=utf-8');
        echo json_encode($resultSet, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function getChosungRange(string $char): string
    {
        $map = [
            'ㄱ' => '[가-깋]', 'ㄴ' => '[나-닣]', 'ㄷ' => '[다-딯]', 'ㄹ' => '[라-맇]',
            'ㅁ' => '[마-밓]', 'ㅂ' => '[바-빟]', 'ㅅ' => '[사-싷]', 'ㅇ' => '[아-잏]',
            'ㅈ' => '[자-짛]', 'ㅊ' => '[차-칳]', 'ㅋ' => '[카-킿]', 'ㅌ' => '[타-팋]',
            'ㅍ' => '[파-핗]', 'ㅎ' => '[하-힣]'
        ];
        return $map[$char] ?? $char;
    }
}