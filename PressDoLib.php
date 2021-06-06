<?php
namespace PressDo
{
    session_start();
    require 'db.php';
    ($conf['UseShortURI'] === true)? require 'data/global/uri_short.php':require 'data/global/uri_long.php';
    class PressDo
    {
        public static function readSyntax($content, $noredirect = 0)
        {
            global $conf;
            require 'mark/'.$conf['Mark'].'/loader.php';
            return loadMarkUp($content, $noredirect);
        }
        
        public static function rand($l=16, $u=false){
            if($u)
                $c = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            else
                $c = '0123456789abcdefghijklmnopqrstuvwxyz';
            $cl = strlen($c);
            $s = '';
            for ($i=0; $i<$l; $i++) {
                $s .= $c[rand(0, $cl-1)];
            }
            return $s;
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

        public static function CIDRCheck ($IP, $CIDR)
        {
            // from PHP.net by claudiu at cnixs dot com
            list ($net, $mask) = split ("/", $CIDR);
            $ip_mask = ~((1 << (32 - $mask)) - 1);
            $ip_ip_net = ip2long ($IP) & $ip_mask;
            return ($ip_ip_net == ip2long ($net));
        }

        public static function geoip($ip)
        {
            return json_decode(file_get_contents('http://ip-api.com/json/'.$ip), true)['countryCode'];
            
        }

        public static function starDocument($action, int $docid, $username)
        {
            global $db;
            if($action === 'star'){
                $d = $db->prepare("INSERT INTO `starred`(docid, user) VALUES(?,?)");
                $d->execute([$docid, $username]);
            }elseif($action === 'unstar'){
                $d = $db->prepare("DELETE FROM `starred` WHERE `docid`=? AND `user`=?");
                $d->execute([$docid, $username]);
            }
            unset($d);
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
            unset($d);
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
            unset($d);
        }

        public static function loginUser($id, $pw, $dt, $ip, $ua)
        {
            global $db;
            $s = $db->prepare("SELECT count(id) as cnt, `gravatar_url` FROM `member` WHERE 'username'=? AND 'password'=?");
            $s->execute([$id, $pw]);
            if($s->fetch()[0] === 1){
                $d = $db->prepare("INSERT INTO `login_history`(username,ip,datetime) VALUES(?,?,?)");
                $d->execute([$id, $ip, $dt]);
                $e = $db->prepare("UPDATE `member` SET `last_login_ua`=?");
                $e->execute([$ua]);
                return $s->fetch()[1];
            }else return false;
            unset($s,$d,$e);
        }
        
        public static function modifyUser($id, $pw, $email)
        {
            global $db;
            $gravatar_url = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d=retro';
            $d = $db->prepare("UPDATE `member` SET `password`=? AND `email`=? AND `gravatar_url`=? WHERE `id`=?");
            $d->execute([$pw,$email,$gravatar_url,$id]);
            unset($d);
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
            unset($b,$c,$d,$e,$f);
        }
    }

    class Docs
    {
        public static function LoadDocument($namespace, $title, int $rev=null)
        {
            global $db;
            $c = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $c->execute([$namespace,$title]);
            $docid = $c->fetchAll()[0]['docid'];
            if($rev === null){
                $d = $db->prepare("SELECT * FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` DESC LIMIT 1");
                $d->execute([$docid]);
            } else {
                $d = $db->prepare("SELECT * FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` ASC LIMIT ".$rev-1 .", 1");
                $d->execute([$docid]);
            }
            ($d->rowCount() < 1)? $a = ['content' => null, 'categories' => null]:$a = $d->fetchAll();
            return $a;
        }
        
        public static function SaveDocument($ns, $t, $con, $com, $act, $id, $ip)
        {
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $d->execute([$ns,$t]);
            if($d->rowCount() < 1){
                $e = $db->prepare("INSERT INTO `live_document_list`(namespace,title) VALUES(?,?)");
                $e->execute([$ns,$t]);
                $f = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
                $f->execute([$ns,$t]);
                $did = $f->fetch()[0];
            }else{
                $did = $d->fetch()[0];
            }
            $g = $db->prepare("INSERT INTO `document`(docid, content, length, comment, datetime, action, contributor_m, contributor_i, is_hidden) VALUES(?,?,?,?,?,?,?,?,'false')");
            $g->execute([$did, $con, iconv_strlen($con), $com, $_SERVER['REQUEST_TIME'], $act, $id, $ip]);
            unset($d, $e, $f, $g);
        }
        
        public static function LoadHistory($NS, $Title, int $from=null,int $until=null)
        {
            global $db;
            if($NS !== null){
                $n = Docs::getIdAndVersion($NS, $Title);
                $c = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
                $c->execute([$NS, $Title]);
                if($c->rowCount() < 1) return null;
                $docid = $c->fetch()[0];
                if($from !== null){ // from 값 지정
                    $d = $db->prepare("SELECT `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` DESC LIMIT ".$nv-$from.", 31");
                    $d->execute([$docid]);
                    $ra = $d->fetchAll();
                }elseif($until !== null){ // until 값 지정
                    $d = $db->prepare("SELECT `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` ASC LIMIT ".$until-1 .", 31");
                    $d->execute([$docid]);
                    $ra = array_reverse($d->fetchAll());
                }else{ // 아무것도 없음
                    $d = $db->prepare("SELECT `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri` FROM `document` WHERE BINARY `docid`=? AND `is_hidden`='false' ORDER BY `datetime` DESC LIMIT 31");
                    $d->execute([$docid]);
                    $ra = $d->fetchAll();
                }
            }else{
                $d = $db->query("SELECT `docid`, `length`, `comment`, `action`, `reverted_version`, `contributor_m`, `contributor_i`, `acl_changed`, `moved_from`, `moved_to`, `datetime`, `edit_request_uri` FROM `document` WHERE BINARY `is_hidden`='false' ORDER BY `datetime` DESC LIMIT 100");
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
        
        public static function getRandom($n, $ns='document')
        {
            global $db;
            $d = $db->prepare("SELECT `namespace`,`title` FROM `live_document_list` WHERE `namespace`=? ORDER BY RAND() LIMIT $n");
            $d->execute([rawurlencode($ns)]);
            return $d->fetchAll();
        }
        
        public static function editRequest($ns, $t, $con, $com, $id, $ip, $rv, $url)
        {
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $d->execute([$ns,$t]);
            $docid = $d->fetchAll()[0]['docid'];
            $e = $db->prepare("INSERT INTO `edit_request`(urlstr,docid,status,comment,content,contributor_m,contributor_i,base_revision,datetime,lastedit) VALUES(?,?,'open',?,?,?,?,?,?,?)");
            $e->execute([$url, $docid, $com, $con, $id, $ip, $rv, $_SERVER['REQUEST_TIME'], $_SERVER['REQUEST_TIME']]);
        }
        
        public static function modifyEditRequest()
        {
        
        }
        
        public static function processEditRequest()
        {
        
        }
        
        public static function getIdAndVersion($NS,$Title)
        {
            global $db;
            $c = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $c->execute([$NS, $Title]);
            $docid = $c->fetchAll()[0]['docid'];
            $d = $db->prepare("SELECT count(*) as cnt FROM `document` WHERE `docid`=?");
            $d->execute([$docid]);
            return array($d->fetchAll()[0]['cnt'], $docid);
        }
    }
    class ACL
    {
        public static function getDocACL($NS, $Title){
            global $db;
            $n = intval(Docs::getIdAndVersion($NS, $Title)[1]);
            if($n > 0) {
                $d = $db->prepare("SELECT `id`,`access`,`condition`,`action`,`until` as `expired` FROM `acl_document` WHERE `docid`=$n AND (`until`>=? OR `until`=0) ORDER BY `id` ASC");
                $d->execute([$_SERVER['REQUEST_TIME']]);
                $r = $d->fetchAll();
            }else
                $r = null;
            return $r;
        }
        
        public static function addACL(){
        
        }
        
        public static function checkACL($API){
            global $db, $conf, $uri;
            /*
            Perm Type
            default: any, member, ip, 

            [0] => true/false
            [1] => err_permission
            [2] => editable
            */
            $perms = [];
            function check($action){
                if($action == 'allow'){
                    return true;
                }elseif($action == 'deny'){
                    return false;
                }
            }
           
            if($API['session']['member'] !== null){
                // 로그인된 유저 perm 가져오기
                $s = $db->prepare("SELECT perm FROM `member` WHERE 'username'=?");
                $s->execute([$API['session']['member']['username']]);
                $perms = json_decode($s->fetch(), true);
                if(in_array('nsacl', $perms)) return [true, $acltype];
            }
            $i = explode(':',$API['session']['identifier']);
            if($i[0] == 'm')
                $sql_str = '`target_member`=\''.$i[1].'\'';
            elseif($i[0] == 'i')
                $sql_str = '`target_ip`=\''.$i[1].'\'';
            // 만료되지 않았거나 영구설정으로 동록된 거
            $t = $db->prepare("SELECT `target_aclgroup`, `action` FROM `acl_group_log` WHERE $sql_str AND (`until`>=? OR `until`=0) ORDER BY `datetime` ASC");
            $t->execute([$_SERVER['REQUEST_TIME']]);
            $u = $t->fetchAll();
            foreach($u as $e){
                // 만료된 애들은 걸러내기
                $a = [];
                if($e['action'] == 'aclgroup_add')
                    array_push($a, $e['target_aclgroup']);
                elseif($e['action'] == 'aclgroup_remove')
                    unset($a[array_search($e['target_aclgroup'], $a)]);
            }
            $args = [true => '/acl/', false =>'.php?page=acl&title='];
            $api = PressDo::requestAPI('http://'.$conf['Domain'].'/internal'.$args[$conf['UseShortURI']].$API['page']['title'], $_SESSION);
            $acls = [$api['page']['data']['docACL']['acls'], $acln_s = $api['page']['data']['nsACL']['acls']];
            // acltype 지정
            switch($API['page']['viewName']){
                case 'wiki':
                case 'raw':
                case 'blame':
                case 'history':
                case 'diff':
                    $acltype = 'read';
                    break;
                case 'revert':
                    $acltype = 'edit';
                    break;
                default:
                    $acltype = $API['page']['viewName'];
            }
            // STEP 1
        function FinalCheck($acltype, $acls, $perms){
            for ($i=0; $i<2; $i++){ // 1단계 Loop: Doc / NS, read / edit ...
                $acl_d = $acls[$i][$acltype];
                foreach ($acl_d as $ad){ // 2단계 Loop: ACL 
                    if($i === 0 && count($acl_d) == 0)
                        break; // DocACL이고 ACL 설정이 없을 때
                    elseif($i === 1 && count($acl_d) == 0)
                        return [false, $acltype, null]; // NSACL 설정이 없을 때
                    $allowed = [];
                    if($ad['action'] == 'allow') array_push($allowed, $ad['condition']);
                    $cond = explode(':',$ad['condition']);
                    switch($cond[0]){
                        case 'perm':
                            if($cond[1] == 'any' || in_array($cond[1], $perms)){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                else
                                    return [check($ad['action']), $acltype];
                            }else
                                break;
                        case 'member':
                            if($cond[1] == $API['session']['member']['username']){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                else
                                    return [check($ad['action']), $acltype];
                            }
                            break;
                        case 'ip':
                            if($cond[1] == $API['session']['ip']){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                else
                                    return [check($ad['action']), $acltype];
                            }
                            break;
                        case 'geoip':
                            if($cond[1] == PressDo::geoip($API['session']['ip'])){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                else
                                    return [check($ad['action']), $acltype];
                            }
                            break;
                        case 'aclgroup':
                            if(in_array($cond[1], $a)){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                else
                                    return [check($ad['action']), $acltype];
                            }
                    }
                    if(count($acl_d) > 0)
                        return [false, $acltype, $allowed]; // 규칙 있지만 허용되지 않음
                }
            }
        }
       
        if(FinalCheck('read', $acls, $perms)[0] == false) return FinalCheck('read', $acls, $perms);
        if($acltype == 'create_thread'){
            if(FinalCheck('write_thread_comment', $acls, $perms)[0] == false) return FinalCheck('write_thread_comment', $acls, $perms);
        }elseif($acltype == 'delete' || $acltype == 'move'){
            if(FinalCheck('edit', $acls, $perms)[0] == false) return FinalCheck('edit', $acls, $perms);
        }
        return FinalCheck($acltype, $acls, $perms);
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
        
        public static function getNSACL($NS){
            global $db;
            $d = $db->prepare('SELECT `id`,`access`,`condition`,`action`,`until` as `expired` FROM `acl_namespace` WHERE `namespace`=? AND (`until`>=? OR `until`=0) ORDER BY `id` ASC');
            $d->execute([$NS, $_SERVER['REQUEST_TIME']]);
            return $d->fetchAll();
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
            if($mode === 1){
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
