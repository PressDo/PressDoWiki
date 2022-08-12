<?php
$db = new PDO('mysql:host=localhost;dbname=pressdo', 'admin', 'praseod@ts');

if($_POST['pw'] && $_POST['id']){
    $a = $db->prepare("UPDATE member SET `password`=? WHERE `username`=? AND `password`=?");
    $a->execute([password_hash($_POST['pw'], PASSWORD_BCRYPT),$_POST['id'], hash('sha256', $_POST['pw'])]);
    if($a->rowCount() > 0)
        echo 'Success';
    else
        echo 'Fail';
}
?>
비밀번호 재생성<br>
<form action="chpw.php" method="post">
ID <input type="text" name="id"><br>
PW <input type="password" name="pw">
<input type="submit">
</form>