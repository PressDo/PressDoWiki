<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,History,Member,ACL};
use PressDo\app\Core\{Controller,Model};
use PressDo\app\Helpers\{Languages,Config};

class RecentChanges extends Controller
{
    public function makeData(): array
    {
        $page = [
            'view_name' => 'RecentChanges',
            'title' => Languages::get('page', 'RecentChanges'),
            'data' => [
                'content' => []
            ],
            'menus' => [],
            'customData' => []
        ];

        if(in_array($_GET['logtype'], ['create', 'revert', 'move', 'delete']))
            $lt = $_GET['logtype'];
        else
            $lt = 'all';

        $fetch = History::recentChanges($lt);
        $resultSet = [];

        foreach($fetch as $f){
            $ip = $member = null;

            if(empty($f['contributor_m'])){
                $uuid = Model::bin2uuid($f['contributor_i']);
                $ip = Member::ipLookup($uuid);

                if (empty($userStyleSet[$ip])):
                    $groups = array_map(fn($r) => $r[0]['groupid'], ACL::getUserAclgroups($ip));
                    $userStyleSet[$ip] = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
                endif;
            }else{
                $uuid = Model::bin2uuid($f['contributor_m']);
                $member = Member::lookup($uuid);
                $ip = null;

                if (empty($userStyleSet[$uuid])):
                    $groups = array_map(fn($r) => $r[0]['groupid'], ACL::getUserAclgroups(uuid: $uuid));
                    $userStyleSet[$uuid] = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
                endif;

                if (empty($mperms[$uuid])) {
                    $mperms[$uuid] = [];
                    ACL::getAccountPerms($uuid, $member, $mperms[$uuid]);
                }
            }

            $_e = Document::getTitleByUuid(Model::bin2uuid($f['document']));
            $rs = [
                'uuid' => Model::bin2uuid($f['uuid']),
                'document' => ['namespace' => $_e['namespace'], 'title' => $_e['title'], 'forceShowNamespace' => self::forceShowNamespace($_e['namespace'], $_e['title'])],
                'date' => $f['datetime'],
                'log' => $f['comment'],
                'author' => $member,
                'ip' => $ip,
                'contributor_uuid' => $uuid,
                'rev' => $f['rev'],
                'style' => $userStyleSet[$uuid] ?? $userStyleSet[$ip],
                'admin' => $ip ?? in_array('admin', $mperms[$uuid]),
                'count' => $f['count'],
                'logtype' => $f['action'],
                'target_rev' => $f['reverted_version'],
                'acl' => $f['acl_changed'],
                'from' => $f['moved_from'],
                'to' => $f['moved_to'],
                'user_mode' => []
            ];
            array_push($resultSet, $rs);
        }

        $page['data']['content'] = $resultSet;

        return $page;
    }
}