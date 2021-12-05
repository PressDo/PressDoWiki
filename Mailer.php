<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'external/PHPMailer/src/Exception.php';
require 'external/PHPMailer/src/PHPMailer.php';
require 'external/PHPMailer/src/SMTP.php';

function mail_send($to, $subject, $body) {
    global $conf;
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $conf['SMTPHost']; 
        $mail->SMTPAuth   = true;
        $mail->Username   = $conf['SMTPUsername'];
        $mail->Password   = $conf['SMTPPassword'];
        $mail->CharSet = 'UTF-8'; 
        $mail->Encoding = "base64";
        $mail->SMTPSecure = $conf['SMTPProtocol']; 
        $mail->Port       = $conf['SMTPPort'];
        $mail->setFrom($conf['SMTPAddress'], $conf['SiteName_en']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}