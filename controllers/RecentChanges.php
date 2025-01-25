<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/RecentChanges.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        $page = [
            'view_name' => 'RecentChanges',
            'title' => Lang::get('page')['RecentChanges'],
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

        $fetch = Models::RecentChanges($lt);
        $resultSet = [];

        foreach($fetch as $f){
            $ip = $member = null;

            if(empty($f['contributor_m'])){
                $uuid = Models::bin2uuid($f['contributor_i']);
                $ip = Models::ip_lookup($uuid);
            }else{
                $uuid = Models::bin2uuid($f['contributor_m']);
                $member = Models::member_lookup($uuid);
            }

            $_e = Models::get_doc_title(Models::bin2uuid($f['document']));
            $rs = array(
                'uuid' => Models::bin2uuid($f['uuid']),
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
            );
            array_push($resultSet, $rs);
        }

        $page['data']['content'] = $resultSet;

        return $page;
    }
}