<?php
namespace PressDo;

require 'controllers/common.php';
require 'models/RecentDiscuss.php';

use PressDo\Models;
class WikiPage extends WikiCore
{
    public function make_data()
    {
        $lo = $this->uri_data->query->logtype;
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

        if(!in_array($lo, $logtypes))
            $lo = 'normal_thread';

        $lot = explode('_',$lo);
        $from = $lot[1];

        if($lot[0] == 'closed')
            $status = 'close';
        elseif($lot[0] == 'open' || $lot[0] == 'accepted' || $lot[0] == 'pause')
            $status = $lot[0];
        elseif($lot[0] == 'old' && $lot[1] == 'editrequest')
            $status = 'open';
        else
            $status = 'normal';

        $order = ($lot[0] == 'old')? 'ASC' : 'DESC';

        $fetch = Models::RecentDiscuss($from, $status, $order);
        $resultSet = [];
        foreach ($fetch as $f){
            if(!$f['last_contributor_m']){
                $uuid = Models::bin2uuid($f['last_contributor_i']);
                $ip = Models::ip_lookup($uuid);
            }else{
                $uuid = Models::bin2uuid($f['last_contributor_m']);
                $member = Models::member_lookup($uuid);
            }
            $_e = Models::get_doc_title(Models::bin2uuid($f['document']));
            $rs = array(
                'slug' => $f['urlstr'],
                'document' => ['namespace' => $_e['namespace'], 'title' => $_e['title']],
                'topic' => $f['topic'],
                'date' => $f['last_comment'],
                'logtype' => $f['logtype'],
                'ip' => $ip,
                'author' => $member,
                'user_mode' => []
            );
            array_push($resultSet, $rs);
        }
        $page = [
            'view_name' => 'RecentDiscuss',
            'title' => Lang::get('page')['RecentDiscuss'],
            'data' => [
                'content' => $resultSet
            ]
        ];

        return $page;
    }
}