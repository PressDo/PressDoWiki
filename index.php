<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\SQL;
include 'config.php';
require_once 'PressDoLib.php';
require_once './skin/'.$conf['Skin'].'/skin.php';

if(!$_GET['title']){
    $Title = $conf['Title'];
}else{
    $Title = $_GET['title'];
}

if($_GET['action'] == 'save'){
    // 편집 저장
}elseif($_GET['action'] == 'edit'){
    // 편집 모드
}elseif($_GET['action'] == 'move'){
    // 문서이동 모드
}elseif($_GET['action'] == 'delete'){
    // 삭제 모드
}elseif($_GET['action'] == 'acl'){
    // ACL 모드
}elseif($_GET['action'] == 'view' || !$_GET['action']){
    // 보기 모드
WikiSkin::FullPage($Title, PressDo::readSyntax(SQL::LoadDocument($_GET['title'])));
}

?>
