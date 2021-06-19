<?php
$_license = array(
    'license' => 'CC0',
    'version' => '1.0',
    'country' => '',
    'URL' => '//creativecommons.org/publicdomain/zero/1.0/deed.ko'
);
$conf = array(
    'SiteName' => "",
    'SiteName_en' => '',
    'NameSpace' => "",
    'FullURL' => '',
    'Domain' => "",
    'Language' => "ko-kr",
    'FrontPage' => "",
    'Description' => "",
    'CopyRightText' => "",
    'EditAgreeText' => "",
    'PageFooter' => "",
    'CopyRightURL' => "",
    'CannonicalURL' => "",
    'SiteNotice' => "",
    'FaviconURL' => "",
    'LogoURL' => "/src/img/logo.png",
    'DBType' => "",
    'DBHost' => "",
    'DBPort' => "",
    'DBName' => "",
    'DBUser' => "",
    'DBPass' => "",
    'Uploadable' => true,
    'AllowFileExt' => ["PNG", "JPG", "JPEG", "GIF", "SVG"],
    'CompressFile' => true,
    'PublicLevel' => 0,
    "AllowJoin" => true,
    "DefaultSkin" => "liberty",
    'Mark' => 'NamuMark',
    "TitleText" => "",
    "LogoWidth" => "6.6rem",
    "UseShortURI" => false,
    "UseMailWhitelist" => true,
    'UseMailAuth' => false,
    'SMTPHost' => '',
    'SMTPPort' => '',
    'SMTPProtocol' => '',
    'SMTPUsername' => '',
    'SMTPPassword' => '',
    'SMTPAddress' => '',
    "MailWhitelist" => [
        "gmail.com",
        "naver.com",
        "kakao.com",
        "prws.kr"
    ],
    'Extension' => [
        'TestExtension'
    ],
    'comments' => "",
    'ForceRecaptchaPublic' => '',
    'RecaptchaPublic' => '',
    'ForceShowNameSpace' => false,
    'MasterKey' => ''
);
require 'data/language/'.$conf['Language'].'.php';
$_ns = array(
     'wiki' => $lang['ns:wiki'],
     'file' => $lang['ns:file'],
     'template' => $lang['ns:template'],
     'category' => $lang['ns:category'],
     'trash' => $lang['ns:trash'],
     'document' => $lang['ns:document'],
     'user' => $lang['ns:user'],
     'specialfunction' => $lang['ns:specialfunction'],
     'vote' => $lang['ns:vote'],
     'discussion' => $lang['ns:discussion']
 );
$icon = array(
    'aclgroup' => 'ion-md-color-wand'
);
