<?php
namespace PressDo
{
    include 'config.php';
    require_once 'dbConnect.php';
    ini_set('include_path', __DIR__);
    class PressDo
    {
        public static function readSyntax($Raw)
        {
            include 'syntax.php';
            return readSyntax($Raw);
        }

        public static function getip()
        { // 사용자IP확인
            $ipaddress = '';
            if (getenv('HTTP_CLIENT_IP')) {
                $ipaddress = getenv('HTTP_CLIENT_IP');
            } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
            } elseif (getenv('HTTP_X_FORWARDED')) {
                $ipaddress = getenv('HTTP_X_FORWARDED');
            } elseif (getenv('HTTP_FORWARDED_FOR')) {
                $ipaddress = getenv('HTTP_FORWARDED_FOR');
            } elseif (getenv('HTTP_FORWARDED')) {
                $ipaddress = getenv('HTTP_FORWARDED');
            } elseif (getenv('REMOTE_ADDR')) {
                $ipaddress = getenv('REMOTE_ADDR');
            } else {
                $ipaddress = 'UNKNOWN';
            }
            return $ipaddress;
        }
    }

    class Data
    {
        public static function LoadOldDocument($docnm, $rev)
        {
            // 문서 로드(구)
            $d = urlencode($docnm);
            $s = "SELECT * from `old_Document` where DocNm='$d' AND version='$rev'";
            $q = SQL_Query($s);
            $r = mysqli_fetch_assoc($q);
            if($q->num_rows < 1){
                return false;
            }else{
                return array($docnm, $r['content'], $r['version'], $r['type']);
            }
        }

        public static function LoadLatestDocument($docnm)
        {
            // 문서 로드(최신)
            $d = urlencode($docnm);
            $s = "SELECT * from `Document` where DocNm='$d';";
            $q = SQL_Query($s);
            $r = mysqli_fetch_assoc($q);
            if($q->num_rows < 1){
                return false;
            }else{
                return array($docnm, $r);
            }
        }

        public static function SaveDocument($docnm, $content, $sum = null)
        {
            global $SQL, $conf;
            // 문서유형 설정
            if(preg_match('/^'.$conf['NameSpace'].':/', $docnm) || preg_match('/^'.$conf['Main'].'$/', $docnm)){
                $r = '프로젝트';
            }elseif(preg_match('/^특수:/', $docnm)){
                $r = '특수';
            }elseif(preg_match('/^분류:/', $docnm)){
                $r = '분류';
            }else{
                $r = '일반';
            }
            // 문서 저장
            $doc = urlencode($docnm);
            $s = "SELECT * from `Document` where DocNm='$doc'";
            $q = SQL_Query($s);
            $r = mysqli_fetch_assoc($q);
            $rev = $r['version'] + 1;

            // 이전 버전과 차이 없으면 업데이트 안함
            if($r['content'] == $content){
                 return false;
            }else{

            // 기여자 설정
            if(!$_SESSION['userid']){
                 $con = PressDo::getip();
            }else{
                 $con = $_SESSION['userid'];
            }
            $c = $content;

            // 기존 데이터 이동
            $old = SQL_Query("INSERT INTO `old_Document` SELECT * FROM `Document` WHERE DocNm='$doc'");
            $s = "UPDATE `Document` SET DocNm=?, content=?, version=?, type=?, contributor=?, savetime=?, summary=?";
            $stmt = $SQL->prepare($s);
            $stmt->bind_param("ssissss", $doc, $c, $rev, $r, $con, date("Y-m-d H:i:s"), $sum);
            $q = $stmt->execute();
            if(!$q){
                return 'false'.mysqli_error($SQL);
            }
            }
        }
    }
}
?>
