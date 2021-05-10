<?php
use PressDo\PressDo;
use PressDo\Thread;
use PressDo\Docs;
/* Parameters
* viewName: 페이지
* Title: 제목
*/
require_once 'PressDoLib.php';
$config = [
    'force_recaptcha_public' => $conf['ForceRecaptchaPublic'],
    'recaptcha_public' => $conf['RecaptchaPublic'],
    'editagree_text' => htmlspecialchars($conf['EditAgreeText']),
    'front_page' => $conf['FrontPage'],
    'site_name' => $conf['SiteName'],
    'copyright_url' => $conf['CopyRightURL'],
    'cannonical_url' => $conf['CannonicalURL'],
    'copyright_text' => htmlspecialchars($conf['CopyRightText']),
    'sitenotice' => htmlspecialchars($conf['SiteNotice']),
    'logo_url' => $conf['LogoURL']
];
$post_sess = $_POST['session'];
if(isset($post_sess['username'])){
    $user_thread = checkUserThread($post_sess['username']);
    $member = [
        'user_document_discuss' => $user_thread[0],
        'username' => $post_sess['username'],
        'gravatar_url' => $post_sess['gravatar_url']
    ];
}else $member = null;
$sess = [
    'menus' => [],
    'member' => $member,
    'ip' => PressDo::getip()
];
preg_match('/^('.$_ns['wiki'].'|'.$_ns['category'].'|'.$_ns['trash'].'|'.$_ns['filetrash'].'|'.$_ns['wikioperation'].'|'.$_ns['specialfunction'].'|'.$_ns['vote'].'|'.$_ns['user'].'|'.$_ns['discussion'].'|'.$_ns['include'].'|'.$_ns['template'].'|'.$_ns['file'].'):(.*)$/', $_GET['title'], $get_ns);
if(!$get_ns){
    $NameSpace = $_ns['document'];
    $Title = $_GET['title'];
} else {
    $NameSpace = $get_ns[1];
    $Title = $get_ns[2];
}
$NS = array_search($NameSpace, $_ns);
switch ($_GET['page']){
    case 'w':
    case 'jump':
        (!$_GET['rev'])? $fetch = Docs::LoadDocument($NS, rawurlencode($Title)): $fetch = Docs::LoadDocument($NS, rawurlencode($Title), $_GET['rev']);
        $discussions = Thread::getDocThread($fetch['docid'], 1);
        $starred = PressDo::ifStarred($post_sess['username'], $fetch['docid']);
        $starcount = PressDo::countStar($fetch['docid']);
        $localConfig = [];
        $body = [
            'viewName' => 'wiki',
            'title' => $_GET['title'],
            'data' => [
                'starred' => $starred,
                'star_count' => $starcount,
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                    'content' => $fetch['content'],
                    'categories' => [
                        [
                            'namespace' => '분류',
                            'title' => '분류명'
                        ]
                    ]
                ],
                'discuss_progress' => (isset($discussions[0])),
                'date' => $fetch['datetime'],
                'rev' => $_GET['rev'],
                'user' => ($NameSpace == $_ns['user'])
//                'customData' => $ad_set
            ]
        ];
    default:
        break;
}
echo json_encode(array('config' => $config, 'localConfig' =>$localConfig, 'page' => $body, 'session' => $sess), JSON_UNESCAPED_UNICODE);
Header('Content-Type: application/json; Charset=utf-8');
