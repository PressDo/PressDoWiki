<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';

switch ($_SERVER['REQUEST_URI']){
case '/pages/raw.php':
    Header("HTTP/2.0 403 Forbidden");
    exit;
}

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(isset($_GET['title'])){
    $Title = $_GET['title'];
}

$Doc_content = Data::LoadLatestDocument($Title);
if(isset($_GET['rev']) && $_GET['rev'] !== $Doc_content[1]['version']){
$Doc_content = Data::LoadOldDocument($Title, $_GET['rev']);
}
WikiSkin::FullPage($Title, $Doc_content[1], 'view');
$array = $Doc_content[1];
unset($Doc_content);
$Doc = $Title;
if (!$array['content']) {
    // 없는 문서?>
    <div pressdo-content>
        <h1 pressdo-doc-title>오류</h1>
        <div id="cont_ent" pressdo-doc-content>
    <span> 문서를 찾을 수 없습니다. </span>
  </div></div><?php
} else { ?>
    <br>
    <style>
    </style>
    <div pressdo-content>
        <div pressdo-toolbar>
            <div pressdo-toolbar-menu>
                <a pressdo-toolbar-link href="/history/<?=$Doc?>">역사</a>
                <a pressdo-toolbar-link href="/edit/<?=$Doc?>">편집</a>
                <a pressdo-toolbar-link href="/backlink/<?=$Doc?>">역링크</a>
            </div>
        </div>
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a>
            <small pressdo-doc-action>(r<?=$_GET['rev']?> RAW)</small>
        </h1>
        <div id="cont_ent" pressdo-doc-content>
            <textarea readonly="readonly" pressdo-editor>
            <?=str_replace('@@@PressDo-Replace-Title-Here@@@', urlencode($Title), $array['content'])?>
            </textarea>
        </div>
    </div><?php

}
WikiSkin::footer();
?>