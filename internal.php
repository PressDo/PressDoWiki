<?php
session_start();
use PressDo\PressDo;
use PressDo\Thread;
use PressDo\Docs;
use PressDo\ACL;
use Pressdo\Member;
require 'PressDoLib.php';
Header('Content-Type: application/json; Charset=utf-8');
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
    $user_thread = Thread::checkUserThread($post_sess['member']['username']);
    $member = [
        'username' => $post_sess['member']['username'],
        'quickblock' => false,
        'gravatar_url' => $post_sess['member']['gravatar_url'],
        'user_document_discuss' => $user_thread[0]
    ];
    $i = 'm:'.$member['username'];
}else {
    $member = null;
    $i = 'i:'.$post_sess['ip'];
}

function verifyACL($sess, $title, $vn, $ns, $t){
    global $lang, $uri;
    $acl = ACL::checkACL(['session' => $sess, 'page' => ['title' => $title, 'viewName' => $vn, 'namespace' => $ns, 'title_' => $t]]);
    if($acl[0] === false){
        if($acl[2] === null)
            $msg = str_replace(['@1@','@2@'], [$lang['acl:'.$acl[1]], $uri['acl'].$title], $lang['msg:aclerr_no_rules']);
        else
            $msg = str_replace(['@1@', '@2@', '@3@'], [$lang['acl:'.$acl[1]], implode(' OR ',$acl[2]), $uri['acl'].$title] , $lang['msg:aclerr_denied']);    
        return [
            'content' => $msg,
            'missing_permission' => 'permission_'.$acl[1]
        ];
    }else
        return true;
}

$rev = @$_GET['rev'];
$full_title = @$_GET['title'];
$sess = [
    'member' => $member,
    'ip' => $post_sess['ip'],
    'identifier' => $i,
    'menus' => []
];
preg_match('/^('.implode('|',$_ns).'):(.*)$/', $full_title, $get_ns);
if(!$get_ns){
    $NameSpace = $lang['ns:document'];
    $Title = $full_title;
} else {
    $NameSpace = $get_ns[1];
    $Title = $get_ns[2];
}
$NS = array_search($NameSpace, $_ns);

switch ($_GET['page']){
    case 'w':
    case 'jump':
        $localConfig = [];
        if(!$sess || !$full_title || !$NameSpace || !$Title)
            $ACL = ['content' => 'err_var_undefined'];
        else{
            $acl = ACL::checkACL(['session' => $sess, 'page' => ['title' => $full_title, 'viewName' => 'wiki', 'namespace' => $NameSpace, 'title_' => $Title]]);
            if($acl === null)
                $ACL = ['content' => 'err_acl_check'];
            else{
                if($acl[0] === false){
                    if($acl[2] === null)
                        $msg = str_replace(['@1@','@2@'], [$lang['acl:'.$acl[1]], $uri['acl'].$full_title], $lang['msg:aclerr_no_rules']);
                    else
                        $msg = str_replace(['@1@', '@2@', '@3@'], [$lang['acl:'.$acl[1]], implode(' OR ',$acl[2]), $uri['acl'].$full_title] , $lang['msg:aclerr_denied']);    
                        $ACL = [
                        'content' => $msg,
                        'missing_permission' => 'permission_'.$acl[1]
                    ];
                }else
                    $ACL = true;
            }
        }
        if($ACL === true){
            $body['data']['user'] = ($NameSpace == $_ns['user']);
            (!$rev)? $fetch = Docs::LoadDocument($NS, rawurlencode($Title)): $fetch = Docs::LoadDocument($NS, rawurlencode($Title), $rev);
            $discussions = Thread::getDocThread($fetch[0]['docid'], 1);
            $starred = PressDo::ifStarred($post_sess['username'], $fetch[0]['docid']);
            $starcount = PressDo::countStar($fetch[0]['docid']);
            $datetime = $fetch[0]['datetime'];
            $discussProgress = (isset($discussions[0]));
            ($fetch[0]['content'])? $DOC_CONTENT = PressDo::readSyntax($full_title, $fetch[0]['content']) : $DOC_CONTENT = null;
            $content = $DOC_CONTENT['html'];
            $ct = [$DOC_CONTENT['categories']];
        }elseif(is_array($ACL)){
            $err = $ACL;
        }
        $body = [
            'viewName' => 'wiki',
            'title' => $full_title,
            'error' => $err,
            'data' => [
                'starred' => $starred,
                'star_count' => $starcount,
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                    'content' => htmlspecialchars($content),
                    'categories' => $ct
                ],
                'discuss_progress' => $discussProgress,
                'date' => $datetime,
                'rev' => $rev,
                'user' => null,
        //   'customData' => $ad_set
            ]
        ];
        break;
    case 'edit':
        $localConfig = [];
        $ACL = verifyACL($sess, $full_title, 'edit', $NameSpace, $Title);
        if($ACL === true){
            $iv = Docs::getIdAndVersion($NS, rawurlencode($Title));
            $baserev = $iv[0];
            $fetch = Docs::LoadDocument($NS, rawurlencode($Title));
            $raw = $fetch[0]['content'];
            $preview = PressDo::readSyntax($full_title, $fetch[0]['content'])['html'];
            $section = intval($_GET['section']);
            $isUser = ($NameSpace === $_ns['user']);
            $token = PressDo::rand(64);
        } elseif(is_array($ACL)){
            $err = $ACL;
        }
        $body = [
            'viewName' => 'edit',
            'title' => $full_title,
            'error' => $err,
            'data' => [
                'editor' => [
                    'baserev' => $baserev,
                    'section' => $section,
                    'raw' => $raw,
                    'preview' => $preview
                ],
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'user' => $isUser,
                'token' => $token
            //   'customData' => $ad_set
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
            'title' => $full_title.' (역사)',
            'data' => [
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'history' => [
                ]
        //  'customData' => $ad_set
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
        ($body['data']['history'][0]['rev'] == $iv[0])? $body['data']['prev_ver'] = null : $body['data']['prev_ver'] = $body['data']['history'][0]['rev'] + 1;
        (end($body['data']['history'])['rev'] == 1)? $body['data']['next_ver'] = null : $body['data']['next_ver'] = end($body['data']['history'])['rev'] - 1;
        $body['data']['initial_date'] = $l[0]['datetime'];
        break;
    case 'raw':
        (!$_GET['rev'])? $fetch = Docs::LoadDocument($NS, rawurlencode($Title)) : $fetch = Docs::LoadDocument($NS, rawurlencode($Title), $_GET['rev']);
        $localConfig = [];
        $body = [
            'viewName' => 'raw',
            'title' => $full_title,
            'data' => [
                    'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'rev' => $_GET['rev'],
                'text' => $fetch[0]['content']
        //  'customData' => $ad_set
            ]
        ];
        break;
    case 'acl':
        if($post_sess['member']['username'] !== null){
            $ACL = verifyACL($sess, $full_title, 'acl', $NameSpace, $Title);
            $doc_editable = ($ACL === true)? true:false;
            $ns_editable = (ACL::checkPerm('nsacl', $post_sess['member']['username']) === true)? true:false;
        }else{
            $doc_editable = false;
            $ns_editable = false;
        }
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
            'title' => $full_title,
            'data' => [
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'docACL' => [
                    'acls' => $docACLs,
                    'editable' => $doc_editable
                ],
                'nsACL' => [
                    'acls' => $nsACLs,
                    'editable' => $ns_editable
                ],
                'ACLTypes' => $ACLTypes
            ]
        ];
        break;
    case 'aclgroup':
        $t = ACL::checkPerm('aclgroup', $post_sess['member']['username']);
        $g = ACL::getACLgroups($t);
        if(isset($_GET['group']))
            $now = (in_array($_GET['group'], $g))? $_GET['group']:$g[0];
        else $now = $g[0];
        $resultSet = [];
        foreach (ACL::getACLgroupMember($now, $_GET['from'], $_GET['until']) as $l){
            $exp = ($l['until'] == 0)? null:$l['until'];
            array_push($resultSet, ['id' => $l['id'], 'cidr' => $l['target_ip'], 'member' => $l['target_member'], 'memo' => $l['comment'], 'date' => $l['datetime'], 'expiry' => $exp]);
        }
        if(isset($_GET['from'])){
            $_u = $_GET['from'] + 1;
            $_f = end($resultSet)['id'] - 1;
        }
        if(isset($_GET['until'])) {
            $_u = $resultSet[0]['id'] + 1;
            $_f = $_GET['until'] - 1;
        }
        $localConfig = [];
        $body = [
            'viewName' => '',
            'title' => 'ACLGroup',
            'data' => [
                'aclgroups' => $g,
                'accessible' => $t,
                'from' => $_f,
                'until' => $_u,
                'currentgroup' => urldecode($now),
                'groupmembers' => $resultSet
            ]
        ];
        break;
    case 'RecentChanges':
        $lo = $_GET['logtype'];
        $lt = array(
            'create' => "AND `action`='create'",
            'revert' => "AND `action`='revert'",
            'move' => "AND `action`='move'",
            'delete' => "AND `action`='delete'",
            '' => ''
        );
        $fetch = Docs::LoadHistory($NS, null, null, null, $lt[$lo]);
        $resultSet = [];
        foreach ($fetch as $f){
            $_e = Docs::findByID($f['docid']);
            $rs = array(
                'document' => ['namespace' => $_ns[$_e[0]], 'title' => $_e[1], 'ForceShowNameSpace' => $conf['ForceShowNameSpace']],
                'date' => $f['datetime'],
                'date' => $f['datetime'],
                'rev' => $f['rev'],
                'log' => $f['comment'],
                'author' => $f['contributor_m'],
                'ip' => $f['contributor_i'],
                'style' => null,
                'count' => $f['count'],
                'logtype' => $f['action'],
                'target_rev' => $f['reverted_version'],
                'acl' => $f['acl_changed'],
                'from' => $f['moved_from'],
                'to' => $f['moved_to'],
                'user_mode' => []
            );
            array_push($resultSet, $rs);
        }
        $localConfig = [];
        $body = [
            'viewName' => '',
            'title' => '최근 변경내역',
            'data' => [
                'content' => $resultSet
            ]
        ];
        break;
    case 'member':
        switch($_GET['title']){
            case 'login':
                if(isset($post_sess['POST']['username']) && isset($post_sess['POST']['password'])){
                    $l = Member::loginUser($post_sess['POST']['username'], hash('sha256', $post_sess['POST']['password']), $_SERVER['REQUEST_TIME'], $sess['ip'], $post_sess['ua']);
                    if(!$l){
                        $error = 'invalid_member';
                    }else{
                        $sess['menus'] = [];
                        $SP = ['aclgroup', 'grant', 'login_history'];
                        foreach ($SP as $prm){
                            $t = ACL::checkPerm($prm, $l[1]);
                            if($t === true) array_push($sess['menus'], $prm);
                        }
                        $error = null;
                        
                        $sess['member'] = [
                                'user_document_discuss' => null,
                                'username' => $l[1],
                                'gravatar_url' => $l[0]
                        ];
                    }
                }else{
                    $error = null;
                }
                $body = [
                    'viewName' => '',
                    'title' => 'login',
                    'data' => [
                        'error' => $error,
                        'redirect' => base64_decode($_GET['redirect'])
                    ]
                ];
                break;
        }
        break;
    case 'admin':
        $ac = ACL::checkPerm($_GET['title'], $post_sess['member']['username']);
            if($ac === true)
                $error = null;
            else
                $error = 'no_permission';
        $body = [
            'viewName' => '',
            'title' => $_GET['title'],
            'data' => [
                'error' => $error
            ]
        ];
        break;
}
echo json_encode(array('config' => $config, 'localConfig' => $localConfig, 'page' => $body, 'session' => $sess), JSON_UNESCAPED_UNICODE);
