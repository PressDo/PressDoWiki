<?php
use PressDo\PressDo;
use PressDo\WikiSkin;
use PressDo\Data;
include '../config.php';
require_once '../PressDoLib.php';
require_once '../skin/'.$conf['Skin'].'/skin.php';
WikiSkin::header('계정 만들기');
WikiSkin::page_frame();
?><style>
div[pressdo-errorbox]{
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebcccc;
    margin-bottom: 1.4rem;
    padding: .5rem .8rem;
    border-radius: .25rem;
    font-size: .9rem;
    border: 1px solid #bcdff1;
}
div[pressdo-register-formarea]{
    margin-bottom: 15px;
}
div[pressdo-register-formarea]>label{
    margin-bottom: .5rem;
}
form[pressdo-register]{
    width:100%;
}
input[type=email][pressdo-formdata],
input[type=password][pressdo-formdata]{
    display: inline-block;
    margin: 0 0 0 .7rem;
    width: calc(100% - .7rem);
    border-radius: 0;
    padding: .25rem .5rem;
    font-size: .9rem;
    line-height: 1.5;
    color: #55595c;
    background-color: #fff;
    background-image: none;
    border: .0625rem solid #ccc;
}
ul[pressdo-mail-whitelist]{
    padding-left: .5rem;
    margin: .4em 0 .4em 1.5em;
}
ul[pressdo-mail-whitelist]>li{
    margin: .4em 0;
    list-style: inherit!important;
    list-style-type: inherit!important;
    font-size: .9rem;
}
div[pressdo-buttonarea]>button[pressdo-button-blue]{
    color: #fff;
    background-color: #0275d8;
    border-color: #0275d8;
    float: right;
    width: 5rem;
    margin-top: 2rem;
}
button[pressdo-button]{
    margin: 0;
    display: inline-block;
    font-size: 1rem;
    font-weight: 400;
    line-height: 1.5;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    padding: .2rem .7rem;
    color: #373a3c;
    background-color: #fff;
    border: .0625rem solid #ccc;
    font-size: .9rem;
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
                <?php if($conf['UseMailWhitelist'] == 1){
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