<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Thread,Document,Member,ACL};
use PressDo\app\Core\{Controller,Model};
use PressDo\app\Helpers\{Languages,Config};

class RecentDiscuss extends Controller
{
    public function makeData(): array
    {
        $lo = $_GET['logtype'];
        $logtypes = [
            'normal_thread',
            'old_thread',
            'pause_thread',
            'closed_thread',
            'open_editrequest',
            'accepted_editrequest',
            'closed_editrequest',
            'old_editrequest'
        ];

        if (!in_array($lo, $logtypes))
            $lo = 'normal_thread';

        $lot = explode('_',$lo);
        $from = $lot[1];

        if ($lot[0] == 'closed')
            $status = 'close';
        elseif ($lot[0] == 'open' || $lot[0] == 'accepted' || $lot[0] == 'pause')
            $status = $lot[0];
        elseif ($lot[0] == 'old' && $lot[1] == 'editrequest')
            $status = 'open';
        else
            $status = 'normal';

        $order = ($lot[0] == 'old')? 'ASC' : 'DESC';
        $fetch = Thread::recentDiscuss($from, $status, $order);
        $resultSet = [];
        $userStyleSet = [];
        $mperms = [];

        foreach ($fetch as $f){
            if (!$f['contributor_m']) {
                $uuid = Model::bin2uuid($f['contributor_i']);
                $ip = Member::ipLookup($uuid);

                if (empty($userStyleSet[$ip])):
                    $groups = array_map(fn($r) => $r[0]['groupid'], ACL::getUserAclgroups($ip));
                    $userStyleSet[$ip] = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
                endif;
            } else {
                $uuid = Model::bin2uuid($f['contributor_m']);
                $member = Member::lookup($uuid);
                $ip = null;

                if (empty($userStyleSet[$uuid])):
                    $groups = array_map(fn($r) => $r[0]['groupid'], ACL::getUserAclgroups(uuid: $uuid));
                    $userStyleSet[$uuid] = implode(' ', array_map(fn($r) => Config::get('aclgroup.'.$r.'.style', ' '), $groups));
                endif;

                if (empty($mperms[$uuid])) {
                    $mperms[$uuid] = [];
                    ACL::getAccountPerms(['username' => $member, 'uuid' => $uuid], $mperms[$uuid]);
                }
            }
            $_e = Document::getTitleByUuid(Model::bin2uuid($f['document']));
            $rs = [
                'slug' => $f['urlstr'],
                'document' => ['namespace' => $_e['namespace'], 'title' => $_e['title'], 'forceShowNamespace' => self::forceShowNamespace($_e['namespace'], $_e['title'])],
                'topic' => $f['topic'],
                'date' => $f['last_comment'],
                'ip' => $ip,
                'author' => $member,
                'contributor_uuid' => $uuid,
                'style' => $userStyleSet[$uuid] ?? $userStyleSet[$ip], 
                'admin' => $ip ?? in_array('admin', $mperms[$uuid]),
                'user_mode' => []
            ];
            array_push($resultSet, $rs);
        }
        $page = [
            'view_name' => 'RecentDiscuss',
            'title' => Languages::get('page', 'RecentDiscuss'),
            'data' => [
                'content' => $resultSet
            ]
        ];

        return $page;
    }
}