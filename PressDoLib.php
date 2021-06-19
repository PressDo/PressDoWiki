<?php
namespace PressDo
{
    error_reporting(E_ALL & ~E_NOTICE);
    require 'db.php';
    ($conf['UseShortURI'] === true)? require 'data/global/uri_short.php':require 'data/global/uri_long.php';
    class PressDo
    {
        public static function readSyntax($title, $content, $noredirect=0)
        {
            global $conf;
            require 'mark/'.$conf['Mark'].'/loader.php';
            return loadMarkUp($title, $content, $noredirect);
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

        public static function starDocument($action, $docid, $username)
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
            global $conf;
            $post_data = ['session' => $session];
            $header_data = array(
                'Content-type: application/x-www-form-urlencoded; charset=utf-8'
            );
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_HTTPHEADER => $header_data,
                CURLOPT_POSTFIELDS => http_build_query($post_data)
            ));
            
            $response = curl_exec($ch);
            return json_decode($response, true);
            /*$options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query(['session' => $session])
                )
            );
            $context  = stream_context_create($options);
            $result = file_get_contents(htmlspecialchars_decode($url), false, $context);
            return json_decode($result, true);*/
        }
    }

    class Member
    {
        public static function addUser($id, $pw, $email, $ua)
        {
            global $db;
            $gravatar_url = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d=retro';
            $d = $db->prepare("INSERT INTO `member`(username,password,email,gravatar_url,perm,last_login_ua) VALUES(?,?,?,?,?,?)");
            $d->execute([$id, $pw, $email, $gravatar_url, 'member',$ua]);
            unset($d);
        }

        public static function loginUser($id, $pw, $dt, $ip, $ua)
        {
            global $db;
            $s = $db->prepare("SELECT count(username) as cnt, `gravatar_url`, `username` FROM `member` WHERE `username`=? AND `password`=?");
            $s->execute([$id, $pw]);
            $sar = $s->fetch();
            if($sar[0] === '1'){
                $d = $db->prepare("INSERT INTO `login_history`(username,ip,datetime) VALUES(?,?,?)");
                $d->execute([$sar[2], $ip, $dt]);
                $e = $db->prepare("UPDATE `member` SET `last_login_ua`=?");
                $e->execute([$ua]);
                return [$sar[1],$sar[2]];
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

        public static function getPermsAnd($id){
            global $db;
            $d = $db->prepare("SELECT `perm`,`registered` FROM `member` WHERE `username`=?");
            $d->execute([$id]);
            return [explode(',', $d->fetch()[0]), $d->fetch()[1]];
        }
        
        public static function grantUser($id, $perms, $exec)
        {
            global $db;
            $p = self::getPermsAnd($id)[0];
            $minus = array_map(fn($a) => '-'.$a, array_diff($p, $perms));
            $plus = array_map(fn($a) => '+'.$a, array_diff($perms, $p));
            $granted = implode(' ', array_merge($plus, $minus));
            $c = implode(',', $perms);
            $e = $db->prepare("UPDATE `member` SET `perm`=? WHERE `id`=?");
            $e->execute([$c,$id]);
            $f = $db->prepare("INSERT INTO `acl_group_log` (executor,target_member,datetime,action,granted) VALUES(?,?,?,'grant',?)");
            $f->execute([$exec, $id, $dt, $granted]);
            unset($p,$c,$d,$e,$f);
        }
    }

    class Docs
    {
        public static function LoadDocument($namespace, $title, $rev=null)
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
        
        public static function SaveDocument($ns, $t, $con, $com, $act, $baserev, $prevlen, $id, $ip)
        {
            global $db;
            $c_nt = iconv_strlen($con)-$prevlen;
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
            $g = $db->prepare("INSERT INTO `document`(docid, content, length, comment, datetime, action, rev, count, contributor_m, contributor_i, is_hidden) VALUES(?,?,?,?,?,?,?,?,?,?,'false')");
            $g->execute([$did, $con, iconv_strlen($con), $com, $_SERVER['REQUEST_TIME'], $act, $baserev+1, $c_nt, $id, $ip]);
            unset($d, $e, $f, $g);
        }
        
        public static function LoadHistory($NS, $Title=null, $from=null,$until=null, $option=null)
        {
            global $db;
            if($Title !== null){
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
            $docid = $c->fetch()['docid'];
            $d = $db->prepare("SELECT count(*) as cnt FROM `document` WHERE `docid`=?");
            $d->execute([$docid]);
            return array($d->fetchAll()[0]['cnt'], $docid);
        }
        public static function findByID($id){
            global $db;
            $c = $db->query("SELECT `namespace`,`title` from `live_document_list` WHERE `docid`=$id");
            return $c->fetch();
        }
    }
    class ACL
    {
        public static function checkPerm($perm, $id){
            if(!$id) return false;
            $p = Member::getPermsAnd($id)[0];
            return (in_array($perm, $p));
        }

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
            function FinalCheck($acltype, $acls, $perms, $API){
                for ($i=0; $i<2; ++$i){ 
                    $acl_d = $acls[$i][$acltype];

                    if($i === 0 && count($acl_d) == 0)
                        continue;
                    
                    if($i === 1 && count($acl_d) == 0)
                        return [false, $acltype, null]; 
                    
                    foreach ($acl_d as $ad){  
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
                                if($cond[1] == strtolower($API['session']['member']['username'])){
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
                            return [false, $acltype, $allowed];
                    }
                }
            }
            function check($action){
                if($action == 'allow')
                    return true;
                elseif($action == 'deny')
                    return false;
            }
            

            $i = explode(':',strtolower($API['session']['identifier']));
            if($API['session']['member'] !== null){
                $s = Member::getPermsAnd($API['session']['member']['username']);
                $perms = $s[0];
                if($_SERVER['REQUEST_TIME'] >= $s[1] + 1296000)
                    array_push($perms, 'member_signup_15days_ago');
                if(in_array('nsacl', $perms)) return [true, $acltype];
            }

            $contributor = '`contributor_'.$i[0].'`';
            $docid = Docs::getIdAndVersion($API['page']['namespace'],$API['page']['title_'])[1];
            $r = $db->prepare("SELECT count(*) as cnt FROM `document` WHERE $contributor=?");
            $r->execute([$i[1]]);
            $q = $db->prepare("SELECT count(*) as cnt FROM `document` WHERE `docid`=? AND $contributor=?");
            $q->execute([$docid, $i[1]]);

            if($API['page']['title_'] == $i[1])
                array_push($perms, 'match_username_and_document_title');
            if($q->fetch()['cnt'] > 0)
                array_push($perms, 'document_contributor');
            if($r->fetch()['cnt'] > 0)
                array_push($perms, 'contributor');

            if($i[0] == 'm')
                $sql_str = '`target_member`=\''.$i[1].'\'';
            elseif($i[0] == 'i'){
                $sql_str = '`target_ip`=\''.$i[1].'\'';
                array_push($perms, 'ip');
            }
            $t = $db->prepare("SELECT `target_aclgroup`, `action` FROM `acl_group_log` WHERE $sql_str AND (`until`>=? OR `until`=0) ORDER BY `datetime` ASC");
            $t->execute([$_SERVER['REQUEST_TIME']]);
            $u = $t->fetchAll();
            foreach($u as $e){
                $a = [];
                if($e['action'] == 'aclgroup_add')
                    array_push($a, $e['target_aclgroup']);
                elseif($e['action'] == 'aclgroup_remove')
                    unset($a[array_search($e['target_aclgroup'], $a)]);
            }
            $args = [true => '/acl/', false =>'.php?page=acl&title='];
            $api = PressDo::requestAPI($conf['FullURL'].'/internal'.$args[$conf['UseShortURI']].$API['page']['title'], $_SESSION);
            $acls = [$api['page']['data']['docACL']['acls'], $api['page']['data']['nsACL']['acls']];

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
            
       
            if(FinalCheck('read', $acls, $perms, $API)[0] === false)
                return FinalCheck('read', $acls, $perms, $API);
            if($acltype == 'create_thread')
                if(FinalCheck('write_thread_comment', $acls, $perms, $API)[0] === false) return FinalCheck('write_thread_comment', $acls, $perms, $API);
            elseif($acltype == 'delete' || $acltype == 'move')
                if(FinalCheck('edit', $acls, $perms, $API)[0] === false) return FinalCheck('edit', $acls, $perms, $API);
            return FinalCheck($acltype, $acls, $perms, $API);
        }

        public static function deleteACL(){
        
        }
        
        public static function addACLgroup(){
        
        }
        
        public static function delACLgroup(){
        
        }
        
        public static function getACLgroups($admin){
            global $db;
            (!($admin === true))? $s = "WHERE `admin`!='true'":'';
            $a = $db->query("SELECT `name` FROM `aclgroups` $s");
            $r = [];
            foreach ($a->fetchAll() as $e){
                array_push($r, $e[0]);
            }
            return $r;
        }

        public static function getACLgroupMember($group, $from=null, $until=null){
            global $db;
            if($from !== null || $until !== null){
                $_u = ($until !== null)? 'id>='.$until:'';
                $_u = ($from !== null)? 'id=<'.$from:'';
                $sqlstr = 'AND ('.$_u.')';
            }else $sqlstr = '';
            $a = $db->prepare("SELECT id,executor,target_ip,target_member,comment,`datetime`,until FROM `acl_group_log` WHERE target_aclgroup=? AND (`until`>=? OR `until`=0) $sqlstr ORDER BY `id` ASC LIMIT 50");
            $a->execute([$group, $_SERVER['REQUEST_TIME']]);
            return $a->fetchAll();
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
            $docid = $d->fetch();
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
