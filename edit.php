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
$Doc = $Title;
$array = $Doc_content[1];
?><h1><a href="index.php?title=<?=$Doc?>"><?=$Doc?></a> (편집) </h1>
<div align="right">
    <button type="button" onclick="goMenu('xref');">역링크</button>
    <button type="button" onclick="goMenu('delete');">삭제</button>
    <button type="button" onclick="goMenu('move');">이동</button>
</div>
<?php if (!$array['content']) { ?>
    <p> 새 문서를 생성합니다. </p>
<?php } else { ?>
    <p> 문서를 편집하는 중입니다. </p>
<?php } ?><hr>
<form method="post" action="index.php">
    <input type="hidden" name="title" value=<?=$Doc?>>
    <input type="hidden" name="action" value="save">
    <textarea name="content" style="width:95%; height:30rem; font-size:10pt;"><?=$array['content']?></textarea>
    <p>편집 요약</p>
    <input type="text" name="summary" style="width:95%;">
    <p><input type="checkbox" name="agree"> 문서 편집을 저장하면 당신은 기여한 내용을 CC-BY-NC-SA 2.0 KR으로 배포하고 기여한 문서에 대한 하이퍼링크나 URL을 이용하여 저작자 표시를 하는 것으로 충분하다는 데 동의하는 것입니다. 이 동의는 철회할 수 없습니다.</p><?php
if (!$_SESSION['userid']) { ?>
    <b>로그인하지 않은 상태로 편집하고 있습니다. 저장 시 아이피(<?=PressDo::getip()?>)가 영구히 기록됩니다.</b><?php
} ?>
    <div align="right"><input type="submit" value="저장" style="width:15%; height:5%; right:5%;"></div>
</form><?php
WikiSkin::footer();
?>
