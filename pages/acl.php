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
WikiSkin::FullPage($Title, $Doc_content[1], 'view');
unset($Doc_content);
$Doc = $Title; ?>
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
            <small pressdo-doc-action>(ACL)</small>
        </h1>
        <div pressdo-doc-content>
<h2>문서 ACL</h2>
<div pressdo-acl-partarea>
<h4>편집</h4>
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
<tbody pressdo-acl-part>
<tr pressdo-acl-part>
<td pressdo-acl-part>No</td>
</tr>
</tbody>
</table>
</div>
</div>
<h2>이름공간 ACL</h2>
        </div>
    </div><?php
WikiSkin::footer();
?>