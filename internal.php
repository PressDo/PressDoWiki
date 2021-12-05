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
    global $lang, $uri, $testdata;
    $acl = ACL::checkACL(['session' => $sess, 'page' => ['title' => $title, 'viewName' => $vn, 'namespace' => $ns, 'title_' => $t]]);
    
    if($acl[0] === false){
        $editable = ($acl[2] === null)? null:implode(' OR ',$acl[2]);
        $editablee = ($acl[6][2] === null)? null:implode(' OR ', $acl[6][2]);
        $until = ($acl[5]['until'] == '0')? $lang['aclgroup:forever']:date('Y-m-d H:i:s', $acl[5]['until']);
        $untils = ($acl[6][5]['until'] == '0')? $lang['aclgroup:forever']:date('Y-m-d H:i:s', $acl[6][5]['until']);
        if($vn == 'edit') {
            $msgTemplate = $lang['msg:aclerr_'.$acl[6][3]];
            $msg = str_replace(['@1@', '@2@', '@3@','@4@','@5@','@6@','@7@'], [$lang['acl:'.$acl[6][1]], $uri['acl'].$title, $editablee, $acl[6][5]['id'], $untils, $acl[6][5]['comment'],$acl[6][4]] , $msgTemplate);
        }else{
            $msgTemplate = $lang['msg:aclerr_'.$acl[3]];
            $msg = str_replace(['@1@', '@2@', '@3@','@4@','@5@','@6@','@7@'], [$lang['acl:'.$acl[1]], $uri['acl'].$title, $editable, $acl[5]['id'], $until, $acl[5]['comment'],$acl[4]] , $msgTemplate);
        }
        return [
            'content' => $msg,
            'missing_permission' => 'permission_'.$acl[1]
        ];
    }else
        return true;
}

if(isset($_GET['rev'])) $rev = $_GET['rev'];
$full_title = $_GET['title'];
$sess = [
    'member' => $member,
    'ip' => $post_sess['ip'],
    'identifier' => $i,
    'menus' => []
];
list($rawNS, $NameSpace, $Title) = Docs::parseTitle($full_title);

switch ($_GET['page']){
    case 'w':
    case 'jump':
        $localConfig = [];
        $ACL = verifyACL($sess, $full_title, 'wiki', $NameSpace, $Title);
        if($ACL === true){
            $body['data']['user'] = ($NameSpace == $_ns['user']);
            (!$rev)? $fetch = Docs::loadDocument($rawNS,$Title): $fetch = Docs::loadDocument($rawNS,$Title, $rev);
            $discussions = Thread::getDocThread($rawNS,$Title);
            $starred = PressDo::ifStarred($post_sess['username'], $fetch['docid']);
            $starcount = PressDo::countStar($fetch['docid']);
            $datetime = $fetch['datetime'];
            $discussProgress = (isset($discussions[0]));
            if($fetch['content'] !== null){
                $DOC_CONTENT = PressDo::readSyntax($fetch['content'], ['title' => $full_title, 'shortUri' => $conf['UseShortURI']]);
                $content = htmlspecialchars(utf8_encode($DOC_CONTENT['html']));
            }else{
                $DOC_CONTENT = null;
                $content = null;
            }
            
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
                    'content' => $content,
                    'categories' => $DOC_CONTENT['categories']
                ],
                'discuss_progress' => $discussProgress,
                'date' => $datetime,
                'rev' => $rev,
                'user' => null,
        //   'customData' => $ad_set
            ],
            'testData' => $ACL
        ];
        break;
    case 'edit':
        $localConfig = [];
        $ACL = verifyACL($sess, $full_title, 'edit', $NameSpace, $Title);
        if($ACL === true || $ACL['missing_permission'] == 'permission_edit_request' || $ACL['missing_permission'] == 'permission_edit_request'){
            $ver = Docs::getVersion($rawNS,$Title);
            $baserev = $ver;
            $fetch = Docs::loadDocument($rawNS,$Title);
            $raw = $fetch['content'];
            $preview = PressDo::readSyntax($fetch['content'], ['title' => $full_title, 'shortUri' => $conf['UseShortURI']])['html'];
            $section = intval($_GET['section']);
            $isUser = ($NameSpace === $_ns['user']);
            $token = PressDo::rand(64);
            if(is_array($ACL))
                $err = $ACL;
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
                    'raw' => utf8_encode($raw),
                    'preview' => utf8_encode($preview)
                ],
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace']
                ],
                'user' => $isUser,
                'token' => $token
            //   'customData' => $ad_set
            ],
            'testData' => $testdata
        ];
        break;
    case 'delete':
        $localConfig = [];
        $ACL = verifyACL($sess, $full_title, 'delete', $NameSpace, $Title);
        if($ACL === true ){
            $body = [
                'viewName' => 'delete',
                'title' => $full_title,
                'data' => [
                    'document' => [
                        'namespace' => $NameSpace,
                        'title' => $Title,
                        'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                    ]
                ]
            ];
            if(is_array($ACL))
                $err = $ACL;
        } elseif(is_array($ACL)){
            $err = $ACL;
        }
        break;
    case 'move':
        $localConfig = [];
        $token = PressDo::rand(64);
        $ACL = verifyACL($sess, $full_title, 'move', $NameSpace, $Title);
        if($ACL === true){
            $body = [
                'viewName' => 'move',
                'title' => $full_title,
                'data' => [
                    'document' => [
                        'namespace' => $NameSpace,
                        'title' => $Title,
                        'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                    ],
                    'token' => $token
                ]
            ];
        } elseif(is_array($ACL)){
            $body = [
                'viewName' => 'move',
                'title' => $full_title,
                'error' => $ACL,
                'token' => $token
            ];
        }
        break;
    case 'history':
        $ACL = verifyACL($sess, $full_title, 'wiki', $NameSpace, $Title);
        if($ACL === true){
            if(isset($_GET['from'])) $from = $_GET['from'];
            if(isset($_GET['until'])) $until = $_GET['until'];
            $fetch = Docs::loadHistory($rawNS,$Title, $from, $until);
            $ver = Docs::getVersion($rawNS,$Title);
            $l = Docs::loadDocument($rawNS,$Title, 1);
            $localConfig = [];
            $cn = count($fetch);
            $cl = ($cn < 31)? $cn:$cn-1;
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
            for($i=0; $i<$cl; ++$i){
                if(isset($from))
                    $r = $from - $i;
                elseif(isset($until))
                    $r = $until + $cl - $i - 1;
                else
                    $r = $ver - $i;
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
            $body['data']['prev_ver'] = ($body['data']['history'][0]['rev'] == $ver)? null : $body['data']['history'][0]['rev'] + 1;
            $body['data']['next_ver'] = (end($body['data']['history'])['rev'] == 1)? null : end($body['data']['history'])['rev'] - 1;
            $body['data']['initial_date'] = $l['datetime'];
        }else
            $body['error'] = $ACL;
        break;
    case 'raw':
        $ACL = verifyACL($sess, $full_title, 'wiki', $NameSpace, $Title);
        if($ACL === true){
            (!$_GET['rev'])? $fetch = Docs::loadDocument($rawNS,$Title) : $fetch = Docs::loadDocument($rawNS,$Title, $_GET['rev']);
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
                'text' => $fetch['content']
            //  'customData' => $ad_set
                ]
            ];
        } else
            $body['error'] = $ACL;
        break;
    case 'diff':
        $or = ($_GET['oldrev'] > 0)? $_GET['oldrev']:$_GET['rev'] - 1;
        $localConfig = [];
        $ACL = verifyACL($sess, $full_title, 'wiki', $NameSpace, $Title);
        if($ACL === true){
            $body['data']['user'] = ($NameSpace == $_ns['user']);
            $new = Docs::loadDocument($rawNS,$Title, $rev)['content'];
            $old = Docs::loadDocument($rawNS,$Title, $or)['content'];
        }elseif(is_array($ACL)){
            $err = $ACL;
        }
        $body = [
            'viewName' => 'wiki',
            'title' => $full_title,
            'error' => $err,
            'data' => [
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title,
                    'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                ],
                'oldrev' => $or,
                'rev' => $_GET['rev'],
                'diff' => utf8_encode(Docs::load_diff($old,$new, $or, $_GET['rev']))
            ]
        ];
        break;
    case 'discuss':
        $d_perms = [];
        $perms = ['delete_thread', 'update_thread_status', 'hide_thread_comment', 'update_thread_document', 'update_thread_topic'];
        $actions = ['create_thread', 'write_thread_comment'];
        foreach ($perms as $p)
            if(ACL::checkPerm($p, $post_sess['member']['username']) === true) array_push($d_perms, $p);
        
        foreach ($actions as $a)
            if(verifyACL($sess, $full_title, $a, $NameSpace, $Title) === true) array_push($d_perms, $a);
        
        
        $thr = Thread::getDocThread($rawNS,$Title);
        $threads = [];
        foreach ($thr as $t){
            $com = Thread::getLatestComments($t['urlstr']);
            $discuss = [];
            foreach ($com as $c){
                if($c['type'] == 'status' || $c['type'] == 'topic' || $c['type'] == 'document')
                    $cont = $c['content'];
                else
                    $cont = PressDo::readSyntax($c['content'], ['thread' => true, 'shortUri' => $conf['UseShortURI']])['html'];
                $blocked = ($c['hide_author'])? true:false;
                array_push($discuss, [
                    'id' => $c['no'],
                    'author' => $c['contributor_m'],
                    'ip' => $c['contributor_i'],
                    'text' => $cont,
                    'date' => $c['datetime'],
                    'hide_author' => $c['blind'],
                    'type' => $c['type'],
                    'admin' => ACL::checkPerm('admin',$c['contributor_m']),
                    'blocked' => $blocked
                ]);
            }
            $ra = [
                'slug' => $t['urlstr'],
                'topic' => $t['topic'],
                'discuss' => $discuss
            ];
            array_push($threads, $ra);
        }
        $body = [
            'viewName' => 'discuss',
            'title' => $full_title,
            'error' => $err,
            'data' => [
                'document' => [
                    'namespace' => $NameSpace,
                    'title' => $Title
                ],
                'thread_list' => $threads,
                'editRequests' => [
                    'slug'
                ],
                'perms' => $d_perms
            ]
        ];
        break;
    case 'thread':
        $actions = ['create_thread', 'write_thread_comment'];
        $info = Thread::getThreadInfo($_GET['title']);
        $com = Thread::getComments($_GET['title']);
        $threads = [];
        $d_perms = [];
        if($info['namespace'] == 'document')
            $doctitle = $info['title'];
        else
            $doctitle = $_ns[$info['namespace']].':'.$info['title'];
        
        foreach ($actions as $a)
            if(verifyACL($sess, $doctitle, $a, $_ns[$info['namespace']], $info['title']) === true) array_push($d_perms, $a);
            
        foreach ($com as $c){
            if($c['type'] == 'status' || $c['type'] == 'topic' || $c['type'] == 'document')
                $cont = $c['content'];
            else
                $cont = PressDo::readSyntax($c['content'], ['thread' => true, 'shortUri' => $conf['UseShortURI']])['html'];
            $blocked = ($c['hide_author'])? true:false;
            array_push($threads, [
                'id' => $c['no'],
                'author' => $c['contributor_m'],
                'ip' => $c['contributor_i'],
                'text' => $cont,
                'date' => $c['datetime'],
                'hide_author' => $c['blind'],
                'type' => $c['type'],
                'admin' => ACL::checkPerm('admin',$c['contributor_m']),
                'blocked' => $blocked
            ]);
        }
        $body = [
            'viewName' => 'thread',
            'data' => [
                'document' => [
                    'namespace' => $_ns[$info['namespace']],
                    'title' => $info['title']
                ],
                'status' => $info['status'],
                'topic'=> $info['topic'],
                'slug' => $_GET['title'],
                'initial_author' => $info['contributor_i'].$info['contributor_m'],
                'comments' => $threads,
                'perms' => $d_perms,
                'updateThreadDocument' => ACL::checkPerm('update_thread_document',$post_sess['member']['username']),
                'updateThreadTopic' => ACL::checkPerm('update_thread_topic',$post_sess['member']['username']),
                'updateThreadStatus' => ACL::checkPerm('update_thread_status',$post_sess['member']['username']),
                'hideThreadComment' => ACL::checkPerm('hide_thread_comment',$post_sess['member']['username']),
                'deleteThread' => ACL::checkPerm('delete_thread',$post_sess['member']['username'])
            ]
        ];
        break;
    case 'acl':
        $ACL = verifyACL($sess, $full_title, 'acl', $NameSpace, $Title);
        $doc_editable = ($ACL === true)? true:false;
        $ns_editable = (ACL::checkPerm('nsacl', $post_sess['member']['username']) === true)? true:false;
        $docacls = ACL::getDocACL($rawNS,$Title); // {0:{id: 1, access: edit, until: 30}, 1: ...}
        $nsacls = ACL::getNSACL($rawNS);
        $ACLTypes = ACL::$PRESSDO_ACLTYPES;
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
            ],
            'testData' => $post_sess['api']
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
            array_push($resultSet, ['id' => $l['id'], 'cidr' => $l['display_ip'], 'member' => $l['target_member'], 'memo' => $l['comment'], 'date' => $l['datetime'], 'expiry' => $exp]);
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
                'currentgroup' => $now,
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
        $fetch = Docs::loadHistory($rawNS, null, null, null, $lt[$lo]);
        $resultSet = [];
        foreach ($fetch as $f){
            $_e = Docs::findByID($f['docid']);
            $rs = array(
                'document' => ['namespace' => $_ns[$_e['namespace']], 'title' => $_e['title']],
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
                'ForceShowNameSpace' => $conf['ForceShowNameSpace'],
                'user_mode' => []
            );
            array_push($resultSet, $rs);
        }
        $localConfig = [];
        $body = [
            'viewName' => '',
            'title' => $lang['RecentChanges'],
            'data' => [
                'content' => $resultSet
            ]
        ];
        break;
    case 'RecentDiscuss':
        $lo = $_GET['logtype'];
        $fetch = Thread::RecentDiscuss($lo);
        $resultSet = [];
        foreach ($fetch as $f){
            $rs = array(
                'slug' => $f['urlstr'],
                'document' => ['namespace' => $_ns[$f['namespace']], 'title' => $f['title']],
                'topic' => $f['topic'],
                'date' => $f['last_comment'],
                'logtype' => $f['logtype'],
                'user_mode' => []
            );
            array_push($resultSet, $rs);
        }
        $localConfig = [];
        $body = [
            'viewName' => '',
            'title' => $lang['RecentDiscuss'],
            'data' => [
                'content' => $resultSet
            ]
        ];
        break;
    case 'BlockHistory':
        if(isset($_GET['from'])) $from = $_GET['from'];
        if(isset($_GET['until'])) $until = $_GET['until'];
        $type = $_GET['target'];
        $keyword = $_GET['query'];
        $data = array_reverse(ACL::blockHistory($type, $keyword,$from, $until));
        $dataset = [];
        foreach ($data as $d){
            if($d['until'] == null)
                $dur = null;
            else
                $dur = $d['until'] - $d['datetime'];
            array_push($dataset, [
                'author' => $d['executor'],
                'author_ip' => $d['executor_ip'],
                'datetime' => $d['datetime'],
                'action' => $d['action'],
                'content' => [
                    'id' => $d['id'],
                    'ip' => $d['display_ip'],
                    'duration' => $dur,
                    'memo' => $d['comment'],
                    'member' => $d['target_member'],
                    'aclgroup' => $d['target_aclgroup'],
                    'granted' => $d['granted']
                ]
            ]);
        }
        
        $body = [
            'viewName' => '',
            'title' => $lang['BlockHistory'],
            'data' => [
                'prev_page' => null,
                'next_page' => null,
                'history' => $dataset
            ]
        ];
        if($body['data']['history'][0]['content']['id'] != ACL::countBlockHistory())
            $body['data']['prev_page'] = $body['data']['history'][0]['content']['id'] + 1;
        if (end($body['data']['history'])['content']['id'] != 1)
            $body['data']['next_page'] = end($body['data']['history'])['content']['id'] - 1;
        break;
    case 'RandomPage':
        if(!in_array($_GET['namespace'],array_values($_ns)))
            $ns = 'document';
        else
            $ns = array_search($_GET['namespace'], $_ns);
        $localConfig = [];
        $dataset = [];
        foreach (Docs::getRandom(100,$ns) as $d){
            array_push($dataset, ['namespace' => $_ns[$d['namespace']], 'title' => $d['title']]);
        }
        $body = [
            'viewName' => '',
            'title' => 'RandomPage',
            'namespaces' => array_values($_ns),
            'data' => [
                'content' => $dataset
            ]
        ];
        break;
    case 'OldPages':
        if(!in_array($_GET['namespace'],array_values($_ns)))
            $ns = 'document';
        else
            $ns = array_search($_GET['namespace'], $_ns);
        $localConfig = [];
        $dataset = [];
        foreach (Docs::getRandom(100,$ns,'o') as $d){
            array_push($dataset, ['namespace' => $_ns[$d['namespace']], 'title' => $d['title'], 'datetime' => $d['datetime']]);
        }
        $body = [
            'viewName' => '',
            'title' => 'RandomPage',
            'namespaces' => array_values($_ns),
            'data' => [
                'content' => $dataset
            ]
        ];
        break;
    case 'ShortestPages':
    case 'LongestPages':
        if(!in_array($_GET['namespace'],array_values($_ns)))
            $ns = 'document';
        else
            $ns = array_search($_GET['namespace'], $_ns);
        $localConfig = [];
        $dataset = [];
        foreach (Docs::getRandom(100,$ns,strtolower(substr($_GET['page'],0,1))) as $d){
            array_push($dataset, ['namespace' => $_ns[$d['namespace']], 'title' => $d['title'], 'length' => $d['length']]);
        }
        $body = [
            'viewName' => '',
            'title' => 'RandomPage',
            'namespaces' => array_values($_ns),
            'data' => [
                'content' => $dataset
            ]
        ];
        break;
    case 'member':
        switch($_GET['title']){
            case 'login':
                if(isset($post_sess['POST']['username']) && isset($post_sess['POST']['password'])){
                    $l = Member::loginUser($post_sess['POST']['username'], hash('sha256', $post_sess['POST']['password']), $_SERVER['REQUEST_TIME'], $sess['ip'], $post_sess['ua']);
                    if(!$l){
                        $error = 'err_invalid_member';
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
    case 'acldata':
        $docacls = ACL::getDocACL($rawNS,$Title); // {0:{id: 1, access: edit, until: 30}, 1: ...}
        $nsacls = ACL::getNSACL($rawNS);
        $ACLTypes = ACL::$PRESSDO_ACLTYPES;
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
                ],
                'docACL' => [
                    'acls' => $docACLs
                ],
                'nsACL' => [
                    'acls' => $nsACLs
                ],
                'ACLTypes' => $ACLTypes
            ]
        ];
        break;
    case 'License':
        $updated = (ACL::checkPerm('admin', $post_sess['member']['username']) === true)? '7/10/2021, 15:11:00 PM':'';
        $body = [
            'viewName' => '',
            'title' => $lang['License'],
            'data' => [
                'version' => '2107a',
                'updated' => $updated,
                'hash' => 'ffede52'
            ]
        ];
        break;
    case 'Upload':
        $dataset = Docs::
        $body = [
            'viewName' => '',
            'title' => $lang['Upload'],
            'data' => [
                'Licenses' => Docs::getUploadInfo()['License'],
                'Categories' => Docs::getUploadInfo()['Category']
            ]
        ];
        break;
}
echo json_encode(array('config' => $config, 'localConfig' => $localConfig, 'page' => $body, 'session' => $sess), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS);
