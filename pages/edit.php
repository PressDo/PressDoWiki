<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';
if($_SERVER['REQUEST_URI'] == '/pages/edit.php'){
    Header("HTTP/2.0 403 Forbidden");
    exit;
}

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(!$_GET['title']){
    return 404;
    exit;
}else{
    $Title = $_GET['title'];
}
$Doc_content = Data::LoadLatestDocument($Title);
WikiSkin::FullPage($Title, 'edit');
$Doc = $Title;
$array = $Doc_content[1];
?>
<div pressdo-content>
    <div pressdo-content-header>
        <div pressdo-toolbar>
            <div pressdo-toolbar-menu>
                <a pressdo-toolbar-link href="/backlink/<?=$Doc?>">역링크</a>
                <a pressdo-toolbar-link pressdo-toolbar-d rel="nofollow" href="/delete/<?=$Doc?>">삭제</a>
                <a pressdo-toolbar-link pressdo-toolbar-last rel="nofollow" href="/move/<?=$Doc?>">이동</a>
            </div>
        </div>
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a> 
            <small pressdo-doc-action><?php  if(!$array['version']) {echo '(새 문서 생성)';}else{echo "(r".$array['version'] ." 편집)";} ?></small>
        </h1>
    </div>
    <?php if (!$array['content']) { ?>
        <p> 새 문서를 생성합니다. </p>
    <?php } else { ?>
        <p> 문서를 편집하는 중입니다. </p>
    <?php } 
        if(isset($_POST['content'])){
            $Con = $_POST['content'];
        }else{
            $Con = $array['content'];
        }?>
        <pressdo-anchor id="_pressdo-form-anchor">
    <form method="post" name="editForm" id="editForm" enctype="multipart/form-data">
        <input type="hidden" name="title" value="<?=$Doc?>">
        <textarea pressdo-editor name="content"><?=$Con?></textarea>
        <p>요약</p>
        <input pressdo-edit-summary type="text" name="summary">
        <p><input type="checkbox" name="agree" id="agree"> <label for="agree">문서 편집을 저장하면 당신은 기여한 내용을 CC-BY-NC-SA 2.0 KR으로 배포하고 기여한 문서에 대한 하이퍼링크나 URL을 이용하여 저작자 표시를 하는 것으로 충분하다는 데 동의하는 것입니다. 이 동의는 철회할 수 없습니다.</label></p><?php
        if (!$_SESSION['userid']) { ?>
            <b>로그인하지 않은 상태로 편집하고 있습니다. 저장 시 아이피(<?=PressDo::getip()?>)가 영구히 기록됩니다.</b><?php
        } ?>
        <div pressdo-buttonarea>
            <button pressdo-button type="button" onclick="editorMenu('preview');">미리 보기</button>
            <button pressdo-button-blue pressdo-button style="margin-top:0; margin-left:5px;" type="button" onclick="editorMenu('save');">저장</button>
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
                n = document.getElementById('_pressdo-form-anchor')
                if(action == 'preview'){
                    f.action = '/edit/<?=urlencode($Doc)?>';
                    var parent = n.parentNode;
                    parent.insertBefore(f, parent.childNodes[6]);
                }
                if(action == 'save'){
                    if(!document.editForm.agree.checked){
                        alert('수정하기 전에 먼저 문서 배포 규정에 동의해 주세요.');
                        var parent = n.parentNode;
                        parent.insertBefore(f, parent.childNodes[6]);
                        return false;
                    }
                    f.action = '<?=$conf['ViewerUri'].urlencode($Doc)?>'; 
                }
                f.submit();
            }
            </script>
        </div>
    </form><?php
    if($_POST['action'] == 'preview'){
        ?><p> 아래는 저장되지 않은 미리 보기의 모습입니다. </p><hr><?php
        echo str_replace('@@@PressDo-Replace-Title-Here@@@', urlencode($Title), PressDo::readSyntax($_POST['content'], 1));
    }?></div><?php
WikiSkin::footer();
?>
