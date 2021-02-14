<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';

switch ($_SERVER['REQUEST_URI']){
case '/':
    Header('Location: http://'.$conf['Domain'].$conf['ViewerUri'].$conf['Title']);
    exit;
case '/pages/view.php':
    Header("HTTP/2.0 403 Forbidden");
    exit;
}

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(isset($_GET['title'])){
    $Title = $_GET['title'];
}

if($_POST['action'] == 'save'){
    // 편집 저장

    Data::SaveDocument($Title, $_POST['content'], $_POST['summary']);
}

$Doc_content = Data::LoadLatestDocument($Title);
WikiSkin::FullPage($Title, 'view');
if(isset($_GET['rev']) && $_GET['rev'] !== $Doc_content[1]['version']){
$Doc_content = Data::LoadOldDocument($Title, $_GET['rev']);
}
$array = $Doc_content[1];
unset($Doc_content);
$Doc = $Title;
if (!$array['content']) {
    // 없는 문서?>
    <div pressdo-content>
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a></h1>
    <span> 해당 문서를 찾을 수 없습니다. </span><br>
    <p><a href="/edit/<?=$Doc?>">[새 문서 만들기]</a></p>
</div><?php
} else { ?>
    <br>
    <div pressdo-content>
        <div pressdo-toolbar>
            <div pressdo-toolbar-menu>
                <a pressdo-toolbar-link href="/backlink/<?=$Doc?>">역링크</a>
                <a pressdo-toolbar-link href="/discuss/<?=$Doc?>">토론</a>
                <a pressdo-toolbar-link href="/edit/<?=$Doc?>">편집</a>
                <a pressdo-toolbar-link href="/history/<?=$Doc?>">역사</a>
                <a pressdo-toolbar-link pressdo-toolbar-last href="/acl/<?=$Doc?>">ACL</a>
            </div>
        </div>
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a>
            <?php if(isset($_GET['rev'])){ ?><small pressdo-doc-action>(r<?=$_GET['rev']?> 판)</small><?php } ?>
        </h1>
        <p pressdo-doc-changed>최근 수정 시각: <?=$array['savetime']?></p>
        <div id="cont_ent" pressdo-doc-content>
            <div id="categoryspace_top"></div>
            <?=str_replace('@@@PressDo-Replace-Title-Here@@@', urlencode($Title), PressDo::readSyntax($array['content']));?>
            <script>
                // 분류 위치 조정
                var f = document.getElementById('categories');
                var parent = f.parentNode.parentNode;
                parent.insertBefore(f, parent.childNodes[0]);
            </script>
        </div>
        <footer pressdo-con-footer>
                <p><?=$conf['comments']?></p>
            </footer>
    </div><?php

}
WikiSkin::footer();
?>
