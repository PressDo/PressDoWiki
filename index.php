<?php
if(!($_GET['page'] == 'member' && $_GET['title'] == 'signup')) session_start();
use PressDo\PressDo;
use PressDo\Docs;
use PressDo\WikiSkin;
use PressDo\SkinTemplate;
use PressDo\Pages;
use PressDo\ACL;
use PressDo\Member;
use PressDo\Thread;
require 'PressDoLib.php';
require 'skin/liberty/skin.php';
if($conf['UseMailAuth'] === true){
    require 'Mailer.php';
}
$_SESSION['POST']= (isset($_POST))? $_POST:[];
$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
$_SESSION['ip'] = PressDo::getip();
$pagename = $_GET['page'];
($conf['UseShortURI'])? $_url=$conf['FullURL'].'/internal'.$_SERVER['REQUEST_URI'] : $_url=$conf['FullURL'].'/internal.php?'.$_SERVER['QUERY_STRING'];
$api = PressDo::requestAPI($_url, $_SESSION);
if(isset($api['page']['data']))
    $api_data = $api['page']['data'];
$api_doc = $api_data['document'];
if(isset($api_doc['namespace']))
    ($api_doc['namespace'] == $lang['ns:document'] && $conf['ForceShowNameSpace'] === false)? $ns = '' : $ns = $api_doc['namespace'].':';

$full_title = $_GET['title'];
$title = $api_doc['title'];
$content = htmlspecialchars_decode($api_doc['content']);
$datetime = $api_data['date'];
$rev = ($_GET['rev']);
$err_content = (isset($api['page']['error']))? $api['page']['error']['content'] : null;
$baserev = $api_data['editor']['baserev'];
$raw = $api_data['editor']['raw'];
$token = (isset($api_data['token']))? $api_data['token'] : null;
$api_error = $api['page']['error'];

switch ($pagename){
    case '':
        Header('Location: '.$conf['FullURL'].$uri['wiki'].$conf['FrontPage']);
        exit;
    case 'w':
    case 'jump':
        ($api_doc['content'] !== null)? $con = utf8_decode($content):$con = null;
        WikiSkin::ConstPage('wiki',[$full_title, $ns, $title, $con, $datetime, $rev, null, ['error' => $err_content, 'notice' => $api_data['discuss_progress']]]);
        unset($api);
        break;
    case 'edit':
        if(is_array($api_error)){
            if($api_error['missing_permission'] == 'permission_read')
                WikiSkin::ConstPage('error', [$err_content]);
            elseif($api_error['missing_permission'] == 'permission_edit')
                Header('Location: '.$conf['FullURL'].$uri['edit_request'].$full_title);
            elseif($api_error['missing_permission'] == 'permission_edit_request') // 편집요청 권한 없음.
                WikiSkin::ConstPage('wiki', [$_GET['title'], $ns, $title, utf8_decode($api_data['editor']['raw']), $datetime, $api_data['editor']['baserev'], 'disabled', ['errbox' => $api_error['content']]]);
        }else{
            if(isset($_POST['content']) && trim($_POST['content']) == trim($_SESSION['editor']['raw'])){
                $errMsg = $lang['msg:err_same_contents'];
                WikiSkin::ConstPage('edit', [$full_title, $ns, $title, trim($_POST['content']), $_SESSION['editor']['token'],$api_data['editor']['baserev'], utf8_decode($api_data['editor']['preview']), $errMsg]);
            }elseif(isset($_SESSION['editor']['fulltitle']) && isset($_SESSION['editor']['title']) && ($_SESSION['editor']['token'] == $_POST['token']) && isset($_SESSION['editor']['identifier']) && isset($_POST['content']) && isset($_POST['token'])){
                
                $identifier = explode(':',$_SESSION['editor']['identifier']);
                if(!$identifier[1]) {
                    WikiSkin::ConstPage('error','잘못된 접근입니다.');
                    exit;
                }

                if($identifier[0] === 'm'){
                    $id=$identifier[1]; $ip=null;
                }elseif($identifier[0] === 'i'){ 
                    $id=null; $ip=$identifier[1];
                }

                $action = (!$_SESSION['editor']['baserev'])? 'create':'modify';
                Docs::SaveDocument(array_search($_SESSION['editor']['namespace'], $_ns), $_SESSION['editor']['title'], trim($_POST['content']),$_POST['summary'],$action,$_SESSION['editor']['baserev'],iconv_strlen($_SESSION['editor']['raw']),$id,$ip);
                Header('Location: '.$conf['FullURL'].$uri['wiki'].$_SESSION['editor']['fulltitle']);
                unset($editor);
                exit;

            }else{
                if(isset($_POST['token']) && $_SESSION['editor']['token'] !== $_POST['token']){
                    $errMsg = $lang['msg:err_csrf_token'];
                    $raw = utf8_encode($_POST['content']);
                }
                $_SESSION['editor'] = array(
                    'fulltitle' => $full_title, 
                    'namespace' => $api_doc['namespace'],
                    'title' => $title,
                    'token' => $token,
                    'identifier' => $api['session']['identifier'],
                    'baserev' => $baserev
                );
                if(!isset($errMsg)) {
                    $errMsg = null;
                    $_SESSION['raw'] = utf8_decode($raw);
                }
                WikiSkin::ConstPage('edit',[$full_title, $ns, $title, utf8_decode($raw), $_SESSION['editor']['token'],$api_data['editor']['baserev'], utf8_decode($api_data['editor']['preview']), $errMsg]);
            }
        }
        unset($api);
        break;
    case 'acl':
        if(isset($_POST['target'])){ // 규칙 삭제
            $a = explode('.', $_POST['target']);
            if(!$api_data[$a[0].'ACL']['editable'])
                $err = 'no_acl_permission';
            elseif(!is_numeric($a[1]) || !in_array($a[0], ['doc','ns']))
                $err = 'invalid_arguments';
            elseif(!$err){
                ACL::deleteACL($a[0], $a[1]);
                Header('Location:'.$_conf['FullURL'].$_SERVER['REQUEST_URI']);
            }
        }else{
            if(floor($_POST['duration_raw']) == '0')
                $d = 0;
            else 
                $d = $_SERVER['REQUEST_TIME'] + floor($_POST['duration_raw']);

            if($_POST['target_type'] == 'member') $_POST['target_name'] = strtolower($_POST['target_name']);
            if(isset($_POST['acl_target'])){ // 규칙 추가
                $a = explode('.', $_POST['acl_target']);
                if(!$api_data[$a[0].'ACL']['editable']) // 권한 없음
                    $err = 'no_acl_permission';
                elseif(!in_array($a[1],ACL::$PRESSDO_ACLTYPES)) // 잘못된 타입
                    $err = 'invalid_acltype';
                elseif($a[0] == 'ns')
                    $da = ACL::addACL('ns',array_search($api_doc['namespace'], $_ns),null,$a[1],$_POST['target_type'].':'.$_POST['target_name'],$_POST['action'],$d);
                elseif($a[0] == 'doc')
                    $da = ACL::addACL('doc',array_search($api_doc['namespace'], $_ns),$title,$a[1],$_POST['target_type'].':'.$_POST['target_name'],$_POST['action'],$d);
                
                if(isset($da) && $da === false)
                    $err = 'acl_already_exists';
                else
                    Header('Location:'.$_conf['FullURL'].$_SERVER['REQUEST_URI']);
            }
        }
        WikiSkin::ConstPage('acl',[$err, $_GET['title'], $ns, $title, $api_data['docACL']['acls'], $api_data['nsACL']['acls'], $api_data['ACLTypes'], ['doc' => $api_data['docACL']['editable'], 'ns' => $api_data['nsACL']['editable']]]);
        break;
    case 'move':
        if(isset($_POST['token']) && $_SESSION['editor']['token'] !== $_POST['token']){
            $errMsg = $lang['msg:err_csrf_token'];
            $raw = $_POST['content'];
        }elseif(isset($api['page']['error'])){
            WikiSkin::ConstPage('error', [$api['page']['error']['content']]);
        }elseif(isset($_POST['token']) && $_SESSION['editor']['token'] == $_POST['token'] && isset($_POST['new_title'])){
            if($_SESSION['member'] == null){
                $ip = $_SESSION['ip'];
                $id = null;
            }else{
                $ip = null;
                $id = $_SESSION['member']['username'];
            }
            Docs::moveDocument($full_title, $_POST['new_title'], $id, $ip);
            Header('Location: '.$conf['FullURL'].$uri['wiki'].$_POST['new_title']);
        }else{
            $_SESSION['editor'] = array(
                'fulltitle' => $full_title, 
                'namespace' => $api_doc['namespace'],
                'title' => $title,
                'token' => $token,
                'identifier' => $api['session']['identifier'],
                'baserev' => $baserev,
                'raw' => $raw
            );
            WikiSkin::ConstPage('move', [$_GET['title'],$ns,$title,$token]);
        }
        break;
    case 'delete':
        WikiSkin::ConstPage('delete', [$_GET['title'],$ns,$title]);
        break;
    case 'history':
        if(is_array($api['page']['error'])){
            WikiSkin::ConstPage('error', [$err_content]);
        }else
            WikiSkin::ConstPage('history',[$_GET['title'], $ns, $title, $api_data['history'], $api_data['prev_ver'], $api_data['next_ver']]);
        unset($api);
        break;
    //case 'backlink':
        
    //    break;
    case 'raw':
        WikiSkin::ConstPage('wiki', [$_GET['title'], $ns, $title, $api_data['text'], $datetime, $_GET['rev'], 'raw']);
        unset($api);
        break;
    case 'diff':
        WikiSkin::ConstPage('wiki', [$_GET['title'], $ns, $title, utf8_decode($api_data['diff']), $datetime, $_GET['rev'], 'diff']);
        unset($api);
        break;
    //case 'blame':
        
    //    break;
    case 'random':
        $r = Docs::getRandom(1);
        if($conf['ForceShowNameSpace'] === false && $r[0]['namespace'] === 'document')
            $title = $r[0]['title'];
        else
            $title = $r[0]['namespace'].':'.$r[0]['title'];
        Header('Location: '.$conf['FullURL'].$uri['wiki'].$title);
        exit;
    case 'revert':
        
        break;
    case 'Upload':
        if(isset($_POST['fileInput'])){
            
        }
        WikiSkin::ConstPage('Upload', [$api_data]);
        break;
    case 'License':
        WikiSkin::ConstPage('License', [$api_data]);
        break;
    case 'RandomPage':
        if(isset($_POST['namespace']))
            Header('Location: '.$conf['FullURL'].$uri['RandomPage'].$uri['prefix'].'namespace='.$_POST['namespace']);
        WikiSkin::ConstPage('pageList', ['RandomPage', $api_data['content'], $api['page']['namespaces']]);
        break;
    case 'discuss':
        if(isset($_POST['topic']) && isset($_POST['text'])){
            if(iconv_strlen($_POST['text']) > 1000)
                $long = ['text',1000];
            if(iconv_strlen($_POST['topic']) > 100)
                $long = ['topic',100];
            if(array_search('create_thread', $api_data['perms']) === false)
                $err = 'no_permission';
            elseif(isset($long))
                $err = ['too_long', $long[0], $long[1]];
            else{
                $slug = Thread::createThread(array_search($api_doc['namespace'], $_ns), $api_doc['title'], $_POST['topic'], $_POST['text'], $api['session']['identifier']);
                Header('Location: '.$conf['FullURL'].$uri['thread'].$slug);
            }
        }
        WikiSkin::ConstPage('discuss', [$_GET['title'], $ns, $title, $erlist, $api_data['thread_list'],$err]);
        break;
    case 'thread':
        if(isset($_POST['text'])){
            if(iconv_strlen($_POST['text']) > 1000)
                $long = ['text',1000];
            if(array_search('write_thread_comment', $api_data['perms']) === false)
                $err = 'no_permission';
            elseif(isset($long))
                $err = ['too_long', $long[0], $long[1]];
            elseif($api_data['status'] !== 'normal')
                $err = 'thread_invalid_status';
            else{
                Thread::addThreadComment($api_data['slug'], $_POST['text'], $api['session']['identifier']);
                Header('Location: '.$conf['FullURL'].$uri['thread'].$api_data['slug']);
            }
        }elseif(in_array($_POST['status'], ['normal', 'pause', 'close']) && $api_data['updateThreadStatus'] === true){
            Thread::updateStatus($_GET['title'], $_POST['status'], $api['session']['member']['username']);
            Header('Location: '.$conf['FullURL'].$uri['thread'].$api_data['slug']);
        }elseif(isset($_POST['document']) && $api_data['updateThreadDocument'] === true){
            Thread::moveThread($_GET['title'], $_POST['document'], $api['session']['member']['username']);
            Header('Location: '.$conf['FullURL'].$uri['thread'].$api_data['slug']);
        }elseif(isset($_POST['topic']) && $api_data['updateThreadTopic'] === true){

        }
        WikiSkin::ConstPage('thread', [$ns, $title, $api_data, $err]);
        break;
    case 'edit_request':
        
        break;
    case 'new_edit_request':
        
        break;
    case 'RecentChanges':
        WikiSkin::ConstPage('RecentPage', ['RecentChanges',$api_data['content']]);
        break;
    case 'RecentDiscuss':
        WikiSkin::ConstPage('RecentPage', ['RecentDiscuss',$api_data['content']]);
        break;
    case 'BlockHistory':
        WikiSkin::ConstPage('BlockHistory', [$api_data]);
        break;
    /*case 'NeedPages':
        
        break;
    case 'OrphanedPages':
        
        break;
    case 'UncategorizedPages':
        
        break;*/
    case 'OldPages':
        WikiSkin::ConstPage('pageList', ['OldPages', $api_data['content'], $api['page']['namespaces']]);
        break;
    case 'ShortestPages':
        WikiSkin::ConstPage('pageList', ['ShortestPages', $api_data['content'], $api['page']['namespaces']]);
        break;
    case 'LongestPages':
        WikiSkin::ConstPage('pageList', ['LongestPages', $api_data['content'], $api['page']['namespaces']]);
        break;
    case 'internal':
        include 'internal.php';
        break;
    case 'aclgroup':
        if(isset($_POST['group'])){
            if(ACL::checkPerm('aclgroup', $_SESSION['member']['username']) === false)
                $errbox = 'no_permission';

            if(isset($_POST['id'])){

                if(!$_POST['note'])
                    $err = ['note'=> true, 'mode' => 'd'];

                if(!$err && !$errbox){
                    $q = ACL::remove_from_aclgroup($_POST['id'], $_SESSION['member']['username'], $_POST['note']);

                    if($q === false){
                        $errbox = 'err_database';
                    }else
                        Header('Location:'.$_conf['FullURL'].$_SERVER['REQUEST_URI']);
                }
            }else{
                if(floor($_POST['duration_raw']) > 29030400)
                    $err['expire_much'] = true;

                if(floor($_POST['duration_raw']) == '0')
                    $d = 0;
                else
                    $d = $_SERVER['REQUEST_TIME'] + floor($_POST['duration_raw']);
                if(!$_POST['note'])
                    $err['note'] = true;
                elseif(!isset($_POST['duration_raw']) || floor($_POST['duration_raw']) < 0)
                    $err['expire'] = true;
                elseif($_POST['mode'] == 'username' && !$_POST['username'])
                    $err['username'] = true;
                elseif($_POST['mode'] == 'ip' && !$_POST['ip'])
                    $err['ip'] = true;
                if($_POST['mode'] == 'ip'){
                    if(!preg_match('/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\/[0-9]{1,2}/', $_POST['ip']) && !preg_match('/^(([0-9a-fA-F]{1,4}:){7,7}[0-9a-fA-F]{1,4}|[0-9a-fA-F]{1,4}:((:[0-9a-fA-F]{1,4}){1,6})|([0-9a-fA-F]{1,4}:){1,2}(:[0-9a-fA-F]{1,4}){1,5}|([0-9a-fA-F]{1,4}:){1,3}(:[0-9a-fA-F]{1,4}){1,4}|([0-9a-fA-F]{1,4}:){1,4}(:[0-9a-fA-F]{1,4}){1,3}|([0-9a-fA-F]{1,4}:){1,5}(:[0-9a-fA-F]{1,4}){1,2}|([0-9a-fA-F]{1,4}:){1,6}:[0-9a-fA-F]{1,4}|([0-9a-fA-F]{1,4}:){1,7}:|:((:[0-9a-fA-F]{1,4}){1,7}|:)|fe80:(:[0-9a-fA-F]{0,4}){0,4}%[0-9a-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1})\/([1-9]|[1-9][0-9]|1[01][0-9]|12[0-8])$/', $_POST['ip']))
                        $err['ip'] = true;
                }elseif($_POST['mode'] == 'username'){
                    if(!Member::userExists($_POST['username']))
                        $err['username'] = true;
                }
                if(ACL::in_aclgroup($_POST['group'], $_POST['username'])[0] === true || ACL::in_aclgroup($_POST['group'], $_POST['ip'], 'CIDR') === true)
                    $errbox = 'aclgroup_already_exists';
                
                if(!in_array($_POST['group'], ACL::getACLgroups(true)))
                    $errbox = 'aclgroup_group_not_found';

                if(!$err && !$errbox){
                    ACL::addtoACLgroup($_SESSION['member']['username'],$_POST['ip'],$_POST['username'],$_POST['group'],$d,$_POST['note']);
                    Header('Location:'.$_conf['FullURL'].$_SERVER['REQUEST_URI']);
                }
            }
        }elseif(isset($_POST['name'])){
            if(ACL::checkPerm('aclgroup', $_SESSION['member']['username']) === false)
                $errbox = 'no_permission';
            elseif(ACL::groupExists($_POST['name']) === true){
                $errbox = 'aclgroup_group_already_exists';
                $err = ['mode' => 'c'];
            }elseif(!$_POST['name'])
                $err = ['name' => true, 'mode' => 'c'];
            
            if(!$err && !$errbox){
                ACL::addACLgroup($_POST['name'], $_POST['isAdmin'][0]);
                Header('Location:'.$_conf['FullURL'].$_SERVER['REQUEST_URI']);
            }
        }elseif(isset($_POST['delnm'])){
            if(ACL::checkPerm('aclgroup', $_SESSION['member']['username']) === false)
                $errbox = 'no_permission';
            
            if(!$err && !$errbox){
                ACL::delACLgroup($_POST['delnm']);
                Header('Location:'.$_conf['FullURL'].$_SERVER['REQUEST_URI']);
            }
        }
        WikiSkin::ConstPage('aclgroup', [[$errbox,$err], $api_data]);
        break;
    case 'member':
        switch($_GET['title']){
            case 'login':
               if(isset($api['session']['member']['username']) && $api_data['error'] == null){
                    $_SESSION = [
                        'menus' => [],
                        'member' => [
                            'user_document_discuss' => null,
                            'username' => $api['session']['member']['username'],
                            'gravatar_url' => $api['session']['member']['gravatar_url']
                        ],
                        'ip' => PressDo::getip()
                    ];
                    foreach ($api['session']['menus'] as $m){
                        array_push($_SESSION['menus'], ['l' => $uri[$m],'t' => $lang['menu:'.$m],'i' => $icon[$m]]);
                    }
                    Header('Location:'.base64_decode($_GET['redirect']));
                    exit;
                }else{
                    $ShowPage = 1;
                }
                if($ShowPage === 1){ 
                    WikiSkin::ConstPage('login',[$_GET['redirect'],$api_data['error']]);
                }
                break;
            case 'signup':
                if(!$_SESSION['active']){
                    session_save_path('./temp/');
                    ini_set('session.cache_expire', 86400);
                    ini_set('session.gc_maxlifetime', '86400');
                    session_start();
                }
                $_SESSION['active'] = true;
                if(isset($_POST['username']) && isset($_POST['password']) && isset($_SESSION['email'])){
                    if(Member::userExists($_POST['username']) === true){
                        WikiSkin::ConstPage('signup',[2, $lang['msg:err_username_exists']]);
                    }else{
                        Member::addUser($_POST['username'], hash('sha256', $_POST['password']), $_SESSION['email'], $_SERVER['HTTP_USER_AGENT']);
                        Docs::SaveDocument('user', $_POST['username'], '','','create',0,0,$_POST['username'],null);
                        WikiSkin::ConstPage('signup',[3]);
                        unset($_SESSION);
                    }
                    
                }elseif(isset($_GET['k'])){
                    // 인증링크 누름
                    if($_GET['k'] === @$_SESSION['key']){
                        WikiSkin::ConstPage('signup',[2]);
                    }else{
                        WikiSkin::ConstPage('error',[$lang['msg:err_invalid_url']]);
                    }
                }elseif(isset($_SESSION['MAIL_CHECK']) && isset($_POST['email'])){
                    // 이메일 입력 받음
                    if($conf['UseMailWhitelist'] === true && array_search(end(explode('@', $_POST['email'])), $conf['MailWhitelist']) === false)
                        WikiSkin::ConstPage('signup',[0, $lang['msg:err_mail_whitelist']]);
                    elseif(Member::mailExists($_POST['email']) === true){
                        WikiSkin::ConstPage('signup',[0, $lang['msg:err_email_exists']]);
                    }else{
                        $_SESSION['email'] = $_POST['email'];
                        if($conf['UseMailAuth'] === true){
                            // 메일 발송
                            $_SESSION['key'] = PressDo::rand(64);
                            $authURL = ($conf['UseShortURI'])? $conf['FullURL'].'/member/signup/'.$_SESSION['key']: $conf['FullURL'].'/index.php?page=member&title=signup&k='.$_SESSION['key'];
                            $send = mail_send($_POST['email'], str_replace('@1@', $conf['SiteName'], $lang['mail:signup_title']), str_replace(['@1@', '@2@', '@3@'], [$conf['SiteName'], $authURL, PressDo::getip()], $lang['mail:signup']));
                            if(!$send)
                                return $send;
                            else
                                WikiSkin::ConstPage('signup',[1]);
                        }else{
                            unset($_SESSION['MAIL_CHECK']);
                            WikiSkin::ConstPage('signup',[2]);
                        }
                    }
                }else{
                    // 초기 접속
                    WikiSkin::ConstPage('signup',[]);
                }
                break;
            case 'logout':
                session_destroy();
                Header('Location:'.base64_decode($_GET['redirect']));
                exit;
            case 'mypage':
                //WikiSkin::mypage();
                break;
        }
        break;
    case 'admin':
        if(isset($api_data['error']))
            WikiSkin::ConstPage('error', [$lang['msg:admin_'.$api_data['error']]]);
        else{
            if($_GET['title'] == 'grant'){
                if(isset($_POST['username']) && Member::userExists($_POST['username']) === false)
                    $data = ['errbox' => 'user_not_exist'];
                elseif(isset($_POST['member']) && is_array($_POST['perms'])){
                    if($_POST['perms'] !== Member::getPermsAnd($_GET['username'])[0])
                        Member::grantUser($_POST['member'], $_POST['perms'], $api['session']['member']['username']);
                    Header('location:'.$_conf['FullURL'].$uri['admin'].$_GET['title'].$uri['prefix'].'username='.$_POST['member']);
                }elseif(isset($_POST['username']))
                    Header('location:'.$_conf['FullURL'].$uri['admin'].$_GET['title'].$uri['prefix'].'username='.Member::getPermsAnd($_POST['username'])[2]);
                elseif(isset($_GET['username'])){
                    $data['perms'] = Member::$PRESSDO_PERMS;
                    $data['have'] = Member::getPermsAnd($_GET['username'])[0];
                }
            }elseif($_GET['title'] == 'login_history'){
                if(isset($_POST['username']) && Member::userExists($_POST['username']) === false)
                    $data = ['errbox' => 'user_not_exist'];
                elseif(isset($_POST['username']))
                    Header('location:'.$_conf['FullURL'].$uri['admin'].$_GET['title'].$uri['prefix'].'username='.Member::getPermsAnd($_POST['username'])[2]);
                elseif(isset($_GET['username'])){
                    $dat = Member::loginHistory($_GET['username'], $api['session']['member']['username'],$_GET['from'],$_GET['until']);
                    $d = Member::getPermsAnd($_GET['username']);
                    $data['from'] = null;
                    $data['until'] = null;
                    $data['UA'] = $d[4];
                    $data['email'] = $d[3];
                    $data['history'] = [];
                    foreach ($dat as $h){
                        array_push($data['history'], [
                            'datetime' => $h['datetime'],
                            'ip' => $h['ip']
                        ]);
                    }
                    if($data['history'][0]['datetime'] != Member::LoginTime($_GET['username'], 'r'))
                        $data['until'] = $data['history'][0]['datetime'] + 1;
                    if (end($data['history'])['datetime'] != Member::LoginTime($_GET['username'], 'o'))
                        $data['from'] = end($data['history'])['datetime'] - 1;
                }
            }
            WikiSkin::ConstPage('admin',[$_GET['title'],$data]);
        }
        break;
}
