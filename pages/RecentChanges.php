<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';

$Title = '최근 변경내역';
WikiSkin::FullPage($Title, 'history');
$Doc = $Title;
?>
<div pressdo-content>
    <h1 pressdo-doc-title><?=$Doc?></h1>
    <div pressdo-history-container>
        <ol pressdo-history>
            <li pressdo-doc-category><a href="/RecentChanges?logtype=all">[전체]</a></li>
            <li pressdo-doc-category><a href="/RecentChanges?logtype=create">[새 문서]</a></li>
            <li pressdo-doc-category><a href="/RecentChanges?logtype=delete">[삭제]</a></li>
            <li pressdo-doc-category><a href="/RecentChanges?logtype=move">[이동]</a></li>
            <li pressdo-doc-category><a href="/RecentChanges?logtype=revert">[되돌림]</a></li>
        </ol>
        <table pressdo-history-content>
            <colgroup data-v-698dcd06="">
                <col>
                <col style="width: 25%;">
                <col style="width: 22%;">
            </colgroup>
            <thead>
                <th pressdo-history>항목</th>
                <th pressdo-history>수정자</th>
                <th pressdo-history>수정 시간</th>
            </thead>
            <tbody>
                    <?php
$lo = $_GET['logtype'];
$lt = array(
'create' => "WHERE `logtype`='create'",
'revert' => "WHERE `logtype`='revert'",
'move' => "WHERE `logtype`='move'",
'delete' => "WHERE `logtype`='delete'",
'' => ''
);
$h = Data::LoadWholeHistory($lt[$lo]);
$sortArr = array();	
foreach($h as $res) 
  $sortArr [] = $res['savetime']; 
array_multisort($sortArr , SORT_DESC, $h);
foreach ($h as $he) {
    $Doc = urldecode($he['DocNm']);
    $Bef = Data::LoadOldDocument($Doc, $he['version'] -1);
    $Diff = $he['strlen'] - $Bef[1]['strlen'];
    $isLogin = $he['loginedit'];

    $logmsg = array(
        'edit' => '',
        'create' => '(새 문서)',
        'delete' => '(삭제)',
        'move' => '(이동)',
        'revert' => '(되돌림)'
    );
    
    // 바이트 수 차이 색깔 표시
    if ($Diff > 0) {
        $Diff = '(<span pressdo-history-green>+'.$Diff.'</span>)';
    } elseif ($Diff == 0) {
        $Diff = '(<span pressdo-history-gray>0</span>)';
    } elseif ($Diff < 0) {
        $Diff = '(<span pressdo-history-red>'.$Diff.'</span>)';
    }
    if ($isLogin == 1) {
        $User = '<b>'.$he['contributor'].'</b>';
    } else {
        $User = $he['contributor'];
    } ?> 
<tr pressdo-history>
    <td pressdo-history>
        <a href="/w/<?=$Doc?>"><?=$Doc?></a> 
        <a href="/history/<?=$Doc?>">[역사]</a>
        <a href="/diff/<?=$Doc.'?rev='.$he['version']?>">[비교]</a>
        <a href="/discuss/<?=$Doc?>">[토론]</a>
        <?=$Diff?>
    </td>
    <td pressdo-history><?=$User?></td>
    <td pressdo-history><?=$he['savetime']?></td>
</tr>
<?php if (strlen($he['summary']) > 0 || $he['logtype'] !== 'edit') {?>
<tr pressdo-history>
    <td pressdo-history-summary><span><?=$he['summary']?> <i><?=$logmsg[$he['logtype']]?></i></span></td>
</tr><?php
    }
} ?>
</tbody>
</table></div>
</div><?php
WikiSkin::footer();
?>
