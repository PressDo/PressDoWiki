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

// 제목 조작 방지
function getTitle(){
    parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $i);
    if(!$i['title']){
        return false;
    }else{
        return $i['title'];
    }
}

if($_POST['action'] == 'save'){
    // 편집 저장
    if(!getTitle() || getTitle() !== $Title){
        echo '잘못된 시도입니다.';
        exit;
    }
    Data::SaveDocument(getTitle(), $_POST['content'], $_POST['summary']);
}
$Doc_content = Data::LoadLatestDocument($Title);
WikiSkin::FullPage($Title, $Doc_content[1], 'view');
$array = $Doc_content[1];
unset($Doc_content);
$Doc = $Title; ?>
<?php
if (!$array['content']) {
    // 없는 문서?>
    <hr>
    <h2> 문서를 찾을 수 없음 </h2><br>
    <p> 존재하지 않는 문서입니다. </p><?php
    if (isset($_SESSION['userid'])) {
        ?><a href="/edit/<?=$Doc?>">문서 만들기</a><?php
    }
} else { ?>
     <hr>
    <br>
    <div id="d_content">
<div align="right" id="toolbar">
    <a href="/xref/<?=$Doc?>"><button type="button">역링크</button></a>
    <a href="/history/<?=$Doc?>"><button type="button">역사</button></a>
    <a href="/edit/<?=$Doc?>"><button type="button">편집</button></a>
    <a href="/acl/<?=$Doc?>"><button type="button">ACL</button></a>
</div>
<h1><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a></h1>
<p style="text-align:right">최근 수정 시각: <?=$array['savetime']?></p>
    <?=PressDo::readSyntax($array['content']);?>
    </div><?php

}
WikiSkin::footer();
?>