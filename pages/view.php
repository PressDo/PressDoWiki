<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
include '../config.php';
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';

if($_SERVER['REQUEST_URI'] == '/'){
Header('Location: http://'.$conf['Domain'].$conf['ViewerUri'].$conf['Title']);
exit;
}

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(isset($_GET['title'])){
    $Title = $_GET['title'];
}

if($_POST['action'] == 'save'){
    // 편집 저장

    var_dump(Data::SaveDocument($Title, $_POST['content'], $_POST['summary']));
}
$Doc_content = Data::LoadLatestDocument($Title);
WikiSkin::FullPage($Title, $Doc_content[1], 'view');
$array = $Doc_content[1];
unset($Doc_content);
$Doc = $Title;
if (!$array['content']) {
    // 없는 문서?>
    <h2> 문서를 찾을 수 없음 </h2><br>
    <p> 존재하지 않는 문서입니다. </p>
    <a href="/edit/<?=$Doc?>">문서 만들기</a><?php
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
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a></h1>
        <p pressdo-doc-changed>최근 수정 시각: <?=$array['savetime']?></p>
        <div id="cont_ent" pressdo-doc-content>
            <div id="categoryspace_top"></div>
            <?=str_replace('@@@PressDo-Replace-Title-Here@@@', urlencode($Title), PressDo::readSyntax($array['content']));?>
<script>
var f = document.getElementById('categories');
var parent = f.parentNode;
parent.insertBefore(f, parent.childNodes[0]);
</script>
        </div>
    </div><?php

}
WikiSkin::footer();
?>