<?php
namespace PressDo
{
    include 'config.php';
    require_once 'PressDoLib.php';
    class WikiSkin
    {
        public static function header($DocNm)
        {
            global $conf;
            include './skin/default/html/head.html'; ?>
            <title> <?=$DocNm?> - <?=$conf['Name']?> </title><?php
        }

        public static function main($Doc, $DocContent, $action = 'view')
        {
            global $conf;
            ?><div align="center" style="text-align:left;"><?php
            include './skin/default/html/main.html';
            if($action == 'view'){ ?>
                <h1><?=$Doc?></h1><?php
                if(!$DocContent){ ?>
                    <hr>
                    <h2> 문서를 찾을 수 없음 </h2><br>
                    <p> 존재하지 않는 문서입니다. </p><?php
                    if(isset($_SESSION['userid'])){
                        ?><a href="/index.php?title=<?=$Doc?>&action=create">문서 만들기</a><?php
                    }
                }else{ ?>
                    <div align="right">
                        <button type="button" onclick="goEdit();">편집</button>
                        <button type="button" onclick="goMove();">이동</button>
                        <button type="button" onclick="goDelete();">삭제</button>
                        <button type="button" onclick="goACL();">ACL</button>
                    </div>
                    <hr>
                    <br>
                    <?=PressDo::readSyntax($DocContent)?>
<script>
var docMenu = document.createElement('form');
docMenu.method = 'GET';
docMenu.action = 'index.php';
var from = document.createElement('input');
from.setAttribute('type', 'hidden');
from.setAttribute('name', 'title');
from.setAttribute('value', '<?=$Doc?>');
var mode = document.createElement('input');
mode.setAttribute('type', 'hidden');
mode.setAttribute('name', 'action');
function goEdit(){
    mode.setAttribute('value', 'edit');
    docMenu.appendChild(from);
    docMenu.appendChild(mode);
    document.body.appendChild(docMenu);
    docMenu.submit();
}
function goDelete(){
    mode.setAttribute('value', 'delete');
    docMenu.appendChild(from);
    docMenu.appendChild(mode);
    document.body.appendChild(docMenu);
    docMenu.submit();
}
function goMove(){
    mode.setAttribute('value', 'move');
    docMenu.appendChild(from);
    docMenu.appendChild(mode);
    document.body.appendChild(docMenu);
    docMenu.submit();
}
function goACL(){
    mode.setAttribute('value', 'acl');
    docMenu.appendChild(from);
    docMenu.appendChild(mode);
    document.body.appendChild(docMenu);
    docMenu.submit();
}
</script><?php
                }
            }elseif($action == 'edit'){ ?>
<h1><?=$Doc?> (편집) </h1>
<p> 문서를 편집 중입니다. </p>
<form method="post" action="index.php">
    <input type="hidden" name="action" value="save">
    <textarea name="content" style="width:95%; height:65%; font-size:10pt;"><?=$DocContent?></textarea>
    <p> 편집 요약 </p>
    <input type="text" name="summary" style="width:95%;">
    <p><input type="checkbox" name="agree"> 문서 편집을 저장하면 당신은 기여한 내용을 CC-BY-NC-SA 2.0 KR으로 배포하고 기여한 문서에 대한 하이퍼링크나 URL을 이용하여 저작자 표시를 하는 것으로 충분하다는 데 동의하는 것입니다. 이 동의는 철회할 수 없습니다.</p><?php
if(!$_SESSION['userid']){
    ?><b>로그인하지 않은 상태로 편집하고 있습니다. 저장 시 아이피(<?=PressDo::getip()?>)가 영구히 기록됩니다.</b><?php
} ?>
    <div align="right"><input type="submit" value="저장" style="width:15%; height:5%; right:5%;"></div>
</form>
<?php
        }
?></div><?php
    }

    public static function footer()
    {
        global $conf;?>
<footer>
    <hr>
    <p> ⓒCopyright <?=$conf['CopyRight']?></p>
    <p> <?=$conf['HelpMail']?> | <?=$conf['TermsOfUse']?> | <?=$conf['SecPolicy']?> </p>
    <br>
    <br>
    <br>
    <br>
</footer><?php
    }

    public static function FullPage($DocNm, $DocContent, $action = 'view')
    {
        WikiSkin::header($DocNm);
        WikiSkin::main($DocNm, $DocContent, $action);
        WikiSkin::footer();
    }
}
}
?>
