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
case '/pages/acl.php':
    Header("HTTP/2.0 403 Forbidden");
    exit;
}

if(isset($_POST['title'])){
    $Title = $_POST['title'];
}elseif(isset($_GET['title'])){
    $Title = $_GET['title'];
}
$Doc_content = Data::LoadLatestDocument($Title);
WikiSkin::FullPage($Title, 'view');
$Doc = $Title; 
$ac = array(
    'allow' => '허용',
    'deny' => '거부',
    'gotons' => '이름공간ACL 실행'
);
$acc = array('deny', 'allow');
$ei = array(
    array('view', '읽기'),
    array('edit', '편집'),
    array('move', '이동'),
    array('delete', '삭제'),
    array('create_thread', '토론 생성'),
    array('write_thread_comment', '토론 댓글'),
    array('edit_request', '편집요청'),
    array('acl', 'ACL')
);
?>
<style>
h4[pressdo-acl-type]{
    font-size: 1.4em;
    margin: 1.2em 0 .8em;
    font-weight: 700;
    font-family: inherit;
    line-height: 1.1;
    color: inherit;
}
h2[pressdo-acl-title]{
    font-size: 1.8em;
    margin: 1.2em 0 .8em;
    font-weight: 700;
    font-family: inherit;
    line-height: 1.1;
    color: inherit;
}
div[pressdo-acl-part]{
    overflow-x: auto;
}
div[pressdo-acl-part],
table[pressdo-acl-part]{
    width: 100%;
    max-width: 100%;
}
table[pressdo-acl-part]{
    margin-bottom: 1rem;
    background-color: transparent;
    border-spacing: 0;
    border-collapse: collapse;
    white-space: nowrap;
}
table tr:last-of-type td[pressdo-acl-part]{
    border-bottom: 1px solid #eceeef;
}
td[colspan][pressdo-acl-part]{
    text-align: center;
    cursor: auto;
}
td[pressdo-acl-part]{
    cursor: move;
}
td[pressdo-acl-part],
th[pressdo-acl-part]{
    padding: .5rem .7rem;
    line-height: 1.5;
    border-top: 1px solid #eceeef;
}
th[pressdo-acl-part]{
    vertical-align: bottom;
    border-bottom: 2px solid #eceeef;
    text-align: left;
}
</style>
    <br>
    <div pressdo-content>
        <h1 pressdo-doc-title><a href="<?=$conf['ViewerUri'].$Doc?>"><?=$Doc?></a>
            <small pressdo-doc-action>(ACL)</small>
        </h1>
        <div pressdo-doc-content>
<h2 pressdo-acl-title>문서 ACL</h2>
<div pressdo-acl-partarea>
<?php
foreach($ei as $el){
echo '<h4 pressdo-acl-type>'.$el[1].'</h4>
<div pressdo-acl-part>
    <table pressdo-acl-part>
        <colgroup pressdo-acl-part>
            <col pressdo-acl-part style="width:60px;">
            <col pressdo-acl-part>
            <col pressdo-acl-part style="width:80px;">
            <col pressdo-acl-part style="width:200px;">
            <col pressdo-acl-part style="width:60px;">
        </colgroup> 
        <thead pressdo-acl-part>
            <tr pressdo-acl-part>
                <th pressdo-acl-part>No</th>
                <th pressdo-acl-part>Condition</th>
                <th pressdo-acl-part>Action</th>
                <th pressdo-acl-part>Expiration</th>
                <th pressdo-acl-part></th>
            </tr>
        </thead>
        <tbody pressdo-acl-part>';
$aq = Data::getDocACL($Title, $el[0]);
foreach($aq as $e){
    if($aq[0] == false){ echo '<tr pressdo-acl-part><td colspan="5" pressdo-acl-part>(규칙이 존재하지 않습니다. 이름공간 ACL이 적용됩니다.)</td></tr>'; break;}
    if($e['expiry'] == 'none') $e['expiry'] = '영구';
    echo '<tr pressdo-acl-part><td pressdo-acl-part>'.
        $e['ACLID'].'</td><td pressdo-acl-part>'.
        $e['condition'].'</td><td pressdo-acl-part>'.
        $ac[$e['access']].'</td><td pressdo-acl-part>'.
        $e['expiry'].'</td></tr>';
}
?>      </tbody>
    </table>
</div>
<?php } ?>
</div>
<h2 pressdo-acl-title>이름공간 ACL</h2>
<div pressdo-acl-partarea>
<?php
foreach($ei as $el){
echo '<h4 pressdo-acl-type>'.$el[1].'</h4>
<div pressdo-acl-part>
    <table pressdo-acl-part>
        <colgroup pressdo-acl-part>
            <col pressdo-acl-part style="width:60px;">
            <col pressdo-acl-part>
            <col pressdo-acl-part style="width:80px;">
            <col pressdo-acl-part style="width:200px;">
            <col pressdo-acl-part style="width:60px;">
        </colgroup> 
        <thead pressdo-acl-part>
            <tr pressdo-acl-part>
                <th pressdo-acl-part>No</th>
                <th pressdo-acl-part>Condition</th>
                <th pressdo-acl-part>Action</th>
                <th pressdo-acl-part>Expiration</th>
                <th pressdo-acl-part></th>
            </tr>
        </thead>
        <tbody pressdo-acl-part>';
$aq = Data::getNSACL($Doc_content[1]['type']);
foreach($aq as $e){
    if($aq[0] == false) {echo '<tr pressdo-acl-part><td colspan="5" pressdo-acl-part>(규칙이 존재하지 않습니다. 모두 거부됩니다.)</td></tr>'; break;}
    $v = array_search($el, $ei);
    echo '<tr pressdo-acl-part><td pressdo-acl-part>'.'</td><td pressdo-acl-part>'.
        $e['type'].':'.$e['name'].'</td><td pressdo-acl-part>'.
        $ac[$acc[substr($e['value'], $v, 1)]].'</td><td pressdo-acl-part>영구</td></tr>';
} ?>    </tbody>
    </table>
</div>
<?php } ?>
</div>
        </div>
    </div><?php
WikiSkin::footer();
?>