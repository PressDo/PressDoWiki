<?php
namespace PressDo
{
    session_start();
    require_once 'data/global/config.php';
    if($conf['DevMode'] == true){
        $ip = explode('.', PressDo::getip());
        //if (PressDo::getip() == '127.0.0.1' || $ip[0] == 10 || ($ip[0] == 172 && $ip[1] >= 16 && $ip[1] <= 31) || ($ip[0] == 192 && $ip[1] == 168)){
            $inside = true;
            error_reporting(E_ALL);
            ini_set("display_errors", 1);
        //}
       // if($inside !== true){}
    }
    require_once 'db.php';
    ($conf['UseShortURI'] == true)? require_once 'data/global/uri_short.php':require_once 'data/global/uri_long.php';
    class PressDo
    {
        public static function readSyntax($Raw)
        {
            include 'syntax.php';
            return readSyntax($Raw);
        }

        public static function getip()
        {
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

        public static function starDocument($action, int $docid, $username)
        {
            global $db;
            if($action == 'star'){
                $d = $db->prepare("INSERT INTO `starred`(docid, user) VALUES(?,?)");
                $d->execute([$docid, $username]);
            }elseif($action == 'unstar'){
                $d = $db->prepare("DELETE FROM `starred` WHERE `docid`=? AND `user`=?");
                $d->execute([$docid, $username]);
            }
        }

        public static function getStarred($username)
        {   global $db;
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `user`=?");
            $d->execute([$username]);
            return $d->fetchAll();
        }

        public static function ifStarred($username, $docid)
        {   global $db;
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `docid`=? AND `user`=?");
            $d->execute([$docid, $username]);
            ($d->rowCount() < 1)? $bool = false:$bool = true;
            return $bool;
        }

        public static function countStar($docid)
        {   global $db;
            $d = $db->prepare("SELECT `user` FROM `starred` WHERE `docid`=?");
            $d->execute([$docid]);
            return $d->rowCount();
        }

        public static function requestAPI($url, $session)
        {
            // header, method, body를 설정한다.
            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query(['session' => $session])
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            return json_decode($result, true);
        }
    }

    class Member
    {
        public static function addUser($id, $pw, $email, $ua)
        {
            global $db;
            $gravatar_url = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d=retro';
            $d = $db->prepare("INSERT INTO `member`(username,password,email,gravatar_url,perm,last_login_ua) VALUES(?,?,?,?,?,?)");
            $d->execute([$id, $pw, $email, $gravatar_url, '[]',$ua]);
        }

        public static function loginUser($id, $pw, $dt, $ip, $ua)
        {
            global $db;
            $d = $db->prepare("SELECT `id` FROM `member` WHERE 'username'=? AND 'password'=?");
            $d->execute([$id, $pw]);
            if($s->num_rows == 1){
                $d = $db->prepare("INSERT INTO `login_history`(username,ip,datetime) VALUES(?,?,?)");
                $d->execute([$id, $ip, $dt]);
                $e = $db->prepare("UPDATE `member` SET `last_login_ua`=?");
                $e->execute([$ua]);
            }else return false;
        }
        
        public static function modifyUser($id, $pw, $email)
        {
            global $db;
            $gravatar_url = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d=retro';
            $d = $db->prepare("UPDATE `member` SET `password`=? AND `email`=? AND `gravatar_url`=? WHERE `id`=?");
            $d->execute([$pw,$email,$gravatar_url,$id]);
        }
        
        public static function loginHistory($id, $ip, $exec)
        {
            global $db;
            $dt = time();
            $d = $db->prepare("SELECT * FROM `login_history` WHERE `username`=? OR `ip`=? ORDER BY `datetime` DESC");
            $d->execute([$id,$ip]);
            $e = $db->prepare("INSERT INTO `acl_group_log` (executor,target_ip,target_member,datetime,action) VALUES(?,?,?,?,'login_history')");
            $e->execute([$exec, $ip, $id, $dt]);
            return $d->fetchAll();
        }
        
        public static function grantUser($id, array $perms, $exec)
        {
            global $db;
            $d = $db->prepare("SELECT `perm` FROM `member` WHERE `id`=?");
            $d->execute([$id]);
            $b = $d->fetch();
            $minus = array_map(fn($a) => '-'.$a, array_diff($b['perm'], $perms));
            $plus = array_map(fn($a) => '+'.$a, array_diff($perms, $b['perm']));
            $granted = implode(' ', array_merge($plus, $minus));
            $c = json_encode($perms, JSON_UNESCAPED_UNICODE);
            $e = $db->prepare("UPDATE `member` SET `perm`=? WHERE `id`=?");
            $e->execute([$c,$id]);
            $f = $db->prepare("INSERT INTO `acl_group_log` (executor,target_member,datetime,action,granted) VALUES(?,?,?,'grant',?)");
            $f->execute([$exec, $id, $dt, $granted]);
        }
    }

    class Docs
    {
        public static function LoadDocument($namespace, $title, $rev=null)
        {
            global $db;
            if($rev == null){
                $d = $db->prepare("SELECT * FROM `document` WHERE BINARY `namespace`=? AND `title`=? AND `is_hidden`='false' ORDER BY `datetime` DESC LIMIT 1");
                $d->execute([$namespace,$title]);
            } else {
                $d = $db->prepare("SELECT * FROM `document` WHERE BINARY `namespace`=? AND `title`=? AND `is_hidden`='false' ORDER BY `datetime` ASC LIMIT ?, 1");
                $d->execute([$namespace,$title,$rev-1]);
            }
            ($d->rowCount() < 1)? $a = ['namespace' => null, 'title' => null, 'content' => null, 'categories' => null]:$a = $d->fetch($db->FETCH_ASSOC);
            return $a;
        }
        
        public static function SaveDocument($ns, $t, $con, $com, $act, $id, $ip){
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $d->execute([$ns,$t]);
            if($a->num_rows < 1){
                $e = $db->prepare("INSERT INTO `live_document_list`(namespace,title) VALUES(?,?)");
                $e->execute([$ns,$t]);
                $f = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
                $f->execute([$ns,$t]);
                $did = $f->fetch(pdo::FETCH_ASSOC);
            }else{
                $did = $d->fetch(pdo::FETCH_ASSOC);
            }
            $g = $db->prepare("INSERT INTO `document`(docid,namespace, title, content, length, comment, datetime, action, contributor_m, contributor_i) VALUES(?,?,?,?,?,?,?,?,?,?)");
            $g->execute([$did['docid'], $ns, $t, $con, mb_strlen($con), $com, time(), $act, $id, $ip]);
        }
        
        public static function LoadHistory($docid=null, $from=null){
            global $db;
            if($docid !== null){
                if($from == null){
                    $d = $db->prepare("SELECT `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` DESC LIMIT 31");
                    $d->execute([$docid]);
                }else{
                    $d = $db->prepare("SELECT `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` ASC LIMIT ?, 30");
                    $d->execute([$docid,$from-1]);
                }
            }else{
                $d = $db->query("SELECT `namespace`, `title`, `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to` FROM `document` WHERE BINARY `is_hidden`='false' ORDER BY `datetime` DESC LIMIT 100");
            }
            return $d->fetchAll();
        }
        
        public static function hideHistory($docid, $timestamp){
            global $db;
            $d = $db->prepare("UPDATE `document` SET `is_hidden`='true' WHERE `docid`=? AND `datetime`=?");
            $d->execute([$docid, $timestamp]);
        }
        
        public static function unhideHistory($docid, $timestamp){
            global $db;
            $d = $db->prepare("UPDATE `document` SET `is_hidden`='true' WHERE `docid`=? AND `datetime`=?");
            $d->execute([$docid,$timestamp]);
        }
        
        public static function getRandom($n, $ns='document'){
            global $db;
            $d = $db->prepare("SELECT `title` FROM `live_document_list` WHERE `namespace`=? ORDER BY RAND() LIMIT ?");
            $d->execute([$ns,$n]);
            return $d->fetchAll();
        }
        
        public static function editRequest($ns, $t, $con, $com, $id, $ip, $rv, $url){
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $d->execute([$ns,$t]);
            $docid = $d->fetch();
            $e = $db->prepare("INSERT INTO `edit_request`(urlstr,docid,status,comment,content,contributor_m,contributor_i,base_revision,datetime,lastedit) VALUES(?,?,'open',?,?,?,?,?,?,?)");
            $e->execute([$url, $docid, $com, $con, $id, $ip, $rv, time(), time()]);
        }
        
        public static function modifyEditRequest(){
        
        }
        
        public static function processEditRequest(){
        
        }
    }
    class ACL
    {
        public static function getDocACL(){
        
        }
        
        public static function addACL(){
        
        }
        
        public static function checkACL(){
        
        }
        
        public static function deleteACL(){
        
        }
        
        public static function addACLgroup(){
        
        }
        
        public static function delACLgroup(){
        
        }
        
        public static function ACLgroupList(){
        
        }
        
        public static function addtoACLgroup(){
        
        }
        
        public static function getNSACL(){
        
        }
        
        public static function addNSACL(){
        
        }
        
        public static function deleteNSACL(){
        
        }
        
        public static function writeACLlog(){
        
        }
        
    }
    class Thread
    {
        public static function checkUserThread($username){
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`='사용자' AND `title`=?");
            $d->execute([$username]);
            $docid = $d->fetchAll();
            $e = $db->prepare("SELECT `last_comment` FROM `thread_list` WHERE `docid`=? ORDER BY `last_comment` DESC LIMIT 1");
            $e->execute([$docid[0]]);
            return $e->fetchAll();
        }
        public static function getDocThread($docid, $mode){
            global $db;
            if($mode == 1){
                $d = $db->prepare("SELECT `urlstr` FROM `thread_list` WHERE `docid`=?");
                $d->execute([$docid]);
                return $d->fetchAll();
            }
            //$d = $db->prepare(
        }
        public static function addThread(){
        
        }
        
        public static function addThreadComment(){
        
        }
        
        public static function editThread(){
        
        }
        
        public static function moveThread(){
        
        }
        
        public static function deleteThread(){
        
        }
    }
}
