<?php
session_start();
namespace PressDo
{
    require_once 'dbConnect.php';
    ini_set('include_path', __DIR__);

    if($conf['DevMode'] == true){
        $ip = explode('.', PressDo::getip());
        if (PressDo::getip() == '127.0.0.1' || $ip[0] == 10 || ($ip[0] == 172 && $ip[1] >= 16 && $ip[1] <= 31) || ($ip[0] == 192 && $ip[1] == 168)){
            $inside = true;
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        }
        if($inside !== true){
            ?><script src='https://prws.kr/js/01-fixing.js' integrity='sha384-pwqGhGQxZjjTYiTOQOzuKXp56Jht\/Axbe5++Dl5M\/RmD\/RSIAL1Q2htCHrb1DwML' crossorigin='anonymous'></script><?php
        exit;
    }
    }

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
        public static function ConstUser($session)
        {
            if(!$session['username']) $session['username'] = PressDo::getip();
            $u = $session['usertype'].':'.$session['username'];
            return array('username' => $u, 'group' => Data::getACLofUser($u));
        }
        public static function splitACL($acls, $action)
        {
            $acts = array('view', 'edit', 'move', 'delete', 'create_thread', 'write_thread_comment', 'edit_request', 'acl');
            $a_k = array_search($acts, $action);
            return substr($acls, $a_k, 1);
        }
    }

    class Data
    {
        public static function LoadOldDocument($docnm, $rev)
        {
            // 문서 로드(구)
            $d = urlencode($docnm);
            $s = "SELECT * from `old_Document` where BINARY DocNm='$d' AND version='$rev'";
            $q = SQL_Query($s);
            $r = SQL_Assoc($q);
            if($q->num_rows < 1){
                return false;
            }else{
                return array($docnm, $r);
            }
        }

        public static function LoadLatestDocument($docnm)
        {
            // 문서 로드(최신)
            $d = urlencode($docnm);
            $s = "SELECT * from `Document` where BINARY DocNm='$d';";
            $q = SQL_Query($s);
            $r = SQL_Assoc($q);
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
            if(preg_match('/^'.$conf['NameSpace'].':/', $docnm) || preg_match('/^'.$conf['Title'].'$/', $docnm)){
                $r = 'wiki';
            }elseif(preg_match('/^특수:/', $docnm)){
                $r = 'special';
            }elseif(preg_match('/^분류:/', $docnm)){
                $r = 'category';
            }else{
                $r = 'document';
            }
            // 문서 저장
            $doc = urlencode($docnm);
            $s = "SELECT * from `Document` where DocNm='$doc'";
            $q = SQL_Query($s);
            $res = SQL_Assoc($q);
            

            // 이전 버전과 차이 없으면 업데이트 안함
            if($res['content'] == $content){
                 return false;
            }else{
                // 기여자 설정
                if(!$_SESSION['userid']){
                    $con = PressDo::getip();
                    $l = 0;
                }else{
                    $con = $_SESSION['userid'];
                    $l = 1;
                }
                $c = $content;
                $len = mb_strlen($content);
                $dt = date("Y-m-d H:i:s");
                if(!$res){
                    $rev = 1;
                    $log = 'create';
                    $s = 'INSERT INTO `Document` (DocNm, content, version, strlen, type, contributor, savetime, summary, loginedit, logtype) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?)';
                }else{
                    // 기존 데이터 이동
                    $rev = $res['version'] + 1;
                    $log = 'edit';
                    $old = SQL_Query("INSERT INTO `old_Document` SELECT * FROM `Document` WHERE DocNm='$doc'");
                    $s = "UPDATE `Document` SET DocNm=?, content=?, version=?, strlen=?, type=?, contributor=?, savetime=?, summary=?, loginedit=?, logtype=? WHERE DocNm='$doc'";
                }
                
                $stmt = $SQL->prepare($s);
                $stmt->bind_param("ssisssssis", $doc, $c, $rev, $len, $r, $con, $dt, $sum, $l, $log);
                $q = $stmt->execute();
                if(!$q){
                    return 'false'.mysqli_error($SQL);
                }
            }
        }

        // 판본 숨기기
        public static function hidehistory($DocNm, $rev)
        {
            $doc = urlencode($DocNm);
            $q = SQL_Assoc(SQL_Query("SELECT * from `Document` where DocNm='$doc'"));
            if($q['version'] == $rev){
                // 최신 판본
                $act = SQL_Query("INSERT INTO `hidden_history` SELECT * FROM `Document` WHERE DocNm='$doc'");
                $d = SQL_Query("DELETE FROM `Document` WHERE DocNm='$doc' AND version='$rev'");
            }else{
                // 구 판본
                $act = SQL_Query("INSERT INTO `hidden_history` SELECT * FROM `old_Document` WHERE DocNm='$doc' AND version='$rev'");
                $d = SQL_Query("DELETE FROM `old_Document` WHERE DocNm='$doc' AND version='$rev'");
            }
        }

        // 최근편집
        public static function LoadWholeHistory($logtype)
        {
            $s = SQL_Query("SELECT * FROM `old_Document` ".$logtype." UNION ALL SELECT * FROM `Document` ".$logtype." ORDER BY `savetime` DESC LIMIT 200");
            $histories = array();
            while ($row = SQL_Assoc($s)) {
                $h = array('DocNm' => $row['DocNm'], 
                    'content' => $row['content'], 
                    'version' => $row['version'],
                    'strlen' => $row['strlen'],
                    'type' => $row['type'],
                    'category' => $row['category'],
                    'contributor' => $row['contributor'],
                    'savetime' => $row['savetime'],
                    'parent' => $row['parent'],
                    'summary' => $row['summary'],
                    'loginedit' => $row['loginedit'],
                    'logtype' => $row['logtype']
                );
                array_push($histories, $h);
            }
            return $histories;
        }

        // 임의문서
        public static function goRandom($n = 1)
        {
            $rand = SQL_Query("SELECT * FROM `Document` ORDER BY RAND() LIMIT $n");
            return SQL_Assoc($rand);
        }

        // 문서 ACL 설정 가져오기
        public static function getDocACL($DocNm, $action)
        {
            $DocNm = urlencode($DocNm);
            $get = SQL_Query("SELECT * FROM `ACL_Document` WHERE BINARY DocNm='$DocNm' AND action='$action'");
            $res = array();
            while ($row = SQL_Assoc($get)) {
                $h = array('ACLID' => $row['aclid'], 
                    'access' => $row['access'],
                    'condition' => $row['condition'],
                    'expiry' => $row['expiration']
                );
                array_push($res, $h);
            }
            if(empty($res)) return array(false);
            return $res;
        }

        // 선택한 이름공간의 ACL 가져오기
        public static function getNSACL($ns)
        {
            $get = SQL_Query("SELECT `type`,`name`,`$ns` FROM `ACL_NS`");
            $res = array();
            while ($row = SQL_Assoc($get)) {

                $h = array('type' => $row['type'], 
                    'name' => $row['name'],
                    'value' => $row[$ns]
                );
                array_push($res, $h);
            }
            if(empty($res)) return array(false);
            return $res;
        }

        // 사용자의 ACL 그룹 가져오기
        public static function getACLofUser($User)
        {
            $u = explode(':', $User);
            $un = $u[0];
            $ut = $u[1];
            $get = SQL_Query("SELECT ACL_user.aclgroup,ACL_group_list.priority FROM `ACL_user`,`ACL_group_list` WHERE ACL_user.username='$un' AND ACL_user.usertype='$ut' AND ACL_group_list.name=ACL_user.aclgroup ORDER BY ACL_group_list.priority ASC");
            return SQL_Assoc($get);
        }

        // 문서 ACL 제거
        public static function removeDocACL($ACLID)
        {
            $get = SQL_Query("DELETE FROM `ACL_Document` WHERE aclid='$ACLID'");
        }

        // ACL 그룹에 사용자 제거
        public static function removeUserACLgroup($seq)
        {
            $get = SQL_Query("DELETE FROM `ACL_user` WHERE seq='$seq'");
        }

        // ACL 그룹 삭제
        public static function removeACLgroup($aclgroup)
        {
            $get = SQL_Query("DELETE FROM `ACL_group_list` WHERE name='$aclgroup'");
        }

        // 문서 ACL 설정
        public static function setDocACL($DocNm, $action, $access, $target, $expiry = null)
        {
            global $SQL;
            $DocNm = urlencode($DocNm);
            $get = SQL_Query("SELECT * FROM `ACL_Document` WHERE DocNm='$DocNm' AND action='$action' AND condition='$target'");
            if($get->num_rows < 1){
                // 새 설정
                $in = SQL_Query("INSERT INTO `ACL_Document` (DocNm, action, access, condition, expiration) VALUES($DocNm, $action, $access, $target, $expiry)");
            }else{
                // 기존 설정 업데이트
                $in = SQL_Query("UPDATE `ACL_Document` SET DocNm='$DocNm', action='$action', access='$access', condition='$target', expiration='$expiry'");
            }
            if(!$in){
                return 'false'.mysqli_error($SQL);
            }
        }

        // ACL 그룹에 사용자 추가
        public static function addUserACLgroup($User, $aclgroup)
        {
            $User = urlencode($User);
            $get = SQL_Query("INSERT INTO `ACL_user` (username, aclgroup) VALUES('$User', '$aclgroup')");
        }

        // 이름공간 ACL 변경
        public static function setACLNS($aclgroup, $document, $template_set, $category, $file, $user, $special, $wiki, $discuss, $bin, $poll, $filebin, $operation, $template)
        {
            $get = SQL_Query("UPDATE `ACL_group_list` SET document='$document', template_set='$template_set', category='$category', file='$file', user='$user', special='$special', wiki='$wiki', discuss='$discuss', bin='$bin', poll='$poll', filebin='$filebin', operation='$operation', template='$template' WHERE name='$aclgroup'");
        }

        // 새 ACL 그룹 추가
        public static function addACLgroup($aclgroup, $desc, $document, $template_set, $category, $file, $user, $special, $wiki, $discuss, $bin, $poll, $filebin, $operation, $template)
        {
            $get = SQL_Query("INSERT INTO `ACL_group_list` (name, description, document, template_set, category, file, user, special, wiki, discuss, bin, poll, filebin, operation, template) VALUES('$aclgroup', '$desc', '$document', '$template_set', '$category', '$file', '$user', '$special', '$wiki', '$discuss', '$bin', '$poll', '$filebin', '$operation', '$template')");
        }
        // 사용자 ACL 그룹 추가/삭제(제작중)
        public static function checkACLgroup($user, $aclgroup)
        {
            $u = explode(':', $user);
            $type = $u[0];
            $username = $u[1];
            $get = SQL_Query("SELECT FROM `ACL_user` WHERE 'usertype'='$type' AND 'username'='$username' AND 'aclgroup'='$aclgroup'");
        }
        public static function checkACL($user, $action, $DocNm, $DocNS)
        {
            $DocACL = Data::getDocACL($DocNm, $action);
            $NSACL = Data::splitACL(getNSACL($DocNS), $action);
            $UserAG = Data::getACLofUser($user['username']);
            return $acceptance;
        }
    }
}
