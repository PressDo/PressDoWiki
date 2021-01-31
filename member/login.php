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
        var user = document.getElementById("username");
        var pwd = document.getElementById("password");
        if(user.value=="") { // 입력값 확인
	        document.all['erruser'].style.visibility = "visible";
            document.all['errpwd'].style.visibility = "hidden";
            return false;
        }else if(pwd.value==""){
            document.all['erruser'].style.visibility = "hidden";
            document.all['errpwd'].style.visibility = "visible";
            return false;
        }else{
            document.all['erruser'].style.visibility = "hidden";
            document.all['errpwd'].style.visibility = "hidden";
            var f = document.loginform;
		    f.submit(); // 폼 제출
        }
    }
</script>
<article>
<h1> 로그인 </h1>
<form id="loginform" name="loginform" action="auth.php">
    <p>Username</p>
    <input type="text" id="username" name="username"><br>
    <p id="erruser" class="errmsg" style="visibility:hidden;"> 사용자 이름을 입력해주세요. </p><br>
    <p>Password</p>
    <input type="password" id="password" name="password"><br>
    <p id="errpwd" class="errmsg" style="visibility:hidden;"> 비밀번호를 입력해주세요. </p><br>
    <div button><a href="register"><button type="button">계정 만들기</button></a><button type="button" onclick="login();">로그인</button></div>
</form>
</article>
<?php
WikiSkin::footer();
?>