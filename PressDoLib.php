<?php
namespace PressDo
{
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    require 'db.php';
    ($conf['UseShortURI'] === true)? require 'data/global/uri_short.php':require 'data/global/uri_long.php';
    use HyungJu\ReadableURL;
    class PressDo
    {
        public static function exist(string $title): bool
        {
            global $db, $_ns, $lang;
            preg_match('/^('.implode('|',$_ns).'):(.*)$/', $title, $get_ns);
            if(!$get_ns){
                $NameSpace = $lang['ns:document'];
                $Title = $title;
            } else {
                $NameSpace = $get_ns[1];
                $Title = $get_ns[2];
            }
            $rawNS = array_search($NameSpace, $_ns);
            $d = $db->prepare("SELECT count(*) as cnt FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $d->execute([$rawNS, $Title]);
            if($d->fetch()['cnt'] > 0)
                $r = true;
            else
                $r = false;
            return $r;
        }
        public static function readSyntax($content, array $options=[])
        {
            global $conf, $uri;
            require 'mark/'.$conf['Mark'].'/loader.php';
            return loadMarkUp($content, $uri, $options);
        }
        
        public static function rand(int $l=16, bool $u=false, string $add=''): string
        {
            $c = '0123456789abcdefghijklmnopqrstuvwxyz';
            if($u) $c .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            if(strlen($add) > 0) $c .= $add;
            
            $cl = strlen($c);
            $s = '';
            for ($i=0; $i<$l; $i++) {
                $s .= $c[rand(0, $cl-1)];
            }
            return $s;
        }

        public static function getip(): string
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

        public static function cidr_range_ipv4(string $cidr): array
        {
            $range = array();
            $cidr = explode('/', $cidr);
            $range[0] = (ip2long($cidr[0])) & ((-1 << (32 - (int)$cidr[1])));
            $range[1] = ($range[0]) + pow(2, (32 - (int)$cidr[1])) - 1;
            return $range;
        }

        public static function cidr_range_ipv6(string $cidr): array
        {
            list($fs, $pl) = explode('/', $cidr);
            $fb = inet_pton($fs);
            $fh = reset(unpack('H*', $fb));
            $fs = inet_ntop($fb);
            $flexbits = 128 - $pl;
            $lh = $fh;
            $pos = 31;
            while ($flexbits > 0) {
                $orig = substr($lh, $pos, 1);
                $ov = hexdec($orig);
                $nv = $ov | (pow(2, min(4, $flexbits)) - 1);
                $new = dechex($nv);
                $lh = substr_replace($lh, $new, $pos, 1);
                $flexbits -= 4;
                $pos -= 1;
            }
            $lb = pack('H*', $lh);
            $ls = inet_ntop($lb);
            return [self::ipd($fs),self::ipd($ls)];
        }

        public static function ipd($ipaddress)
        {
            $pton = @inet_pton($ipaddress);
            $number = '';
            foreach (unpack('C*', $pton) as $byte) {
                $number .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
            }
            return base_convert(ltrim($number, '0'), 2, 10);
        }

        public static function geoip($ip): string
        {
            return json_decode(file_get_contents('http://ip-api.com/json/'.$ip), true)['countryCode']; 
        }

        public static function formatTime($sec): array
        {
            $week = floor($sec / 604800);
            $sec -= $week * 604800;
            $day = floor($sec / 86400);
            $sec -= $day * 86400;
            $hour = floor($sec / 3600);
            $sec -= $hour * 3600;
            $min = floor($sec / 60);
            $sec -= $min * 60;
            return ['week' => $week, 'day' => $day, 'hour' => $hour, 'minute' => $min, 'second' => $sec];
        }

        public static function starDocument($action, $docid, string $username)
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

        public static function getStarred(string $username): array
        {
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `user`=?");
            $d->execute([$username]);
            return $d->fetchAll();
        }

        public static function ifStarred($username, $docid): bool
        {
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `starred` WHERE `docid`=? AND `user`=?");
            $d->execute([$docid, $username]);
            ($d->rowCount() < 1)? $bool = false:$bool = true;
            unset($d);
            return $bool;
        }

        public static function countStar($docid): int
        {
            global $db;
            $d = $db->prepare("SELECT count(*) as cnt FROM `starred` WHERE `docid`=?");
            $d->execute([$docid]);
            return intval($d->fetch()['cnt']);
        }

        public static function requestAPI(string $url, $session)
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
        }
    }

    class Member
    {
        public static $PRESSDO_PERMS = ['delete_thread','admin','update_thread_status','nsacl','hide_thread_comment','grant','no_force_recaptcha','disable_two_factor_login','login_history','update_thread_document','update_thread_topic','aclgroup','api_access'];
        
        public static function addUser($id, $pw, $email, $ua)
        {
            global $db;
            $gravatar_url = '//www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?d=retro';
            $d = $db->prepare("INSERT INTO `member`(username,password,email,gravatar_url,perm,last_login_ua,registered) VALUES(?,?,?,?,?,?,?)");
            $d->execute([$id, $pw, $email, $gravatar_url, 'member',$ua,$_SERVER['REQUEST_TIME']]);
            unset($d);
        }

        public static function mailExists($mail)
        {
            global $db;
            $a = $db->prepare("SELECT count(*) as cnt FROM `member` WHERE email=?");
            $a->execute([$mail]);
            return ($a->fetch()[0] > 0)? true:false;
        }

        public static function userExists($user)
        {
            global $db;
            $a = $db->prepare("SELECT count(*) as cnt FROM `member` WHERE username=?");
            $a->execute([$user]);
            return ($a->fetch()[0] > 0)? true:false;
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
        
        public static function loginHistory($id, $exec, $from=null, $until=null)
        {
            global $db;
            if($from !== null)
                $sqlstr = "AND `datetime`<=$from";
            elseif($until !== null)
                $sqlstr = "AND `datetime`>=$until";

            $d = $db->prepare("SELECT * FROM `login_history` WHERE `username`=? $sqlstr ORDER BY `datetime` DESC LIMIT 50");
            $d->execute([$id]);
            $e = $db->prepare("INSERT INTO `acl_group_log` (executor,target_member,datetime,action) VALUES(?,?,?,'login_history')");
            $e->execute([$exec, $id, $_SERVER['REQUEST_TIME']]);
            return $d->fetchAll();
        }
        
        public static function LoginTime($user, $type)
        {
            global $db;
            if($type == 'r')
                $s = 'DESC';
            elseif($type == 'o')
                $s = 'ASC';
            $d = $db->prepare("SELECT `datetime` FROM `login_history` WHERE `username`=? ORDER BY `datetime` $s LIMIT 1");
            $d->execute([$user]);
            return $d->fetch()['datetime'];
        }

        public static function getPermsAnd($id)
        {
            global $db;
            $d = $db->prepare("SELECT `perm`,`registered`,`username`,`email`,`last_login_ua` FROM `member` WHERE `username`=?");
            $d->execute([$id]);
            $r = $d->fetch();
            return [explode(',', $r[0]), $r[1], $r[2], $r[3], $r[4]];
        }
        
        public static function grantUser($id, $perms, $exec)
        {
            global $db;
            $p = self::getPermsAnd($id)[0];
            $minus = array_map(fn($a) => '-'.$a, array_diff($p, $perms));
            $plus = array_map(fn($a) => '+'.$a, array_diff($perms, $p));
            $granted = implode(' ',$plus + $minus);
            $c = implode(',', $perms);
            $e = $db->prepare("UPDATE `member` SET `perm`=? WHERE `username`=?");
            $e->execute([$c,$id]);
            $f = $db->prepare("INSERT INTO `acl_group_log` (executor,target_member,datetime,action,granted) VALUES(?,?,?,'grant',?)");
            $f->execute([$exec, $id, $_SERVER['REQUEST_TIME'], $granted]);
            unset($p,$c,$d,$e,$f);
        }
    }

    class Docs
    {
        public static function parseTitle($title): array
        {
            global $lang, $_ns;
            preg_match('/^('.implode('|',$_ns).'):(.*)$/', $title, $get_ns);
            if(!$get_ns)
                return ['document', $lang['ns:document'], $title];
            else
                return [array_search($get_ns[1], $_ns), $get_ns[1], $get_ns[2]];
        }

        public static function makeTitle(string $rawNS, string $title): string
        {
            global $lang, $_ns, $conf;
            if($rawNS == 'document' && $conf['UseShortURI'] === false)
                return $title;
            else 
                return $lang['ns:'.$rawNS].$title;
        }

        public static function loadDocument(string $namespace, string $title, $rev=null, string $typ='assoc'): null | array
        {
            global $db, $DB_ASSOC, $DB_COLUMN;
            $fetchType = [
                'assoc' => $DB_ASSOC,
                'column' => $DB_COLUMN
            ];
            if($rev === null){
                $d = $db->prepare("SELECT d.content,d.length,d.comment,d.datetime,d.action,d.rev,d.count,d.reverted_version,d.contributor_m,d.contributor_i, d.edit_request_uri,d.acl_changed,d.moved_from,d.moved_to,d.is_hidden,d.is_latest FROM `document` as d INNER JOIN `live_document_list` as l ON l.docid = d.docid WHERE BINARY l.`namespace`=? AND l.`title`=? AND `is_hidden`='false' ORDER BY d.`datetime` DESC LIMIT 1");
                $d->execute([$namespace, $title]);
            } else {
                $d = $db->prepare("SELECT d.content,d.length,d.comment,d.datetime,d.action,d.rev,d.count,d.reverted_version,d.contributor_m,d.contributor_i, d.edit_request_uri,d.acl_changed,d.moved_from,d.moved_to,d.is_hidden,d.is_latest FROM `document` as d INNER JOIN `live_document_list` as l ON l.docid = d.docid WHERE BINARY l.`namespace`=? AND l.`title`=? AND `is_hidden`='false' ORDER BY d.`datetime` ASC LIMIT ".$rev-1 .", 1");
                $d->execute([$namespace, $title]);
            }
            ($d->rowCount() < 1)? $a = ['content' => null, 'categories' => null]:$a = $d->fetch($fetchType[$typ]);
            return $a;
        }
        
        public static function SaveDocument(string $ns, string $t, string $con, string $com, string $act, $baserev, $prevlen, $id, string $ip)
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
            }else
                $did = $d->fetch()[0];

            $a = $db->query("UPDATE `document` SET `is_latest`='false' WHERE `docid`=$did AND `is_latest`='true'");
            $g = $db->prepare("INSERT INTO `document`(docid, content, length, comment, datetime, action, rev, count, contributor_m, contributor_i, is_hidden,is_latest) VALUES(?,?,?,?,?,?,?,?,?,?,'false', 'true')");
            $g->execute([$did, $con, iconv_strlen($con), $com, $_SERVER['REQUEST_TIME'], $act, $baserev+1, $c_nt, $id, $ip]);
            unset($d, $e, $f, $g);
        }

        public static function moveDocument($from, $to, $id, $ip, $comment) 
        {
            global $db, $DB_ASSOC, $DB_;
            list($toNSraw, $toNS, $toT) = Docs::parseTitle($to);
            list($fromNSraw, $fromNS, $fromT) = Docs::parseTitle($from);
            $b = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $b->execute([$fromNSraw,$fromT]);
            $did = $b->fetch($DB_ASSOC)['docid'];

            $a = $db->prepare("UPDATE live_document_list SET `namespace`=?, `title`=? WHERE `docid`=(SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?)");
            $a->execute([$toNSraw, $toT, $fromNSraw, $fromT]);

            $c = Docs::loadDocument($fromNSraw, $fromT, null, 'column');
            /*$c['comment'] = $comment;
            $c['datetime'] = $_SERVER['REQUEST_TIME'];
            $c['action'] = 'move';
            $c['reverted_version'] = null;
            $c['contributor_m'] = null;
            $c['contributor_i'] = null;
            $c['edit_request_uri'] = null;
            $c['acl_changed'] = null;
            $c['moved_from'] = $from;
            $c['moved_to'] = $to;

            $a = $db->query("UPDATE `document` SET `is_latest`='false' WHERE `docid`=$did AND `is_latest`='true'");
            $b = $db->prepare("INSERT INTO `document`(docid,content,length,comment,datetime,action,rev,count,reverted_version,contributor_m,contributor_i, edit_request_uri,acl_changed,moved_from,moved_to,is_hidden,is_latest) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $b->execute($c);*/
        }

        public static function load_diff(string $old, string $new, $ov, $nv): string
        {
            require 'external/diff/Diff.php';
            require 'external/diff/Inline.php';
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
                $c = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
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

        public static function getUploadInfo()
        {
            global $db, $DB_ASSOC;
            $l = $db->query("SELECT `namespace`,display_name,`name` FROM uploader_license_category WHERE `type`='license'");
            $c = $db->query("SELECT `namespace`,display_name,`name` FROM uploader_license_category WHERE `type`='category'");
            return ['License' => $l->fetchAll($DB_ASSOC), 'Category' => $c->fetchAll($DB_ASSOC)];
        }
        
        public static function editRequest($ns, $t, $con, $com, $id, $ip, $rv, $url)
        {
            global $db;
            $d = $db->prepare("SELECT `docid` FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
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

        public static function getDocId($rawns, $title)
        {
            global $db, $DB_ASSOC;
            $c = $db->prepare("SELECT docid FROM `live_document_list` WHERE `namespace`=? AND `title`=?");
            $c->execute([$rawns, $title]);
            if($c->rowCount() < 1)
                $res = false;
            else
                $res = intval($c->fetch($DB_ASSOC)['docid']);
                
            return $res;
        }

        public static function getVersion($rawns, $title)
        {
            global $db, $DB_ASSOC;
            $docid = self::getDocId($rawns, $title);
            $d = $db->prepare("SELECT count(*) as cnt FROM `document` WHERE `docid`=?");
            $d->execute([$docid]);
            return intval($d->fetch($DB_ASSOC)['cnt']);
        }

        public static function findByID(int $id)
        {
            global $db;
            $c = $db->query("SELECT `namespace`,`title` FROM `live_document_list` WHERE `docid`=$id");
            return $c->fetch($DB_ASSOC);
        }
    }
    class ACL
    {
        public static $PRESSDO_ACLTYPES = ['read', 'edit', 'move', 'delete', 'create_thread', 'write_thread_comment', 'edit_request', 'acl'];
        public static function checkPerm($perm, $id)
        {
            if(!$id) return false;
            $p = Member::getPermsAnd($id)[0];
            return (in_array($perm, $p));
        }

        public static function getDocACL($ns, $Title)
        {
            global $db;
            $d = $db->prepare("SELECT a.`id`,a.`access`,a.`condition`,a.`action`,a.`until` as `expired` FROM `acl_document` AS a INNER JOIN `live_document_list` AS l ON l.docid = a.docid WHERE l.namespace=? AND l.title=? AND (`until`>=? OR `until`=0) ORDER BY `id` ASC");
            $d->execute([$ns, $Title, $_SERVER['REQUEST_TIME']]);
            $r = $d->fetchAll();
            return $r;
        }

        
        public static function addACL($t, $ns, $title=null, $type, $condition, $action, $until)
        {
            global $db;
            if($t == 'doc'){
                $n = Docs::getDocId($ns, $title);
                $b = $db->prepare("SELECT count(id) as cnt FROM `acl_document` WHERE `docid`=$n AND `access`=? AND `condition`=? AND (`until`>=? OR `until`=0)");
                $b->execute([$type,$condition,$_SERVER['REQUEST_TIME']]);
                if($b->fetch()[0] > 0) return false;
                else{
                    $c = $db->prepare("INSERT INTO `acl_document` (`docid`,`access`,`condition`,`action`,`until`) VALUES($n,?,?,?,$until)");
                    $c->execute([$type,$condition,$action]);
                }
            }elseif($t == 'ns'){
                $b = $db->prepare("SELECT count(id) as cnt FROM `acl_namespace` WHERE `namespace`=? AND `access`=? AND `condition`=? AND (`until`>=? OR `until`=0)");
                $b->execute([$ns,$type,$condition,$_SERVER['REQUEST_TIME']]);
                if($b->fetch()[0] > 0) return false;
                else {
                    $c = $db->prepare("INSERT INTO `acl_namespace` (`namespace`,`access`,`condition`,`action`,`until`) VALUES(?,?,?,?,$until)");
                    $c->execute([$ns,$type,$condition,$action]);
                }
            }

        }

        public static function getAllowed($acltype, $acls, $perms, $API)
        {
            $res = [];
            for ($i=0; $i<2; ++$i){ 
                $acl_d = $acls[$i][$acltype];
                if($i === 0 && count($acl_d) == 0)
                    continue;
                if($i === 1 && count($acl_d) == 0)
                    return null;
                foreach ($acl_d as $ad){
                    $cond = explode(':',$ad['condition']);
                    switch($cond[0]){
                        case 'perm':
                            if($cond[1] == 'any' || in_array($cond[1], $perms)){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                elseif($ad['action'] == 'allow')
                                    array_push($res, $cond);
                            }
                            break;
                        case 'member':
                            if($cond[1] == strtolower($API['session']['member']['username'])){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                elseif($ad['action'] == 'allow')
                                    array_push($res, $cond);
                            }
                            break;
                        case 'ip':
                            if($cond[1] == $API['session']['ip']){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                elseif($ad['action'] == 'allow')
                                    array_push($res, $cond);
                            }
                            break;
                        case 'geoip':
                            if($cond[1] == PressDo::geoip($API['session']['ip'])){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                elseif($ad['action'] == 'allow')
                                    array_push($res, $cond);
                            }
                            break;
                        case 'aclgroup':
                            $ingroup = ACL::in_aclgroup($cond[1],explode(':',$API['session']['identifier'])[1], null);
                            if($ingroup[0] === true){
                                if($ad['action'] == 'gotons')
                                    break 2;
                                elseif($ad['action'] == 'allow')
                                    array_push($res, $cond);
                            }
                    }
                }
                return $res;
            }
        }
        
        public static function checkACL($API)
        {
            global $db, $conf, $uri;
            /*
                [0] => true/false
                [1] => err_permission
                [2] => allowed
                [3] => errtype (norule/in_aclgroup/target/non_target)
                [4] => yourstate
            */
            $perms = [];
            if(!function_exists('PressDo\FinalCheck')){
                function FinalCheck($acltype, $acls, $perms, $API){
                    if(!function_exists('PressDo\check')){
                        function check($action){
                            if($action == 'allow')
                                return true;
                            elseif($action == 'deny')
                                return false;
                        }
                    }
                    $allowed = ACL::getAllowed($acltype, $acls, $perms, $API);
                    for ($i=0; $i<2; ++$i){ 
                        $acl_d = $acls[$i][$acltype];
                        
                        if($i === 0 && count($acl_d) == 0)
                            continue;
                        if($i === 1 && count($acl_d) == 0)
                            return [false, $acltype, null, 'no_rules']; 
                        foreach ($acl_d as $ad){
                            $cond = explode(':',$ad['condition']);
                            switch($cond[0]){
                                case 'perm':
                                    if($cond[1] == 'any' || in_array($cond[1], $perms)){
                                        if($ad['action'] == 'gotons')
                                            break 2;
                                        else
                                            return [check($ad['action']), $acltype, $allowed, 'in_target', 'any', null];
                                    }
                                    break;
                                case 'member':
                                    if($cond[1] == strtolower($API['session']['member']['username'])){
                                        if($ad['action'] == 'gotons')
                                            break 2;
                                        else
                                            return [check($ad['action']), $acltype, $allowed, 'in_target', 'member:'.$cond[1], null];
                                    }
                                    break;
                                case 'ip':
                                    if($cond[1] == $API['session']['ip']){
                                        if($ad['action'] == 'gotons')
                                            break 2;
                                        else
                                            return [check($ad['action']), $acltype, $allowed, 'in_target', 'ip:'.$cond[1], null];
                                    }
                                    break;
                                case 'geoip':
                                    if($cond[1] == PressDo::geoip($API['session']['ip'])){
                                        if($ad['action'] == 'gotons')
                                            break 2;
                                        else
                                            return [check($ad['action']), $acltype, $allowed, 'in_target', 'geoip:'.$cond[1], null];
                                    }
                                    break;
                                case 'aclgroup':
                                    $ingroup = ACL::in_aclgroup($cond[1],explode(':',$API['session']['identifier'])[1], null);
                                    if($ingroup[0] === true){
                                        if($ad['action'] == 'gotons')
                                            break 2;
                                        else
                                            return [check($ad['action']), $acltype, $allowed, 'in_aclgroup', $cond[1], $ingroup[1]];
                                    }
                            }
                        }
                        if(count($acl_d) > 0)
                            return [false, $acltype, $allowed, 'not_target'];
                    }
                }
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
            $docid = Docs::getDocId($API['page']['namespace'],$API['page']['title_']);
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
            if($i[0] == 'i')
                array_push($perms, 'ip');
            
            $args = [true => '/acldata/', false =>'.php?page=acldata&title='];
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
            if($acltype == 'create_thread'){
                if(FinalCheck('write_thread_comment', $acls, $perms, $API)[0] === false)
                    return FinalCheck('write_thread_comment', $acls, $perms, $API);
            }elseif($acltype == 'delete' || $acltype == 'move'){
                if(FinalCheck('edit', $acls, $perms, $API)[0] === false)
                    return FinalCheck('edit', $acls, $perms, $API);
            }elseif($acltype == 'edit' && FinalCheck('edit', $acls, $perms, $API)[0] === false){
                if(FinalCheck('edit_request', $acls, $perms, $API)[0] === true)
                    return FinalCheck('edit', $acls, $perms, $API);
                else{
                    $ret = FinalCheck('edit_request', $acls, $perms, $API);
                    $ret[6] = FinalCheck('edit', $acls, $perms, $API);
                }
                    return $ret;
            }
            return FinalCheck($acltype, $acls, $perms, $API);
        }

        public static function deleteACL($typ, $id)
        {
            global $db;
            if($typ == 'doc') $type = 'acl_document';
            if($typ == 'ns') $type = 'acl_namespace';
            $a = $db->query("DELETE FROM `$type` WHERE id=$id");
        }
        
        public static function addACLgroup($g,$admin='false')
        {
            global $db;
            if($admin != 'true') $admin = 'false';
            $a = $db->prepare("INSERT INTO `aclgroups`(`name`,`admin`) VALUES(?,?)");
            $a->execute([$g,$admin]);
        }
        
        public static function delACLgroup($g)
        {
            global $db;
            $a = $db->prepare("DELETE FROM `aclgroups` WHERE `name`=?");
            $a->execute([$g]);
            $b = $db->prepare("UPDATE `acl_group_log` SET removed=1 WHERE `action`='aclgroup_add' AND target_aclgroup=?");
            $b->execute([$g]);
        }
        
        public static function getACLgroups($admin)
        {
            global $db;
            (!($admin === true))? $s = "WHERE `admin`!='true'":'';
            $a = $db->query("SELECT `name` FROM `aclgroups` $s");
            $r = [];
            foreach ($a->fetchAll() as $e){
                array_push($r, $e[0]);
            }
            return $r;
        }

        public static function in_aclgroup($aclgroup, $user, $mode=null)
        {
            global $db;
            if($mode == 'CIDR'){ // 중복된 입력인지 확인
                $a = $db->prepare("SELECT count(*) as cnt FROM `acl_group_log` WHERE target_aclgroup=? AND display_ip=? AND action='aclgroup_add' AND (`until`>=? OR `until`=0) AND removed IS NULL ORDER BY datetime");
                $a->execute([$aclgroup,$user,$_SERVER['REQUEST_TIME']]);
                if($a->fetch()['cnt'] > 0)
                    return true;
            }else{
                (strpos($user,'.') || strpos($user,':'))? $t = 'i':$t = 'm';
                if($t == 'i'){
                    if(strpos($user,':')){
                        $ty = 'ipv6';
                        $ip = PressDo::ipd($user);
                    }else{
                        $ty = 'ipv4';
                        $ip = ip2long($user);
                    }
                    $a = $db->prepare("SELECT count(*) as cnt,id,`datetime`,comment,until FROM acl_group_log WHERE target_aclgroup=? AND (? BETWEEN from_ip AND to_ip) AND ipver=? AND `action`='aclgroup_add' AND (`until`>=? OR `until`=0) AND removed IS NULL ORDER BY datetime");
                    $a->execute([$aclgroup,$ip,$ty,$_SERVER['REQUEST_TIME']]);
                }elseif($t = 'm'){
                    $a = $db->prepare("SELECT count(*) as cnt,id,`datetime`,comment,until FROM `acl_group_log` WHERE target_aclgroup=? AND target_member=? AND `action`='aclgroup_add' AND (`until`>=? OR `until`=0) AND removed IS NULL ORDER BY datetime");
                    $a->execute([$aclgroup,$user,$_SERVER['REQUEST_TIME']]);
                }
                $b = $a->fetch();
                if(intval($b['cnt']) < 1)
                    return [false];
                else
                    return [true,$b];
            }
        }

        public static function getACLgroupMember($group, $from=null, $until=null)
        {
            global $db;
            if($from !== null || $until !== null){
                $_u = ($until !== null)? 'id>='.$until:'';
                $_u = ($from !== null)? 'id=<'.$from:'';
                $sqlstr = 'AND ('.$_u.')';
            }else $sqlstr = '';
            $a = $db->prepare("SELECT id,executor,from_ip,to_ip,display_ip,target_member,comment,`datetime`,`until` FROM `acl_group_log` WHERE `action`='aclgroup_add' AND target_aclgroup=? AND (`until`>=? OR `until`=0) AND removed IS NULL $sqlstr ORDER BY `id` ASC LIMIT 50");
            $a->execute([$group, $_SERVER['REQUEST_TIME']]);
            return $a->fetchAll();
        }
        
        public static function addtoACLgroup($exec, $cidr, $username, $aclgroup, $expire, $memo)
        {
            global $db;
            if($cidr !== null){
                if(strpos($cidr, ':')){
                    $m = 'cidr_range_ipv6';
                    $v = 'ipv6';
                }else{
                    $m = 'cidr_range_ipv4';
                    $v = 'ipv4';
                }
                $ip = PressDo::$m($cidr);
            }
            $user = Member::getPermsAnd($username)[2];
            $a = $db->prepare("INSERT INTO acl_group_log (executor,from_ip,to_ip,display_ip,ipver,target_member,`target_aclgroup`,`comment`,`datetime`,`until`,`action`) VALUES(?,?,?,?,?,?,?,?,?,?,'aclgroup_add')");
            $a->execute([$exec,$ip[0],$ip[1],$cidr,$v,$user,$aclgroup,$memo,$_SERVER['REQUEST_TIME'],$expire]);
        }

        public static function remove_from_aclgroup($id, $exec, $memo)
        {
            global $db;
            if(!is_numeric($id)) return false;
            $a = $db->query("SELECT count(*) as cnt,display_ip,target_member,target_aclgroup FROM acl_group_log WHERE id=$id");
            $r1 = $a->fetch();
            if($r1['cnt'] < 1) return false;
            $b = $db->prepare("INSERT INTO acl_group_log (executor,display_ip,`target_member`,`target_aclgroup`,`comment`,`datetime`,`until`,`action`) VALUES(?,?,?,?,?,?,0,'aclgroup_remove')");
            $b->execute([$exec,$r1['display_ip'],$r1['target_member'],$r1['target_aclgroup'],$memo,$_SERVER['REQUEST_TIME']]);
            $c = $db->query("UPDATE `acl_group_log` SET removed=1 WHERE id=$id");
        }
        
        public static function getNSACL($ns)
        {
            global $db;
            $d = $db->prepare('SELECT `id`,`access`,`condition`,`action`,`until` as `expired` FROM `acl_namespace` WHERE `namespace`=? AND (`until`>=? OR `until`=0) ORDER BY `id` ASC');
            $d->execute([$ns, $_SERVER['REQUEST_TIME']]);
            return $d->fetchAll();
        }

        public static function groupExists($g)
        {
            global $db;
            $a = $db->prepare("SELECT count(*) as cnt FROM `aclgroups` WHERE `name`=?");
            $a->execute([$g]);
            if(intval($a->fetch()['cnt']) > 0)
                return true;
            else
                return false;
        }

        public static function blockHistory($target=null, $query=null, $from=null, $until=null)
        {
            global $db;
            if($from !== null)
                $sqlstr = "AND id<=$from";
            elseif($until !== null)
                $sqlstr = "AND id>=$until";

            if($query == null && $target == null)
                $sqlstr = strtr($sqlstr, 'AND', 'WHERE');

            if($query !== null && $target == 'author'){
                $a = $db->prepare("SELECT id,executor,executor_ip,display_ip,target_member,target_aclgroup,comment,`datetime`,until,`action`,granted FROM `acl_group_log` WHERE `executor`=? $sqlstr ORDER BY id ASC LIMIT 100");
                $a->execute([trim($query)]);
            }elseif($query !== null && $target == 'text'){
                $a = $db->prepare("SELECT id,executor,executor_ip,display_ip,target_member,target_aclgroup,comment,`datetime`,until,`action`,granted FROM `acl_group_log` WHERE (`display_ip` LIKE ? OR `comment` LIKE ? OR `target_member` LIKE ? OR `target_aclgroup` LIKE ? OR `id` LIKE ? ) $sqlstr ORDER BY id ASC LIMIT 100");
                $q = '%'.$query.'%';
                $a->execute([$q,$q,$q,$q,$q]);
            }else{
                $a = $db->query("SELECT id,executor,executor_ip,display_ip,target_member,target_aclgroup,comment,`datetime`,until,`action`,granted FROM `acl_group_log` $sqlstr ORDER BY id ASC LIMIT 100");
            }
            return $a->fetchAll();
        }

        public static function countBlockHistory()
        {
            global $db;
            $a = $db->query("SELECT count(*) as cnt FROM `acl_group_log`");
            return $a->fetch()['cnt'];
        }

        
    }
    class Thread
    {
        public static function checkUserThread($username)
        {
            global $db;
            $e = $db->prepare("SELECT `last_comment` FROM `thread_list` WHERE `namespace`='user' AND `title`=? ORDER BY `last_comment` DESC LIMIT 1");
            $e->execute([$username]);
            return $e->fetchAll();
        }

        public static function getDocThread($ns,$Title, $mode='normal')
        {
            global $db, $DB_ASSOC;
            if($mode === 'normal'){
                $d = $db->prepare("SELECT urlstr,topic FROM `thread_list` WHERE `namespace`=? AND `title`=? AND (`status`='normal' OR `status`='pause')");
                $d->execute([$ns,$Title]);
                return $d->fetchAll($DB_ASSOC);
            }elseif($mode === 'closed'){
                $d = $db->prepare("SELECT urlstr,topic FROM `thread_list` WHERE `namespace`=? AND `title`=? AND `status`='close'");
                $d->execute([$ns,$Title]);
                return $d->fetchAll($DB_ASSOC);
            }
        }

        public static function RecentDiscuss($logtype)
        {
            global $db, $DB_ASSOC;
            $query = [
                'normal_thread' => "SELECT * FROM `thread_list` WHERE `status`='normal' ORDER BY `last_comment` DESC LIMIT 100",
                'old_thread' => "SELECT * FROM `thread_list` WHERE `status`='normal' ORDER BY `last_comment` ASC LIMIT 100",
                'closed_thread' => "SELECT * FROM `thread_list` WHERE `status`='close' ORDER BY `last_comment` DESC LIMIT 100",
                'open_editrequest' => "SELECT * FROM `edit_request` WHERE `status`='open' ORDER BY `last_comment` DESC LIMIT 100",
                'accepted_editrequest' => "SELECT * FROM `edit_request` WHERE `status`='accepted' ORDER BY `last_comment` DESC LIMIT 100",
                'closed_editrequest' => "SELECT * FROM `edit_request` WHERE `status`='close' ORDER BY `last_comment` DESC LIMIT 100",
                'old_editrequest' => "SELECT * FROM `edit_request` WHERE `status`='open' ORDER BY `last_comment` ASC LIMIT 100",
            ];
            $q = ($query[$logtype] === null)? "SELECT * FROM `thread_list` WHERE `status`='normal' ORDER BY `last_comment` DESC LIMIT 100":$query[$logtype];
            $a = $db->query($q);
            return $a->fetchAll($DB_ASSOC);
        }

        public static function getThreadInfo($urlstr)
        {
            global $db, $DB_ASSOC;
            $d = $db->prepare("SELECT `namespace`,title,topic,`status` FROM `thread_list` WHERE `urlstr`=?");
            $d->execute([$urlstr]);
            $e = $db->prepare("SELECT contributor_m,contributor_i FROM `thread_content` WHERE `urlstr`=? AND `no`='1'");
            $e->execute([$urlstr]);
            return $d->fetch($DB_ASSOC) + $e->fetch($DB_ASSOC);
        }

        public static function getComments($urlstr)
        {
            global $db, $DB_ASSOC;
            $b = $db->prepare("SELECT `no`,contributor_m,contributor_i,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 30");
            $b->execute([$urlstr]);
            $arr = array_reverse($b->fetchAll($DB_ASSOC));
            return $arr;
        }

        public static function getLatestComments($urlstr)
        {
            global $db, $DB_ASSOC;
            $b = $db->prepare("SELECT `no`,contributor_m,contributor_i,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? ORDER BY `no` DESC LIMIT 3");
            $b->execute([$urlstr]);
            $arr = array_reverse($b->fetchAll($DB_ASSOC));
            if($arr[0]['no'] !== '1' && $b->rowCount() == '3'){
                $c = $db->prepare("SELECT `no`,contributor_m,contributor_i,`type`,content,`datetime`,`blind` FROM thread_content WHERE urlstr=? AND `no`='1'");
                $c->execute([$urlstr]);
                $arr = array_merge([$c->fetch($DB_ASSOC)],$arr);
            }
            return $arr;
        }

        public static function createThread($ns, $title, $topic, $comment, $iden){
            global $db;
            if(explode(':', $iden)[0] == 'm')
                $user = explode(':', $iden)[1];
            else
                $ip = explode(':', $iden)[1];
            $slug = self::generateSlug();
            $a = $db->prepare("INSERT INTO `thread_list` (urlstr,`namespace`,title,topic,`status`,last_comment) VALUES (?,?,?,?,?)");
            $a->execute([$slug, $ns,$title, $topic, 'normal', $_SERVER['REQUEST_TIME']]);
            $b = $db->prepare("INSERT INTO `thread_content` (urlstr,`no`,contributor_m,contributor_i,`type`,content,`datetime`) VALUES (?,?,?,?,?,?,?)");
            $b->execute([$slug, '1', $user, $ip, 'comment', $comment, $_SERVER['REQUEST_TIME']]);
            return $slug;
        }
        
        public static function addThreadComment($slug, $comment, $iden)
        {
            global $db, $DB_ASSOC;
            if(explode(':', $iden)[0] == 'm')
                $user = explode(':', $iden)[1];
            else
                $ip = explode(':', $iden)[1];
            $a = $db->prepare("SELECT `no` FROM `thread_content` WHERE urlstr=? ORDER BY `no` DESC LIMIT 1");
            $a->execute([$slug]);
            $num = $a->fetch($DB_ASSOC)['no'] + 1;
            
            $b = $db->prepare("INSERT INTO `thread_content` (urlstr,`no`,contributor_m,contributor_i,`type`,content,`datetime`) VALUES (?,?,?,?,?,?,?)");
            $b->execute([$slug, $num, $user, $ip, 'comment', $comment, $_SERVER['REQUEST_TIME']]);

            $b = $db->prepare("UPDATE `thread_list` SET last_comment=? WHERE urlstr=?");
            $b->execute([$_SERVER['REQUEST_TIME'],$slug]);
        }
        
        public static function updateStatus($slug, $status, $user)
        {
            global $db, $DB_ASSOC;
            $a = $db->prepare("UPDATE `thread_list` SET `status`=?, last_comment=? WHERE urlstr=?");
            $a->execute([$status,$_SERVER['REQUEST_TIME'],$slug]);
            
            $c = $db->prepare("SELECT `no` FROM `thread_content` WHERE urlstr=? ORDER BY `no` DESC LIMIT 1");
            $c->execute([$slug]);
            $num = $c->fetch($DB_ASSOC)['no'] + 1;
            
            $b = $db->prepare("INSERT INTO `thread_content` (urlstr,`no`,contributor_m,`type`,content,`datetime`) VALUES(?,?,?,?,?,?)");
            $b->execute([$slug,$num,$user,'status',$status,$_SERVER['REQUEST_TIME']]);
        }
        
        public static function moveThread($slug, $full, $user)
        {
            global $db, $DB_ASSOC;
            list($ns, $n, $t) = Docs::parseTitle($full);
            $a = $db->prepare("UPDATE `thread_list` SET `namespace`=?, title=?, last_comment=? WHERE urlstr=?");
            $a->execute([$ns,$t,$_SERVER['REQUEST_TIME'],$slug]);
            
            $c = $db->prepare("SELECT `no` FROM `thread_content` WHERE urlstr=? ORDER BY `no` DESC LIMIT 1");
            $c->execute([$slug]);
            $num = $c->fetch($DB_ASSOC)['no'] + 1;
            
            $b = $db->prepare("INSERT INTO `thread_content` (urlstr,`no`,contributor_m,type,content,datetime) VALUES(?,?,?,?,?,?)");
            $b->execute([$slug,$num,$user,'document',$full,$_SERVER['REQUEST_TIME']]);
        }
        
        public static function deleteThread()
        {
        
        }
        
        public static function generateSlug()
        {
            require 'external/ReadableURL/ReadableURL.php';
            require 'external/ReadableURL/lang/LanguageHelper.php';
            require 'external/ReadableURL/lang/Language.php';
            require 'external/ReadableURL/lang/en/En.php';
            $slug = new ReadableURL(true, 4, '');
            return $slug->generate();
        }
    }
}
