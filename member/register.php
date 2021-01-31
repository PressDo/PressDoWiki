<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
include '../config.php';
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';
WikiSkin::header('로그인');
WikiSkin::page_frame();
?><style>
#loginform {
position:relative;
width:50%;
margin:auto;
font-size: 1rem;
}
#loginform input {
width:100%;
height: 35px;
font-size: 14pt;
}
#loginform input,p {
text-align:left;
}
div[button] {
text-align:right;
}
.errmsg{
    color:red;
}
</style>
<script>
    function login(){
        var mail = document.getElementById("email");
        if(mail.value=="") { // 입력값 확인
	        document.all['errmail'].style.visibility = "visible";
            return false;
        }else{
            document.all['errmail'].style.visibility = "hidden";
            var f = document.regform;
		    f.submit(); // 폼 제출
        }
    }
</script>
<article>
<h1> 계정 만들기 </h1>
<?php if(!$_POST['email']){ ?>
<form id="regform" name="regform" action="auth.php">
    <p>전자우편 주소</p>
    <input type="text" id="email" name="email">
    <p id="errmail" class="errmsg" style="visibility:hidden;"> 전자우편 주소를 입력해주세요. </p>
<?php if($conf['UseMailWhitelist'] == 1){
echo '<p>이메일 허용 목록이 활성화 되어 있습니다.<br>이메일 허용 목록에 존재하는 메일만 사용할 수 있습니다.</p>';
foreach($conf['MailWhitelist'] as $wl){
echo "<li>$wl</li>";
}
} ?>
    <div button><button type="button" onclick="login();">가입</button></div>
</form>
<?php } ?>
</article>
<?php
WikiSkin::footer();
?>