<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
include 'config.php';
require_once 'PressDoLib.php';
require_once './skin/'.$conf['Skin'].'/skin.php';

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(!$_GET['title']){
    $Title = $conf['Title'];
}else{
    $Title = $_GET['title'];
}

if($_POST['action'] == 'save'){
    $act = 'save';
}

// 제목 조작 방지
function getTitle(){
    parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $i);
    if(!$i['title']){
        return false;
    }else{
        return $i['title'];
    }
}
if($act == 'save'){
    // 편집 저장
    if(!getTitle() || getTitle() !== $Title){
        echo '잘못된 시도입니다.';
        exit;
    }
    Data::SaveDocument(getTitle(), $_POST['content'], $_POST['summary']);
}
    $Doc_content = Data::LoadLatestDocument($Title);
    WikiSkin::FullPage($Title, $Doc_content[1], 'view');

?>
