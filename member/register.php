<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';
WikiSkin::header('계정 만들기');
WikiSkin::page_frame();
?><style>




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
<div pressdo-content>
    <h1 pressdo-doc-title> 계정 만들기 </h1>
    <?php if($Mail_OK !== 1){ ?>
    <div pressdo-doc-content>
        <?php if(isset($Mail_Error)){
        echo "<div pressdo-errorbox><strong>[오류!]</strong> $Mail_Error</div>";
        } ?>
        <form pressdo-register id="regform" name="regform" action="register.php">
            <div pressdo-register-formarea>
                <label for="email">전자우편 주소</label>
                <input pressdo-formdata type="email" id="email" name="email">
                <p id="errmail" class="errmsg" style="visibility:hidden;"> 전자우편 주소를 입력해주세요. </p>
                <?php if($conf['UseMailWhitelist'] == true){
                echo '<p>이메일 허용 목록이 활성화 되어 있습니다.<br>이메일 허용 목록에 존재하는 메일만 사용할 수 있습니다.</p>
                <ul pressdo-mail-whitelist>';
                foreach($conf['MailWhitelist'] as $wl){
                echo "<li>$wl</li>";
                }
                echo '</ul>';
                } ?>
            </div>
            <b>가입후 탈퇴는 불가능합니다.</b>
            <div pressdo-buttonarea><button pressdo-button-blue pressdo-button type="submit">가입</button></div>
        </form>
    </div>
    <?php } ?>
</div>
<?php
WikiSkin::footer();
?>