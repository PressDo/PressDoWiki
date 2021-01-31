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
WikiSkin::FullPage($Title, $Doc_content[1], 'history');
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
                <h1><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a> (역사) </h1>
                <div align="right">
                    <button type="button" onclick="goMenu('edit');">편집</button>
                    <button type="button" onclick="goMenu('xref');">역링크</button>
                </div>
                <li> <?=$array['savetime']?> <b>r<?=$array['version']?></b> <?=$Differ?> <?=$User?> (<span style="color:grey"><?=$array['summary']?></span>)</li>
                <?php
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
                        $Diff = '(<span style="color:green">+'.$Diff.'</span>)';
                    }elseif($Diff == 0){
                        $Diff = '(<span style="color:gray">0</span>)';
                    }elseif($Diff < 0){
                        $Diff = '(<span style="color:red">'.$Diff.'</span>)';
                    }
                    if($isLogin == 1) {
                        $User = '<b>'.$OldData[1]['contributor'].'</b>';
                    }else{
                        $User = $OldData[1]['contributor'];
                    }
                    ?><li> <?=$OldData[1]['savetime']?> <b>r<?=$OldData[1]['version']?></b> <?=$Diff?> <?=$User?> (<span style="color:grey"><?=$OldData[1]['summary']?></span>)</li><?php
                } ?></div><?php
WikiSkin::footer();
?>
