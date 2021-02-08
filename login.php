<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';
WikiSkin::header('로그인');
WikiSkin::page_frame();
?><style>
.errmsg{
    color:red;
}
</style>
<script>
    function login(){
        var user = document.getElementById("username");
        var pwd = document.getElementById("password");
   alert(user + pwd);
        if(user.value=="") { // 입력값 확인
	        document.all['erruser'].style.display = "block";
            document.all['errpwd'].style.display = "none";
            return false;
        }else if(pwd.value==""){
            document.all['erruser'].style.display = "none";
            document.all['errpwd'].style.display = "block";
            return false;
        }else{
            document.all['erruser'].style.display = "none";
            document.all['errpwd'].style.display = "none";
            var f = document.loginform;
		    f.submit(); // 폼 제출
        }
    }
</script>
<div pressdo-content>
    <h1 pressdo-doc-title> 로그인 </h1>
    <div pressdo-doc-content>
        <form pressdo-register id="loginform" name="loginform" action="auth.php">
            <label for="username">Username</label>
            <input pressdo-formdata type="text" id="username" name="username">
            <p id="erruser" class="errmsg" style="display:none;"> 사용자 이름을 입력해주세요. </p><br>
            <label for="password">Password</label>
            <input pressdo-formdata type="password" id="password" name="password">
            <p id="errpwd" class="errmsg" style="display:none;"> 비밀번호를 입력해주세요. </p>
            <div pressdo-buttonarea><a pressdo-submit-wrap href="register">계정 만들기</a> <button pressdo-login-blue pressdo-button onclick="login()">로그인</button></div>
        </form>
    </div>
</div>
<?php
WikiSkin::footer();
?>