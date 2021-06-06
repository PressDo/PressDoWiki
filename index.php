<?php
session_start();
use PressDo\PressDo;
use PressDo\Docs;
use PressDo\WikiSkin;
use PressDo\Pages;
use PressDo\ACL;
use PressDo\Member;
require 'PressDoLib.php';
require 'skin/liberty/skin.php';
$_SESSION['ip'] = PressDo::getip();
$pagename = $_GET['page'];
if($conf['UseShortURI'])
    $api = PressDo::requestAPI('http://'.$conf['Domain'].'/internal'.$_SERVER['REQUEST_URI'], $_SESSION);
else
    $api = PressDo::requestAPI('http://'.$conf['Domain'].'/internal.php?'.$_SERVER['QUERY_STRING'], $_SESSION);
($api['page']['data']['document']['namespace'] == $lang['ns:document'])? $ns = '':$ns = $api['page']['data']['document']['namespace'].':';
switch ($pagename){
    case '':
        Header('Location: http://'.$conf['Domain'].$uri['wiki'].$conf['FrontPage']);
        exit;
    case 'w':
    case 'jump':
        WikiSkin::wiki($_GET['title'], $ns, $api['page']['data']['document']['title'], $api['page']['data']['document']['content'], $api['page']['data']['date'], $_GET['rev'], false, $api['page']['error']['content']);
        unset($api);
        break;
    case 'edit':
        if(is_array($api['page']['error'])){
            WikiSkin::error($api['page']['error']['content']);
        }else{
        if(isset($_POST['content']) && $_POST['content'] == $_SESSION['editor']['raw']){
            $errMsg = '문서 내용이 같습니다.';
            WikiSkin::edit($_GET['title'], $ns, $api['page']['data']['document']['title'], $api['page']['data']['editor']['raw'], $_SESSION['token'],$api['page']['data']['editor']['baserev'], $api['page']['data']['editor']['preview'], $errMsg);
        }elseif(isset($_SESSION['editor']['fulltitle']) && isset($_SESSION['editor']['title']) && ($_SESSION['editor']['token'] == $_POST['token']) && isset($_SESSION['editor']['identifier']) && isset($_POST['content']) && isset($_POST['token'])){
            $identifier = explode(':',$_SESSION['editor']['identifier']);
            if(!isset($identifier[1])) {
                WikiSkin::error('잘못된 접근입니다.');
                exit;
            }
            if($identifier[0] === 'm'){ $id=$identifier[1]; $ip=null;}elseif($identifier[0] === 'i'){ $id=null; $ip=$identifier[1];}
            if(!$_SESSION['editor']['baserev'])
                $action = 'create';
            else
                $action = 'modify';
            // 세션 데이터 + 토큰 일치 + 편집값 있음
            Docs::SaveDocument(rawurlencode(array_search($_SESSION['editor']['namespace'], $_ns)), rawurlencode($_SESSION['editor']['title']), $_POST['content'], rawurlencode($_POST['summary']),$action,$id,$ip);
            Header('Location: http://'.$conf['Domain'].$uri['wiki'].$_SESSION['editor']['fulltitle']);
            unset($_SESSION['editor']);
            exit;
        }else{
            if(isset($_POST['token']) && $_SESSION['editor']['token'] !== $_POST['token']) $errMsg = 'CSRF 방지 토큰이 일치하지 않습니다.';
            if(!$errMsg) $errMsg = null;
            $_SESSION['editor'] = array(
                'fulltitle' => $_GET['title'], 
                'namespace' => $api['page']['data']['document']['namespace'],
                'title' => $api['page']['data']['document']['title'],
                'token' => $api['page']['data']['token'],
                'identifier' => $api['session']['identifier'],
                'baserev' => $api['page']['data']['editor']['baserev'],
                'raw' => $api['page']['data']['editor']['raw']
            );
            WikiSkin::edit($_GET['title'], $ns, $api['page']['data']['document']['title'], $api['page']['data']['editor']['raw'], $_SESSION['editor']['token'],$api['page']['data']['editor']['baserev'], $api['page']['data']['editor']['preview'], $errMsg);
        }
        }
        unset($api);
        break;
    case 'acl':
        WikiSkin::acl($_GET['title'], $ns, $api['page']['data']['document']['title'], $api['page']['data']['docACL']['acls'], $api['page']['data']['nsACL']['acls'], $api['page']['data']['ACLTypes']);
        break;
    case 'move':
        break;
    case 'delete':
        break;
    case 'history':
        WikiSkin::history($_GET['title'], $ns, $api['page']['data']['document']['title'], $api['page']['data']['history'], $api['page']['data']['prev_ver'], $api['page']['data']['next_ver']);
        unset($api);
        break;
    case 'backlink':
        
        break;
    case 'raw':
        WikiSkin::wiki($_GET['title'], $ns, $api['page']['data']['document']['title'], $api['page']['data']['text'], $api['page']['data']['date'], $_GET['rev'], true);
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
        Header('Location: http://'.$conf['Domain'].$uri['wiki'].$title);
        exit;
    case 'revert':
        
        break;
    case 'aclgroup':
        
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
    case 'member':
        switch($_GET['title']){
            case 'login':
                if(isset($_POST['username']) && isset($_POST['password'])){
                    $l = Member::loginUser($_POST['username'], $_POST['password'], $_SERVER['REQUEST_TIME'], PressDo::getip(), $_SERER['HTTP_USER_AGENT']);
                    if(!$l){
                        $ShowPage = 1;
                        $LoginError = 1;
                    }else{
                        $_SESSION = array(
                            'menus' => [],
                            'member' => [
                                'user_document_discuss' => null,
                                'username' => $_POST['username'],
                                'gravatar_url' => $l
                            ],
                            'ip' => PressDo::getip()
                        );
                        Header('Location:'.base64_decode($_GET['redirect']));
                    }
                }else{
                    $ShowPage = 1;
                }
                if($ShowPage == 1){ 
                    WikiSkin::login($_GET['redirect']);
                }
                break;
            case 'signup':
                WikiSkin::signup();
            case 'logout':
            case 'mypage':
                WikiSkin::mypage();
                break;
        }
        break;
    default:
        break;
}
