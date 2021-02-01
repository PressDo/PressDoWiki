<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
include '../config.php';
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';

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
?>
<div pressdo-content>
    <div pressdo-toolbar>
        <div pressdo-toolbar-menu>
            <a pressdo-toolbar-link href="/backlink/<?=$Doc?>">역링크</a>
            <a pressdo-toolbar-link pressdo-toolbar-d rel="nofollow" href="/delete/<?=$Doc?>">삭제</a>
            <a pressdo-toolbar-link pressdo-toolbar-last rel="nofollow" href="/move/<?=$Doc?>">이동</a>
        </div>
    </div>
    <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a> 
<small pressdo-doc-action><?php  if(!$array['version']) {echo '(새 문서 생성)';}else{echo "(r".$array['version'] ." 편집)";} ?>
</small> </h1>
    <?php if (!$array['content']) { ?>
        <p> 새 문서를 생성합니다. </p>
    <?php } else { ?>
        <p> 문서를 편집하는 중입니다. </p>
    <?php } 
        if(isset($_POST['content'])){
            $Con = $_POST['content'];
        }else{
            $Con = $array['content'];
        }



?><hr>
<form method="post" name="editForm" id="editForm">
    <input type="hidden" name="title" value=<?=$Doc?>>
    <textarea name="content" style="width:95%; height:30rem; font-size:10pt;"><?=$Con?></textarea>
    <p>편집 요약</p>
    <input type="text" name="summary" style="width:95%;">
    <p><input type="checkbox" name="agree"> 문서 편집을 저장하면 당신은 기여한 내용을 CC-BY-NC-SA 2.0 KR으로 배포하고 기여한 문서에 대한 하이퍼링크나 URL을 이용하여 저작자 표시를 하는 것으로 충분하다는 데 동의하는 것입니다. 이 동의는 철회할 수 없습니다.</p><?php
if (!$_SESSION['userid']) { ?>
    <b>로그인하지 않은 상태로 편집하고 있습니다. 저장 시 아이피(<?=PressDo::getip()?>)가 영구히 기록됩니다.</b><?php
} ?>
    <div align="right">
        <button type="button" onclick="editorMenu('preview');">미리 보기</button>
        <button type="button" onclick="editorMenu('save');">저장</button>
        <script>
    // 버튼
    var f = document.getElementById('editForm');
    var a = document.createElement('input');
    a.setAttribute('type', 'hidden');
    a.setAttribute('name', 'action');
    function editorMenu(action) {
        a.setAttribute('value', action);
        f.appendChild(a);
        document.body.appendChild(f);
        if(action == 'edit') f.action = '/edit/<?=$Doc?>';
        if(action == 'save') f.action = '<?=$conf['ViewerUri'].$Doc?>';
        f.submit();
    }
</script>
    </div>
</form><?php
if($_POST['action'] == 'preview'){
    ?><p> 아래는 저장되지 않은 미리 보기의 모습입니다. </p><hr><?php
    echo PressDo::readSyntax($_POST['content'], 1);
}
WikiSkin::footer();
?>
