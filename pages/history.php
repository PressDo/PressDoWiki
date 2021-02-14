<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';

if(isset($_SESSION['username'])){
$user = array(
'typename' => $_SESSION['usertype'].':'.$_SESSION['username']
);
}else{
$user = array(
'typename' => 'ip:'.PressDo::getip()
);
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
WikiSkin::FullPage($Title, 'history');
$array = $Doc_content[1];
$Doc = $Title;
$PrevData = Data::LoadOldDocument($Doc, $array['version'] - 1);
$Differ = $array['strlen'] - $PrevData[1]['strlen'];
$isLogin = $array['loginedit'];
    // 바이트 수 차이 색깔 표시
    if($Differ > 0){
        $Differ = '(<span style="color:green">+'.$Differ.'</span>)';
    }elseif($Differ == 0){
        $Differ = '(<span style="color:gray">0</span>)';
    }elseif($Differ < 0){
        $Differ = '(<span style="color:red">'.$Differ.'</span>)';
    }
    if($isLogin == 1) {
        $User = '<b>'.$array['contributor'].'</b>';
    }else{
        $User = $array['contributor'];
    }
?>
<div pressdo-content>
    <div pressdo-toolbar>
        <div pressdo-toolbar-menu>
            <a pressdo-toolbar-link href="/edit/<?=$Doc?>">편집</a>
            <a pressdo-toolbar-link href="/backlink/<?=$Doc?>">역링크</a>
        </div>
    </div>
    <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a>
        <small pressdo-doc-action>(문서 역사)</small>
    </h1>
    <div pressdo-history-content>
        <form action="/diff/<?=$Doc?>">
            <p><button pressdo-history-compare type="submit">선택 리비전 비교</button></p>
            <div pressdo-toolbar-menu>
                <a pressdo-history-prev>
                    <span ionicon ion-arrow-back></span>Prev
                </a>
                <a pressdo-history-prev>
                    Next<span ionicon ion-arrow-forward></span>
                </a>
            </div>
            <ul pressdo-history>
                <li pressdo-history>
                    <time><?=$array['savetime']?></time>
                    <span pressdo-history-menu>(<a href="<?=$conf['ViewerUri'].$Doc.'?rev='.$array['version']?>">보기</a> | 
                        <a href="/raw/<?=$Doc.'?rev='.$array['version']?>">RAW</a> | 
                        <a href="/blame/<?=$Doc.'?rev='.$array['version']?>">Blame</a> | 
                        <a href="/revert/<?=$Doc.'?rev='.$array['version']?>">이 리비전으로 되돌리기</a> | 
                        <a href="/diff/<?=$Doc.'?rev='.$array['version']?>">비교</a><?php
if(Data::checkACL($user['typename'], 'admin')){
?> | 
        <a href="/hide/<?=$Doc.'?rev='.$OldData[1]['version']?>">숨기기</a><?php
}
?>)
                    </span>
                    <input type="radio" name="oldrev" value="<?=$array['version']?>" style="visibility: visible;">
                    <input type="radio" name="rev" value="<?=$array['version']?>" style="visibility: visible;">
                    <strong>r<?=$array['version']?></strong> <span><?=$Differ?></span>
                    <div pressdo-history-menu style="display:inline;"><?=$User?></div>
                    (<span style="color:grey"><?=$array['summary']?></span>)</li><?php
$from = $_GET['from'];
if(!$_GET['from']){
    $from = 99999999999999999999999;
}
$minx = min(30, $array['version'], $from);
for ($x = 1; $x < $minx; ++$x) {
    // 현재 버전 기준으로 최근 30개까지만 로드
    $OldData = Data::LoadOldDocument($Doc, $array['version'] - $x);
    $OldData2 = Data::LoadOldDocument($Doc, $array['version'] - $x-1);
    $Diff = $OldData[1]['strlen'] - $OldData2[1]['strlen'];
    $isLogin = $OldData[1]['loginedit'];

    // 바이트 수 차이 색깔 표시
    if($Diff > 0){
        $Diff = '(<span pressdo-history-green>+'.$Diff.'</span>)';
    }elseif($Diff == 0){
        $Diff = '(<span pressdo-history-gray>0</span>)';
    }elseif($Diff < 0){
        $Diff = '(<span pressdo-history-red>'.$Diff.'</span>)';
    }
    if($isLogin == 1) {
        $User = '<b>'.$OldData[1]['contributor'].'</b>';
    }else{
        $User = $OldData[1]['contributor'];
    }
    ?><li pressdo-history><time><?=$OldData[1]['savetime']?></time> 
    <span pressdo-history-menu>(<a href="<?=$conf['ViewerUri'].$Doc.'?rev='.$OldData[1]['version']?>">보기</a> | 
        <a href="/raw/<?=$Doc.'?rev='.$OldData[1]['version']?>">RAW</a> | 
        <a href="/blame/<?=$Doc.'?rev='.$OldData[1]['version']?>">Blame</a> | 
        <a href="/revert/<?=$Doc.'?rev='.$OldData[1]['version']?>">이 리비전으로 되돌리기</a> | 
        <a href="/diff/<?=$Doc.'?rev='.$OldData[1]['version']?>">비교</a><?php
if(Data::checkACL($user['typename'], 'admin')){
?> | 
        <a href="/hide/<?=$Doc.'?rev='.$OldData[1]['version']?>">숨기기</a><?php
}
?>)
    </span>
        <input type="radio" name="oldrev" value="<?=$OldData[1]['version']?>" style="visibility: visible;">
        <input type="radio" name="rev" value="<?=$OldData[1]['version']?>" style="visibility: visible;">
        <strong>r<?=$OldData[1]['version']?></strong> <span><?=$Diff?></span>
        <div pressdo-history-menu style="display:inline;"><?=$User?></div>
        (<span style="color:grey"><?=$OldData[1]['summary']?></span>)</li><?php
} ?>
</ul></form></div>
</div><?php
WikiSkin::footer();
?>
