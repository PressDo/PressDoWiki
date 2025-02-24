<?php
namespace PressDo\app\Controllers\Pages\api;

use PressDo\app\Models\Search as S;
use PressDo\app\Core\Controller;

class Search extends Controller
{
    private const CSRANGE = [
        'ㄱ' => '[가-깋]', 'ㄲ' => '[까-낗]', 'ㄴ' => '[나-닣]', 'ㄷ' => '[다-딯]', 'ㄸ' => '[따-띻]',
        'ㄹ' => '[라-맇]', 'ㅁ' => '[마-밓]', 'ㅂ' => '[바-빟]', 'ㅃ' => '[빠-삫]', 'ㅅ' => '[사-싷]',
        'ㅆ' => '[싸-앃]', 'ㅇ' => '[아-잏]', 'ㅈ' => '[자-짛]', 'ㅉ' => '[짜-찧]', 'ㅊ' => '[차-칳]',
        'ㅋ' => '[카-킿]', 'ㅌ' => '[타-팋]', 'ㅍ' => '[파-핗]', 'ㅎ' => '[하-힣]'
    ];
    
    public function makeData(): never
    {
        [$namespace, $title] = self::parseTitle($_GET['q']);

        $toplevel = '^'; // 고수준 일치: 가나닷~ (완전일치)
        $midlevel = '^'; // 중수준 일치: 가나닷 -> 가나다ㅅ
        $lowlevel = '^'; // 저수준 일치: 가나닷 -> ㄱㄴㄷ~
        $charset = mb_str_split($title);

        foreach ($charset as $k => $t) {
            if (self::is_hangeul($t)) {
                $th = self::ko_head($t);
                $lowlevel .= self::CSRANGE[$th] ?? $th;

                // 받침 있는 마지막 글자만 종성 추출
                if ($k === count($charset) - 1 && (self::utf8_ord($t) - 44032) % 28 > 0) {
                    $toplevel .= self::CSRANGE[$t] ?? $t;
                    $midlevel .= self::ko_exceptlast($t).self::CSRANGE[self::ko_last($t)];
                } else {
                    $toplevel .= self::CSRANGE[$t] ?? $t;
                    $midlevel = $toplevel;
                }
            } else {
                $toplevel .= $t;
                $midlevel = $lowlevel = $toplevel;
            }
        }
        $search = S::softSearch($namespace, $toplevel, $midlevel, $lowlevel);
        $resultSet = [];
        
        foreach($search as $f){
            array_push($resultSet, ['namespace' => $f['namespace'], 'title' => $f['title'], 'forceShowNamespace' => self::forceShowNamespace($f['namespace'], $f['title'])]);
        }

        Header('Content-type: application/json; charset=utf-8');
        echo json_encode($resultSet, JSON_UNESCAPED_UNICODE);
        exit;
    }
}