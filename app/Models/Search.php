<?php
namespace PressDo\app\Models;

use \PDO as PDO;
use \PDOException as PDOException;
use \ErrorException as ErrorException;

class Search extends \PressDo\app\Core\Model
{
    public static function Search(string $keyword): array
    {
        /*
        * 검색 엔진 제작 시 참고사항
        * Whitespace로 쪼개 LIKE %a% 형태로 OR 검색
        * 데이터 많아지면 검색구간 분할
        * 역색인 결과의 교집합을 찾을 것

        Inverted index 구조 개요
        - 편집 후 저장 시 HTML에서 문법요소가 아닌 모든 단어 추출
        - Words 배열로 반환
        - 각각의 단어를 DB에 저장

        - 주석의 경우는 삽입 위치로 원문 이동

        - 편집 후 저장 시 모든 형태의 링크 추출(include, redirect 포함)
        - Links 배열로 반환
        - 역링크 테이블에 추가
        */
        global $db, $DB_ASSOC;
        $len = strlen($keyword);
        $words = [];
        $thisword = '';
        $sqlstr = '';
        $quotopen = false;
        $append_plus = false;
        $resSet = [];

        for($i=0; $i<$len; ++$i):
            $s = $keyword[$i];
            switch($s){
                case '"':
                    if($quotopen === false)
                        $quotopen = true;
                    elseif($quotopen === true)
                        $quotopen = false;
                    break;
                case ' ':
                    if($quotopen === false){
                        if($append_plus === true){
                            $append_plus = false;
                            $sqlstr .= " `content` LIKE ?) OR";
                        }else
                            $sqlstr .= " `content` LIKE ? OR";
                        array_push($words, '%'.$thisword.'%');
                        $thisword = '';
                    }else
                        $thisword .= $s;
                    break;
                case '+':
                    if($quotopen === false){
                        if($append_plus === true){
                            $sqlstr .= " `content` LIKE ? AND";
                        }else{
                            $append_plus = true;
                            $sqlstr .= " (`content` LIKE ? AND";
                        }
                        array_push($words, '%'.$thisword.'%');
                        $thisword = '';
                    }else
                        $thisword .= $s;
                    break;
                default:
                    $thisword .= $s;
            }
        endfor;
        if($append_plus === true){
            $sqlstr .= " `content` LIKE ?)";
        }else
            $sqlstr .= " `content` LIKE ?";
        array_push($words, '%'.$thisword.'%');

        $c = $db->prepare("SELECT `docid` FROM `history` WHERE `is_latest`='true' AND $sqlstr");
        $c->execute($words);
        $r = $c->fetchAll($DB_ASSOC);
        return $resSet;
    }
}