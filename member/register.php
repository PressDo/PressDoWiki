<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
session_start();
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';
WikiSkin::header('계정 만들기');
WikiSkin::page_frame();
?><style>
/* CSS TEST */
</style>
<script>
    function chkmail(){
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
<div pressdo-content>
    <h1 pressdo-doc-title> 계정 만들기 </h1>
    <div pressdo-doc-content>
		<!-- 위에까지 공통 UI -->
        <?php 
	if($_SESSION['SUCCESS'] == true && isset($_POST['username'])){
		?><p>환영합니다! <b><?=$POST['username']?></b>님 계정 생성이 완료되었습니다.</p><?php
	}elseif($_SESSION['MAIL_CHECK'] == true && isset($_POST['email'])){
		// MAIL_FORM, POST값 
		$_SESSION['MAIL_CHECK'] = false;?>
		<p>이메일(<b><?=$_POST['email']?></b>)로 계정 생성 이메일 인증 메일을 전송했습니다. 메일함에 도착한 메일을 통해 계정 생성을 계속 진행해 주시기 바랍니다.</p>
		<ul>
			<li>간혹 메일이 도착하지 않는 경우가 있습니다. 이 경우, 스팸함을 확인해주시기 바랍니다.</li>
			<li>인증 메일은 24시간동안 유효합니다.</li>
		</ul>
		<?php
	}else{
		// 초기 접속시
		$_SESSION['MAIL_CHECK'] = true;
		if(isset($Mail_Error)){
        	echo "<div pressdo-errorbox><strong>[오류!]</strong> $Mail_Error</div>";
			// 메일 오류 있으면 표시
        } ?>
        <form pressdo-register method="POST" id="regform" name="regform" action="register.php">
            <div pressdo-register-formarea>
                <label for="email">이메일</label>
                <input pressdo-formdata type="email" id="email" name="email">
                <p id="errmail" class="errmsg" style="visibility:hidden;"> 이메일의 값은 필수입니다. </p>
                <?php
			if($conf['UseMailWhitelist'] == true){
                echo '<p>이메일 허용 목록이 활성화 되어 있습니다.<br>이메일 허용 목록에 존재하는 메일만 사용할 수 있습니다.</p>
                <ul pressdo-mail-whitelist>';
                foreach($conf['MailWhitelist'] as $wl){
                	echo "<li>$wl</li>";
                }
                echo '</ul>';
            } ?>
            </div>
            <b>가입후 탈퇴는 불가능합니다.</b>
            <div pressdo-buttonarea><button pressdo-button-blue pressdo-button onclick="chkmail()">가입</button></div>
        </form>
		<?php } ?>
		<!-- 이하 공통 UI -->
    </div>
</div>
<?php
WikiSkin::footer();
?>
