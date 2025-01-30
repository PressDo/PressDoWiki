<?php
namespace PressDo\app\Controllers\Pages;

use PressDo\app\Models\{Document,History,Member};
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

        if(in_array($this->uri_data->query->logtype, ['create', 'revert', 'move', 'delete']))
            $lt = $this->uri_data->query->logtype;
        else
            $lt = 'all';

        $fetch = History::recentChanges($lt);
        $resultSet = [];

        foreach($fetch as $f){
            $ip = $member = null;

            if(empty($f['contributor_m'])){
                $uuid = Model::bin2uuid($f['contributor_i']);
                $ip = Member::ipLookup($uuid);
            }else{
                $uuid = Model::bin2uuid($f['contributor_m']);
                $member = Member::lookup($uuid);
            }

            $_e = Document::getTitleByUuid(Model::bin2uuid($f['document']));
            $rs = [
                'uuid' => Model::bin2uuid($f['uuid']),
                'document' => ['namespace' => $_e['namespace'], 'title' => $_e['title']],
                'date' => $f['datetime'],
                'log' => $f['comment'],
                'author' => $member,
                'ip' => $ip,
                'contributor_uuid' => $uuid,
                'rev' => $f['rev'],
                'style' => null,
                'count' => $f['count'],
                'logtype' => $f['action'],
                'target_rev' => $f['reverted_version'],
                'acl' => $f['acl_changed'],
                'from' => $f['moved_from'],
                'to' => $f['moved_to'],
                'ForceShowNameSpace' => Config::get('ForceShowNameSpace'),
                'user_mode' => []
            ];
            array_push($resultSet, $rs);
        }

        $page['data']['content'] = $resultSet;

        return $page;
    }
}