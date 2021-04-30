<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
$User = PressDo::ConstUser($_SESSION);
require_once '../PressDoLib.php';
require_once '../skin/'.$user['skin'].'/skin.php';
include '../skin/'.$user['skin'].'/html/header.html';

$ReqURI = preg_match('/\/([^\/]*)\/(.*)$/', $_SERVER['REQUEST_URI']);
switch ($ReqURI[1]){
    case false:
        Header('Location: http://'.$conf['Domain'].$conf['ViewerUri'].$conf['Title']);
        break;
    case 'w':
        
        break;
    case 'edit':
        
        break;
    case 'acl'
        
        break;
    case 'move':
        
        break;
    case 'delete':
        
        break;
    case 'history':
        
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
}

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(isset($_GET['title'])){
    $Title = $_GET['title'];
}


$acl = array('ok' => true);

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
if ($acl['ok'] == false) {
 // ACL 부족 ?>
    <div pressdo-content>
        <div pressdo-content-header>
            <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a></h1>
            <span> 읽기 권한이 부족합니다. <?=acl['allowed']?>(이)여야 합니다. 해당 문서의 <p><a href="/acl/<?=$Doc?>">ACL 탭</a></p>을 확인하시기 바랍니다. </span><br>
        >/div>
    </div><?php
} elseif (!$array['content']) {
    // 없는 문서?>
    <div pressdo-content>
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a></h1>
    <span> 해당 문서를 찾을 수 없습니다. </span><br>
    <p><a href="/edit/<?=$Doc?>">[새 문서 만들기]</a></p>
</div><?php
} else { ?>
    <div pressdo-content>
        <div pressdo-content-header>
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
        </div>
        <div id="cont_ent" pressdo-doc-content>
            <div id="categoryspace_top"></div>
            <?=str_replace('@@@PressDo-Replace-Title-Here@@@', rawurlencode($Title), PressDo::readSyntax($array['content']));?>
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
include '../skin/'.$user['skin'].'/html/footer.html';
?>