<?php
if(!($_GET['page'] == 'member' && $_GET['title'] == 'signup')) session_start();
use PressDo\PressDo;
use PressDo\Docs;
use PressDo\WikiSkin;
use PressDo\Pages;
use PressDo\ACL;
use PressDo\Member;
require 'PressDoLib.php';
require 'skin/liberty/skin.php';
if($conf['UseMailAuth'] === true){
    require 'Mailer.php';
}
$_SESSION['POST']= @$_POST;
$_SESSION['ua'] = $_SERVER['HTTP_USER_AGENT'];
$_SESSION['ip'] = PressDo::getip();
$pagename = $_GET['page'];
//var_dump($api);
($conf['UseShortURI'])? $_url=$conf['FullURL'].'/internal'.$_SERVER['REQUEST_URI'] : $_url=$conf['FullURL'].'/internal.php?'.$_SERVER['QUERY_STRING'];
$api = PressDo::requestAPI($_url, $_SESSION);
$api_data = @$api['page']['data'];
$api_doc = @$api_data['document'];
if(isset($api_doc['namespace']))
    ($api_doc['namespace'] == $lang['ns:document'])? $ns = '' : $ns = $api_doc['namespace'].':';

$full_title = $_GET['title'];
$title = @$api_doc['title'];
$content = htmlspecialchars_decode($api_doc['content']);
$datetime = @$api_data['date'];
$rev = @($_GET['rev']);
$err_content = (isset($api['page']['error']))? $api['page']['error']['content'] : null;
$baserev = @$api_data['editor']['baserev'];
$raw = @$api_data['editor']['raw'];
$token = (isset($api_data['token']))? $api_data['token'] : null;

switch ($pagename){
    case '':
        Header('Location: '.$conf['FullURL'].$uri['wiki'].$conf['FrontPage']);
        exit;
    case 'w':
    case 'jump':
        WikiSkin::wiki($full_title, $ns, $title, $content, $datetime, $rev, false, $err_content);
        unset($api);
        break;
    case 'edit':
        if(is_array($api['page']['error'])){
            WikiSkin::error($err_content);
        }else{
            if(isset($_POST['content']) && $_POST['content'] == $_SESSION['editor']['raw']){
                $errMsg = '문서 내용이 같습니다.';
                WikiSkin::edit($full_title, $ns, $title, $api_data['editor']['raw'], $_SESSION['editor']['token'],$api_data['editor']['baserev'], $api_data['editor']['preview'], $errMsg);
            }elseif(isset($_SESSION['editor']['fulltitle']) && isset($_SESSION['editor']['title']) && ($_SESSION['editor']['token'] == $_POST['token']) && isset($_SESSION['editor']['identifier']) && isset($_POST['content']) && isset($_POST['token'])){
                $identifier = explode(':',$_SESSION['editor']['identifier']);
                if(!$identifier[1]) {
                    WikiSkin::error('잘못된 접근입니다.');
                    exit;
                }
                if($identifier[0] === 'm'){ 
                    $id=$identifier[1]; $ip=null;
                }elseif($identifier[0] === 'i'){ 
                    $id=null; $ip=$identifier[1];
                }
                if(!$_SESSION['editor']['baserev'])
                    $action = 'create';
                else
                    $action = 'modify';
                Docs::SaveDocument(array_search($_SESSION['editor']['namespace'], $_ns), rawurlencode($_SESSION['editor']['title']), $_POST['content'],$_POST['summary'],$action,$_SESSION['editor']['baserev'],iconv_strlen($_SESSION['editor']['raw']),$id,$ip);
                Header('Location: '.$conf['FullURL'].$uri['wiki'].$_SESSION['editor']['fulltitle']);
                unset($editor);
                exit;
            }else{
                if(isset($_POST['token']) && $_SESSION['editor']['token'] !== $_POST['token']){
                    $errMsg = $lang['msg:err_csrf_token'];
                    $raw = $_POST['content'];
                }
                if(!isset($errMsg)) $errMsg = null;
                $_SESSION['editor'] = array(
                    'fulltitle' => $full_title, 
                    'namespace' => $api_doc['namespace'],
                    'title' => $title,
                    'token' => $token,
                    'identifier' => $api['session']['identifier'],
                    'baserev' => $baserev,
                    'raw' => $raw
                );
                WikiSkin::edit($full_title, $ns, $title, $raw, $_SESSION['editor']['token'],$api_data['editor']['baserev'], $api_data['editor']['preview'], $errMsg);
            }
        }
        unset($api);
        break;
    case 'acl':
        WikiSkin::acl($_GET['title'], $ns, $title, $api_data['docACL']['acls'], $api_data['nsACL']['acls'], $api_data['ACLTypes'], ['doc' => $api_data['docACL']['editable'], 'ns' => $api_data['nsACL']['editable']]);
        break;
    case 'move':
        break;
    case 'delete':
        break;
    case 'history':
        WikiSkin::history($_GET['title'], $ns, $title, $api_data['history'], $api_data['prev_ver'], $api_data['next_ver']);
        unset($api);
        break;
    case 'backlink':
        
        break;
    case 'raw':
        WikiSkin::wiki($_GET['title'], $ns, $title, $api_data['text'], $datetime, $_GET['rev'], true);
        unset($api);
        break;
    case 'blame':
        
        break;
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
        
        break;
    case 'License':
        
        break;
    case 'RandomPage':
        
        break;
    case 'discuss':
        
        break;
    case 'edit_request':
        
        break;
    case 'new_edit_request':
        
        break;
    case 'RecentChanges':
        WikiSkin::RecentChanges($api_data['content']);
        break;
    case 'RecentDiscuss':
        break;
    case 'NeedPages':
        
        break;
    case 'OrphanedPages':
        
        break;
    case 'UncategorizedPages':
        
        break;
    case 'OldPages':
        
        break;
    case 'ShortestPages':
        
        break;
    case 'LongestPages':
        
        break;
    case 'login_history':
        
        break;
    case 'grant':
        
        break;
    case 'LongestPages':
        
        break;
    case 'internal':
        include 'internal.php';
        break;
    case 'aclgroup':
        WikiSkin::aclgroup($api_data);
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
                    WikiSkin::login($_GET['redirect'],$api_data['error']);
                }
                break;
            case 'signup':
                if(!$_SESSION['active']){
                    session_save_path('./temp/');
                    ini_set('session.cache_expire', 86400);
                    ini_set('session.gc_maxlifetime', '86400');
                    session_start();
                }
                $_SESSION['active'] = 1;
                if(isset($_POST['username']) && isset($_POST['password'])){
                    Member::addUser($_POST['username'], hash('sha256', $_POST['password']), $_SESSION['email'], $_SERVER['HTTP_USER_AGENT']);
                    Docs::SaveDocument('user', $_POST['username'], '','','create',$_POST['username'],null);
                    WikiSkin::signup(3);
                }elseif(isset($_GET['k'])){
                    // 인증링크 누름
                    if($_GET['k'] === @$_SESSION['key']){
                        WikiSkin::signup(2);
                    }else{
                        WikiSkin::error($lang['msg:err_invalid_url']);
                    }
                }elseif(isset($_SESSION['MAIL_CHECK']) && isset($_POST['email'])){
                    // 이메일 입력 받음
                    if($conf['UseMailWhitelist'] === true && array_search(end(explode('@', $_POST['email'])), $conf['MailWhitelist']) === false)
                        WikiSkin::signup(0, $lang['msg:err_mail_whitelist']); // 허용 메일 목록 불일치
                    else{
                        $_SESSION['email'] = $_POST['email'];
                        if($conf['UseMailAuth'] === true){
                            // 메일 발송
                            $_SESSION['key'] = PressDo::rand(64);
                            $authURL = ($conf['UseShortURI'])? $conf['FullURL'].'/member/signup/'.$_SESSION['key']: $conf['FullURL'].'/internal.php?page=member&title=signup&k='.$_SESSION['key'];
                            mail_send($_POST['email'], str_replace('@1@', $conf['SiteName_en'], $lang['mail:signup_title']), str_replace(['@1@', '@2@', '@3@'], [$conf['SiteName'], $authURL, PressDo::getip()], $lang['mail:signup']));
                            WikiSkin::signup(1);
                        }else{
                            unset($_SESSION['MAIL_CHECK']);
                            WikiSkin::signup(2);
                        }
                    }
                }else{
                    // 초기 접속
                    WikiSkin::signup();
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
        if(isset($acl_data['error']))
            WikiSkin::error($acl_data['error']);
        else
            WikiSkin::admin($_GET['title']);
        break;
}
