<?php
use PressDo\PressDo;
use PressDo\Thread;
use PressDo\Docs;
use PressDo\ACL;
/* Parameters
* viewName: 페이지
* Title: 제목
*/
require 'PressDoLib.php';
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
if(isset($post_sess['member']['username'])){
    $user_thread = Thread::checkUserThread($post_sess['username']);
    $member = [
        'username' => $post_sess['member']['username'],
        'quickblock' => false,
        'gravatar_url' => $post_sess['member']['gravatar_url'],
        'user_document_discuss' => $user_thread[0]
    ];
    $i = 'm:'.$member['username'];
}else {
    $member = null;
    $i = 'i:'.$post_sess['ip'];}
$sess = [
    'member' => $member,
    'ip' => $post_sess['ip'],
    'identifier' => $i,
    'menus' => []
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
        $discussions = Thread::getDocThread($fetch[0]['docid'], 1);
        $starred = PressDo::ifStarred($post_sess['username'], $fetch[0]['docid']);
        $starcount = PressDo::countStar($fetch[0]['docid']);
        if($fetch[0]['content'])
            $DOC_CONTENT = PressDo::readSyntax($fetch[0]['content']);
        else
            $DOC_CONTENT = null;
        $localConfig = [];
        $body = [
            'viewName' => 'wiki',
            'title' => $_GET['title'],
            'error' => null,
            'data' => [
                'starred' => $starred,
                'star_count' => $starcount,
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                    'content' => $DOC_CONTENT['html'],
                    'categories' => [
                        $DOC_CONTENT['categories']
                    ]
                ],
                'discuss_progress' => (isset($discussions[0])),
                'date' => $fetch[0]['datetime'],
                'rev' => $_GET['rev'],
                'user' => ($NameSpace == $_ns['user'])
//                'customData' => $ad_set
            ]
        ];
        $acl = ACL::checkACL(['session' => $sess, 'page' => $body]);
        if($acl[0] == false){
            if($acl[2] == null)
                $msg = 'ACL에 허용 규칙이 없기 때문에 읽기 권한이 부족합니다. 해당 문서의 <a href="'.$uri['acl'].$body['title'].'">ACL 탭</a>을 확인하시기 바랍니다';
            else
                $msg = '읽기 권한이 부족합니다. '.$acl[2].'여야 합니다. 해당 문서의 <a href="'.$uri['acl'].$body['title'].'">ACL 탭</a>을 확인하시기 바랍니다.';
            
            $body['data']['document']['content'] = null;
            $body['data']['document']['categories'] = [];
            $body['error'] = [
                'content' => $msg,
                'missing_permission' => 'permission_'.$acl[1]
            ];
        }
        break;
    case 'edit':
        $iv = Docs::getIdAndVersion($NS, rawurlencode($Title));
        $fetch = Docs::LoadDocument($NS, rawurlencode($Title));
        $DOC_CONTENT = PressDo::readSyntax($fetch[0]['content']);
        $localConfig = [];
        $body = [
            'viewName' => 'edit',
            'title' => $_GET['title'],
            'data' => [
                'editor' => [
                    'baserev' => $iv[0],
                    'section' => $_GET['section'],
                    'raw' => $fetch[0]['content'],
                    'preview' => $DOC_CONTENT['html']
                ],
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'user' => ($NameSpace === $_ns['user']),
                'token' => PressDo::rand(64)
//                'customData' => $ad_set
            ]
        ];
        break;
    case 'history':
        if(isset($_GET['from'])) $from = $_GET['from'];
        if(isset($_GET['until'])) $until = $_GET['until'];
        $fetch = Docs::LoadHistory($NS, rawurlencode($Title), $from, $until);
        $iv = Docs::getIdAndVersion($NS, rawurlencode($Title));
        $l = Docs::LoadDocument($NS, rawurlencode($Title), 1);
        $localConfig = [];
        $cn = count($fetch);
        $body = [
            'viewName' => 'history',
            'title' => $_GET['title'].' (역사)',
            'data' => [
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'history' => [
                ]
//                'customData' => $ad_set
            ]
        ];
        for($i=0; $i<$cn; ++$i){
            (isset($from))? $r = $from + $cn - 2 - $i:$r = $iv[0] - $i;
            $body['data']['history'][$i] = array(
                'rev' => $r,
                'log' => $fetch[$i]['comment'],
                'date' => $fetch[$i]['datetime'],
                'count' => $fetch[$i]['length'] - $fetch[$i+1]['length'],
                'logtype' => $fetch[$i]['action'],
                'target_rev' => $fetch[$i]['reverted_version'],
                'author' => $fetch[$i]['contributor_m'],
                'ip' => $fetch[$i]['contributor_i'],
                'style' => null,
                'blocked' => null,
                'edit_request' => $fetch[$i]['edit_request_uri'],
                'acl' => $fetch[$i]['acl_changed'],
                'from' => $fetch[$i]['moved_from'],
                'to' => $fetch[$i]['moved_to'],
                'user_mode' => []
            );
        }
        if($body['data']['history'][0]['rev'] == $iv[0])
            $body['data']['prev_ver'] = null;
        else
            $body['data']['prev_ver'] = $body['data']['history'][0]['rev'] + 1;
           
        if(end($body['data']['history'])['rev'] == 1)
            $body['data']['next_ver'] = null;
        else
            $body['data']['next_ver'] = end($body['data']['history'])['rev'] - 1;

        $body['data']['initial_date'] = $l[0]['datetime'];
        break;
    case 'raw':
        (!$_GET['rev'])? $fetch = Docs::LoadDocument($NS, rawurlencode($Title)): $fetch = Docs::LoadDocument($NS, rawurlencode($Title), $_GET['rev']);
        $localConfig = [];
        $body = [
            'viewName' => 'raw',
            'title' => $_GET['title'],
            'data' => [
                    'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'rev' => $_GET['rev'],
                'text' => $fetch[0]['content']
//                'customData' => $ad_set
            ]
        ];
        break;
    case 'acl':
        $docacls = ACL::getDocACL($NS, rawurlencode($Title)); // {0:{id: 1, access: edit, until: 30}, 1: ...}
        $nsacls = ACL::getNSACL($NS);
        $ACLTypes = ['read', 'edit', 'move', 'delete', 'create_thread', 'write_thread_comment', 'edit_request', 'acl'];
        foreach ($ACLTypes as $A){
            $docACLs[$A] = [];
            $nsACLs[$A] = [];
        }
        foreach ($docacls as $d){
            $a = ['id' => $d['id'], 'condition' => $d['condition'], 'action' => $d['action'], 'expired' => $d['expired']];
            array_push($docACLs[$d['access']], $a);
        }
        foreach ($nsacls as $d){
            $a = ['id' => $d['id'], 'condition' => $d['condition'], 'action' => $d['action'], 'expired' => $d['expired']];
            array_push($nsACLs[$d['access']], $a);
        }
        $localConfig = [];
        $body = [
            'viewName' => 'acl',
            'title' => $_GET['title'],
            'data' => [
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'docACL' => [
                    'acls' => $docACLs//,
                   // 'editable' => 
                ],
                'nsACL' => [
                    'acls' => $nsACLs//,
                   // 'editable' => 
                ],
                'ACLTypes' => $ACLTypes
            ]
        ];
        break;
    default:
        break;
}
echo json_encode(array('config' => $config, 'localConfig' =>$localConfig, 'page' => $body, 'session' => $sess), JSON_UNESCAPED_UNICODE);
Header('Content-Type: application/json; Charset=utf-8');
