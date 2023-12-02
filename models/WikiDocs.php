<?php
namespace PressDo;

require 'helpers/DB.php';

use ErrorException;
use PDOException;
use \PDO as PDO;

class Models {
    private static $db = null;

    /**
     * Connect Database.
     * @return PDO
     */
    public static function db() : PDO
    {
        if(!self::$db){
            self::$db = \PressDo\DB::getInstance();
            return self::$db;
        }else
            return self::$db;
    }
    
    /**
     * add document to starred.
     * 
     * @param string $docid     ID of document
     * @param string $user      username
     * @return void
     */
    public static function star_document(int $docid, string $user) : void
    {
        $db = self::db();
        try {
            $d = $db->prepare("INSERT INTO `starred`(docid, user) VALUES(?,?)");
            $d->execute([$docid, $user]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표 중 오류 발생');
        }
    }

    /**
     * add document to starred.
     * 
     * @param string $docid     ID of document
     * @param string $user      username
     * @return void
     */
    public static function unstar_document(int $docid, string $user) : void
    {
        $db = self::db();
        try {
            $d = $db->prepare("DELETE FROM `starred` WHERE `docid`=? AND `user`=?");
            $d->execute([$docid, $user]);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 문서 별표해제 중 오류 발생');
        }
    }

    /**
     * get starred document list
     * 
     * @param string $username  username
     * @return array
     */
    public static function get_starred(string $username) : array
    {
        $db = self::db();
        try{
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `user`=?");
            $d->execute([$username]);
            return $d->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $err) {
            throw new ErrorException($err->getMessage().': 사용자 문서함 조회 중 오류 발생');
        }
    }
    
    public static function saveDocument(string $ns, string $t, string $con, string $com, string $act, $baserev,  int $prevlen, $id, $ip): void
    {
        global $db;
        $c_nt = iconv_strlen($con)-$prevlen;
        $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE BINARY `namespace`=? AND BINARY `title`=?");
        $d->execute([$ns,$t]);
        if($d->rowCount() < 1){
            $e = $db->prepare("INSERT INTO `live_document_list`(namespace,title) VALUES(?,?)");
            $e->execute([$ns,$t]);
            $f = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE BINARY `namespace`=? AND BINARY `title`=?");
            $f->execute([$ns,$t]);
            $did = $f->fetch()[0]; 
        }else
            $did = $d->fetch()[0];

        $a = $db->query("UPDATE `document` SET `is_latest`='false' WHERE `docid`=$did AND `is_latest`='true'");
        $g = $db->prepare("INSERT INTO `document`(docid, content, length, comment, datetime, action, rev, count, contributor_m, contributor_i, is_hidden,is_latest) VALUES(?,?,?,?,?,?,?,?,?,?,'false', 'true')");
        $g->execute([$did, $con, iconv_strlen($con), $com, $_SERVER['REQUEST_TIME'], $act, $baserev+1, $c_nt, $id, $ip]);
        unset($d, $e, $f, $g);
    }

    public function moveDocument($from, $to, $id, $ip, $comment): void
    {
        global $db;
        list($toNSraw, $toNS, $toT) = Docs::parseTitle($to);
        list($fromNSraw, $fromNS, $fromT) = Docs::parseTitle($from);
        $did = $this->get_doc_id($fromNSraw, $fromT);
        $c = Docs::loadDocument($fromNSraw, $fromT);
        if(!$did || $c['content'] === null || $c['action'] == 'delete')
            die;

        $a = $db->prepare("UPDATE `live_document_list` SET `namespace`=?, `title`=? WHERE `docid`=(SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND BINARY `title`=?)");
        $a->execute([$toNSraw, $toT, $fromNSraw, $fromT]);

        $d = [
            $did,
            $c['content'],
            $c['length'],
            $comment,
            $_SERVER['REQUEST_TIME'],
            'move',
            $c['rev'] + 1,
            0,
            $id===null?null:$id,
            $ip===null?null:$ip,
            $from,
            $to
        ];

        $a = $db->query("UPDATE `document` SET `is_latest`='false' WHERE `docid`='".$did."' AND `is_latest`='true'");
        $b = $db->prepare("INSERT INTO `document`(docid,content,length,comment,datetime,action,rev,count,contributor_m,contributor_i,moved_from,moved_to) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)");
        $b->execute($d);
        
    }

    public static function load_diff(string $old, string $new, $ov, $nv): string
    {
        $a = explode("\n", $old);
        $b = explode("\n", $new);

        $options = array(
            //'ignoreWhitespace' => true,
            //'ignoreCase' => true,
        );

        $diff = new Diff($a, $b, $options); 
        $ren = new Diff_Renderer_Html_Inline;
        $ren->oldrev = $ov;
        $ren->newrev = $nv;
        return $diff->render($ren);
    }
    
    public static function loadHistory($ns, $Title=null, $from=null,$until=null, $option=null): null | array
    {
        global $db;
        if($Title !== null){
            $nv = Docs::getVersion($ns, $Title);
            $c = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE BINARY `namespace`=? AND BINARY `title`=?");
            $c->execute([$ns, $Title]);
            if($c->rowCount() < 1) return null;
            
            $docid = $c->fetch()[0];

            if($from !== null)
                $str = 'DESC LIMIT '.$nv-$from.',';
            elseif($until !== null)
                $str = 'ASC LIMIT '.$until-2 .',';
            else
                $str = 'DESC LIMIT';
            

            $d = $db->prepare("SELECT `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` $str 31");
            $d->execute([$docid]);
            if($until !== null)
                $ra = array_reverse($d->fetchAll());
            else
                $ra = $d->fetchAll();
        }else{
            $d = $db->query("SELECT `docid`, `length`, `comment`, `action`, `rev`, `count`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri` FROM `document` WHERE BINARY `is_hidden`='false' $option ORDER BY `datetime` DESC LIMIT 100");
            $ra = $d->fetchAll();
        }
        return $ra;
    }
    
    public static function hideHistory($docid, $timestamp)
    {
        global $db;
        $d = $db->prepare("UPDATE `document` SET `is_hidden`='true' WHERE `docid`=? AND `datetime`=?");
        $d->execute([$docid, $timestamp]);
        unset($d);
    }
    
    public static function unhideHistory($docid, $timestamp)
    {
        global $db;
        $d = $db->prepare("UPDATE `document` SET `is_hidden`='true' WHERE `docid`=? AND `datetime`=?");
        $d->execute([$docid,$timestamp]);
        unset($d);
    }
    
    public static function getRandom($n=null, $ns='document', $theme=null)
    {
        global $db, $DB_ASSOC;
        $add = [
            null => '',
            'l' => ", d.length",
            's' => ", d.length",
            'o' => ", d.datetime",
        ];
        $query = [
            null => "RAND() LIMIT $n",
            'l' => "d.length DESC LIMIT 100",
            's' => "d.length LIMIT 100",
            'o' => "d.datetime LIMIT 100",
        ];
        $d = $db->prepare("SELECT l.namespace, l.title".$add[$theme]." FROM `document` AS d INNER JOIN live_document_list AS l ON d.docid = l.docid WHERE d.is_latest='true' AND l.namespace=? ORDER BY ".$query[$theme]);
        $d->execute([$ns]);
        return $d->fetchAll($DB_ASSOC);
    }

    public static function getUploadInfo(): array
    {
        global $db, $DB_ASSOC, $_ns;
        $category = $license = [];
        $l = $db->query("SELECT `namespace`,display_name,`name` FROM uploader_license_category WHERE `type`='license'")->fetchAll($DB_ASSOC);
        foreach($l as $li):
            $license[$li['display_name']] = [
                'namespace' => $_ns[$li['namespace']],
                'name' => $li['name']
            ];
        endforeach;
        $c = $db->query("SELECT `namespace`,display_name,`name` FROM uploader_license_category WHERE `type`='category'")->fetchAll($DB_ASSOC);
        foreach($c as $ct):
            $category[$ct['display_name']] = [
                'namespace' => $_ns[$ct['namespace']],
                'name' => $ct['name']
            ];
        endforeach;
        return ['License' => $license, 'Category' => $category];
    }
    
    public static function editRequest($ns, $t, $con, $com, $id, $ip, $rv, $url)
    {
        global $db;
        $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE BINARY `namespace`=? AND BINARY `title`=?");
        $d->execute([$ns,$t]);
        $docid = $d->fetch()['docid'];
        $e = $db->prepare("INSERT INTO `edit_request`(urlstr,docid,status,comment,content,contributor_m,contributor_i,base_revision,datetime,lastedit) VALUES(?,?,'open',?,?,?,?,?,?,?)");
        $e->execute([$url, $docid, $com, $con, $id, $ip, $rv, $_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME']]);
    }
    
    public static function modifyEditRequest()
    {
    
    }
    
    public static function processEditRequest()
    {
    
    }

    

    public function getVersion($rawns, $title)
    {
        global $db, $DB_ASSOC;
        $docid = self::get_doc_id($rawns, $title);
        $d = $db->prepare("SELECT count(*) as cnt FROM `document` WHERE `docid`=?");
        $d->execute([$docid]);
        return intval($d->fetch($DB_ASSOC)['cnt']);
    }

    public static function findByID(int $id)
    {
        global $db, $DB_ASSOC;
        $c = $db->query("SELECT `namespace`,`title` FROM `live_document_list` WHERE `docid`=$id");
        return $c->fetch($DB_ASSOC);
    }
    
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

        $c = $db->prepare("SELECT `docid` FROM `document` WHERE `is_latest`='true' AND $sqlstr");
        $c->execute($words);
        $r = $c->fetchAll($DB_ASSOC);
        return $resSet;
    }
}