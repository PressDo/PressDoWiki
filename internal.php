<?php
/* Parameters
* viewName: 페이지
* Title: 제목
*/
require_once 'PressDoLib.php';
$config = [
    "force_recaptcha_public" => $conf['ForceRecaptchaPublic'],
    "recaptcha_public" => $conf['RecaptchaPublic'],
    "editagree_text" => $conf['EditAgreeText'],
    "front_page" => $conf['FrontPage'],
    "site_name" => $conf['SiteName'],
    "copyright_url" => $conf['CopyRightURL'],
    "cannonical_url" => $conf['CannonicalURL'],
    "copyright_text" => $conf['CopyRightText'],
    "sitenotice" => $conf['SiteNotice'],
    "logo_url" => $conf['LogoURL']
];
switch ($_GET['viewName']){
    case 'wiki':
        $fetchdata = Document::LoadDocument($_GET['Title'], $_GET['rev']);
        $localConfig = [];
        $body = [
            'title' => $_GET['Title'],
            'pageInfo' => [
                'title' => [
                    'namespace' => $fetchdata['namespace'],
                    'title' => $fetchdata['title']
                ],
                'time' => time(),
                'content' => 
            ],
        ];
    default:
        break;
}
