<?php
use PressDo\PressDo;
use PressDo\Docs;
use PressDo\WikiSkin;
use PressDo\Pages;
require_once 'PressDoLib.php';
require_once 'skin/liberty/skin.php';
$pagename = $_GET['page'];
if($conf['UseShortURI'])
   $api = fn() => PressDo::requestAPI('http://'.$conf['Domain'].'/internal'.$_SERVER['REQUEST_URI'], $_SESSION);
else
   $api = fn() => PressDo::requestAPI('http://'.$conf['Domain'].'/internal.php?'.$_SERVER['QUERY_STRING'], $_SESSION);
($api()['page']['data']['document']['namespace'] == $_ns['document'])? $ns = '':$ns = $api()['page']['data']['document']['namespace'].':';
switch ($pagename){
    case '':
        Header('Location: http://'.$conf['Domain'].$uri['wiki'].$conf['FrontPage']);
        break;
    case 'w':
    case 'jump':
        WikiSkin::wiki($_GET['title'], $ns, $api()['page']['data']['document']['title'], $api()['page']['data']['document']['content'], $api()['page']['data']['date']);
        break;
    case 'edit':
        WikiSkin::edit($_GET['title'], false);
        break;
    case 'acl':
        
        break;
    case 'move':
        
        break;
    case 'delete':
        
        break;
    case 'history':
        WikiSkin::history($_GET['title'], false);
        break;
    case 'backlink':
        
        break;
    case 'raw':
        
        break;
    case 'blame':
        
        break;
    case 'random':
        
        break;
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
    default:
        break;
}
