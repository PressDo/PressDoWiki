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
    return 404;
    exit;
}else{
    $Title = $_GET['title'];
}
    $Doc_content = Data::LoadLatestDocument($Title);
    WikiSkin::FullPage($Title, $Doc_content[1], 'edit');

?>
